<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Infrastructure\Http\Controller;

use JsonException;
use Mainfreme\UserProfile\Application\Command\AssignRole\AssignRoleCommand;
use Mainfreme\UserProfile\Application\Command\CreateGroup\CreateGroupCommand;
use Mainfreme\UserProfile\Application\Command\CreateRole\CreateRoleCommand;
use Mainfreme\UserProfile\Application\Command\CreateUser\CreateUserCommand;
use Mainfreme\UserProfile\Application\Command\UpdateProfile\UpdateProfileCommand;
use Mainfreme\UserProfile\Application\DTO\GroupListResponse;
use Mainfreme\UserProfile\Application\DTO\GroupResponse;
use Mainfreme\UserProfile\Application\DTO\RoleTreeResponse;
use Mainfreme\UserProfile\Application\DTO\UserListResponse;
use Mainfreme\UserProfile\Application\DTO\UserResponse;
use Mainfreme\UserProfile\Application\Query\GetRoleTree\GetRoleTreeQuery;
use Mainfreme\UserProfile\Application\Query\GetUser\GetUserQuery;
use Mainfreme\UserProfile\Application\Query\ListGroups\ListGroupsQuery;
use Mainfreme\UserProfile\Application\Query\ListUsers\ListUsersQuery;
use Mainfreme\UserProfile\Application\Service\MessageBusDispatcher;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'User Profile', description: 'REST API do profilu użytkownika (Bio) i roli')]
final class UserProfileRestController
{
    public function __construct(
        private readonly MessageBusDispatcher $dispatcher,
    ) {
    }

    #[Route('/api/roles', name: 'user_profile_role_tree', methods: ['GET'])]
    #[OA\Get(
        path: '/api/roles',
        summary: 'Drzewo hierarchii ról',
        tags: ['User Profile'],
    )]
    #[OA\Response(response: 200, description: 'Hierarchia ról')]
    public function roleTree(): JsonResponse
    {
        /** @var RoleTreeResponse $response */
        $response = $this->dispatcher->dispatchQuery(new GetRoleTreeQuery());

        return new JsonResponse(
            $response->toArray(),
            OperationStatus::Success === $response->status
                ? Response::HTTP_OK
                : Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    #[Route('/api/roles', name: 'user_profile_create_role', methods: ['POST'])]
    #[OA\Post(
        path: '/api/roles',
        summary: 'Dodanie roli do drzewa hierarchii',
        tags: ['User Profile'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['role', 'label'],
            properties: [
                new OA\Property(property: 'role', type: 'string', example: 'ROLE_EDITOR'),
                new OA\Property(property: 'label', type: 'string', example: 'Redaktor'),
                new OA\Property(property: 'parentRole', type: 'string', example: 'ROLE_MODERATOR'),
            ],
        ),
    )]
    #[OA\Response(response: 201, description: 'Rola dodana')]
    #[OA\Response(response: 400, description: 'Błąd walidacji')]
    public function createRole(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        /** @var RoleTreeResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateRoleCommand(
            role: $this->stringValue($payload, 'role'),
            label: $this->stringValue($payload, 'label'),
            parentRole: $this->optionalString($payload, 'parentRole'),
        ));

        return new JsonResponse(
            $response->toArray(),
            OperationStatus::Success === $response->status
                ? Response::HTTP_CREATED
                : $this->resolveErrorStatusCode($response->errorMessage ?? ''),
        );
    }

    #[Route('/api/groups', name: 'user_profile_list_groups', methods: ['GET'])]
    #[OA\Get(
        path: '/api/groups',
        summary: 'Lista grup',
        tags: ['User Profile'],
    )]
    #[OA\Response(response: 200, description: 'Lista grup')]
    public function listGroups(): JsonResponse
    {
        /** @var GroupListResponse $response */
        $response = $this->dispatcher->dispatchQuery(new ListGroupsQuery());

        return new JsonResponse(
            $response->toArray(),
            OperationStatus::Success === $response->status
                ? Response::HTTP_OK
                : $this->resolveErrorStatusCode($response->errorMessage ?? ''),
        );
    }

    #[Route('/api/groups', name: 'user_profile_create_group', methods: ['POST'])]
    #[OA\Post(
        path: '/api/groups',
        summary: 'Utworzenie grupy',
        tags: ['User Profile'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Redakcja'),
                new OA\Property(property: 'description', type: 'string', example: 'Zespół redakcyjny'),
                new OA\Property(property: 'role', type: 'string', example: 'ROLE_MODERATOR'),
            ],
        ),
    )]
    #[OA\Response(response: 201, description: 'Grupa utworzona')]
    #[OA\Response(response: 400, description: 'Błąd walidacji')]
    public function createGroup(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        /** @var GroupResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateGroupCommand(
            name: $this->stringValue($payload, 'name'),
            description: $this->optionalString($payload, 'description'),
            role: $this->optionalString($payload, 'role'),
        ));

        if (OperationStatus::Success === $response->status) {
            return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
        }

        return new JsonResponse(
            $response->toArray(),
            $this->resolveErrorStatusCode($response->errorMessage ?? ''),
        );
    }

    #[Route('/api/users', name: 'user_profile_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Lista użytkowników',
        tags: ['User Profile'],
    )]
    #[OA\Parameter(
        name: 'role',
        in: 'query',
        required: false,
        description: 'Filtr po roli, np. ROLE_ADMIN',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(response: 200, description: 'Lista użytkowników')]
    public function list(Request $request): JsonResponse
    {
        $role = $request->query->get('role');
        $roleFilter = \is_string($role) ? $role : null;

        /** @var UserListResponse $response */
        $response = $this->dispatcher->dispatchQuery(new ListUsersQuery($roleFilter));

        return new JsonResponse(
            $response->toArray(),
            OperationStatus::Success === $response->status
                ? Response::HTTP_OK
                : $this->resolveErrorStatusCode($response->errorMessage ?? ''),
        );
    }

    #[Route('/api/users', name: 'user_profile_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Utworzenie użytkownika',
        tags: ['User Profile'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'displayName'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'jan@example.com'),
                new OA\Property(property: 'displayName', type: 'string', example: 'Jan Kowalski'),
                new OA\Property(property: 'bio', type: 'string', example: 'Lubię Symfony i DDD'),
                new OA\Property(property: 'role', type: 'string', example: 'ROLE_USER'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ],
        ),
    )]
    #[OA\Response(response: 201, description: 'Użytkownik utworzony')]
    #[OA\Response(response: 400, description: 'Błąd walidacji')]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateUserCommand(
            email: $this->stringValue($payload, 'email'),
            displayName: $this->stringValue($payload, 'displayName'),
            bio: $this->optionalString($payload, 'bio'),
            role: $this->optionalString($payload, 'role'),
            password: $this->optionalString($payload, 'password'),
        ));

        return $this->userJsonResponse($response, Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', name: 'user_profile_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Pobranie profilu użytkownika',
        tags: ['User Profile'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Profil użytkownika')]
    #[OA\Response(response: 404, description: 'Użytkownik nie istnieje')]
    public function show(string $id): JsonResponse
    {
        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchQuery(new GetUserQuery($id));

        return $this->userJsonResponse($response, Response::HTTP_OK, notFound: true);
    }

    #[Route('/api/users/{id}/profile', name: 'user_profile_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}/profile',
        summary: 'Edycja profilu (Bio i nazwa wyświetlana)',
        tags: ['User Profile'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['bio'],
            properties: [
                new OA\Property(property: 'bio', type: 'string', example: 'Nowe bio użytkownika'),
                new OA\Property(property: 'displayName', type: 'string', example: 'Jan Kowalski'),
            ],
        ),
    )]
    #[OA\Response(response: 200, description: 'Profil zaktualizowany')]
    #[OA\Response(response: 404, description: 'Użytkownik nie istnieje')]
    public function updateProfile(Request $request, string $id): JsonResponse
    {
        $payload = $this->decodeJson($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new UpdateProfileCommand(
            userId: $id,
            bio: $this->stringValue($payload, 'bio'),
            displayName: $this->optionalString($payload, 'displayName'),
        ));

        return $this->userJsonResponse($response, Response::HTTP_OK, notFound: true);
    }

    #[Route('/api/users/{id}/role', name: 'user_profile_assign_role', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}/role',
        summary: 'Przypisanie roli użytkownikowi',
        tags: ['User Profile'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['role'],
            properties: [
                new OA\Property(property: 'role', type: 'string', example: 'ROLE_ADMIN'),
            ],
        ),
    )]
    #[OA\Response(response: 200, description: 'Rola przypisana')]
    #[OA\Response(response: 404, description: 'Użytkownik nie istnieje')]
    public function assignRole(Request $request, string $id): JsonResponse
    {
        $payload = $this->decodeJson($request);

        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new AssignRoleCommand(
            userId: $id,
            role: $this->stringValue($payload, 'role'),
        ));

        return $this->userJsonResponse($response, Response::HTTP_OK, notFound: true);
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function decodeJson(Request $request): array|JsonResponse
    {
        $content = $request->getContent();

        if ('' === $content) {
            return new JsonResponse(
                UserResponse::error('JSON body is required.')->toArray(),
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $decoded = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new JsonResponse(
                UserResponse::error('JSON body is invalid.')->toArray(),
                Response::HTTP_BAD_REQUEST,
            );
        }

        if (!\is_array($decoded)) {
            return new JsonResponse(
                UserResponse::error('JSON body must be an object.')->toArray(),
                Response::HTTP_BAD_REQUEST,
            );
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? '';

        return \is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function optionalString(array $payload, string $key): ?string
    {
        if (!\array_key_exists($key, $payload) || null === $payload[$key]) {
            return null;
        }

        return \is_string($payload[$key]) ? $payload[$key] : null;
    }

    private function userJsonResponse(UserResponse $response, int $successCode, bool $notFound = false): JsonResponse
    {
        if (OperationStatus::Success === $response->status) {
            return new JsonResponse($response->toArray(), $successCode);
        }

        return new JsonResponse(
            $response->toArray(),
            $this->resolveErrorStatusCode($response->errorMessage ?? '', $notFound),
        );
    }

    private function resolveErrorStatusCode(string $message, bool $notFound = false): int
    {
        if ($notFound && str_contains($message, 'not found')) {
            return Response::HTTP_NOT_FOUND;
        }

        $clientErrorFragments = [
            'cannot be empty',
            'invalid format',
            'is invalid',
            'not allowed',
            'must be between',
            'must be at least',
            'exceeds maximum',
            'already exists',
            'JSON body',
            'must start with',
            'was not found',
        ];

        foreach ($clientErrorFragments as $fragment) {
            if (str_contains($message, $fragment)) {
                return Response::HTTP_BAD_REQUEST;
            }
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}

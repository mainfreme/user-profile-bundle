<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\Controller;

use SWH\UserProfile\Application\Command\AssignRole\AssignRoleCommand;
use SWH\UserProfile\Application\Command\CreateGroup\CreateGroupCommand;
use SWH\UserProfile\Application\Command\CreateRole\CreateRoleCommand;
use SWH\UserProfile\Application\Command\CreateUser\CreateUserCommand;
use SWH\UserProfile\Application\Command\UpdateProfile\UpdateProfileCommand;
use SWH\UserProfile\Application\DTO\GroupListResponse;
use SWH\UserProfile\Application\DTO\GroupResponse;
use SWH\UserProfile\Application\DTO\JsonHttpResponse;
use SWH\UserProfile\Application\DTO\RoleTreeResponse;
use SWH\UserProfile\Application\DTO\UserListResponse;
use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Application\Query\GetRoleTree\GetRoleTreeQuery;
use SWH\UserProfile\Application\Query\GetUser\GetUserQuery;
use SWH\UserProfile\Application\Query\ListGroups\ListGroupsQuery;
use SWH\UserProfile\Application\Query\ListUsers\ListUsersQuery;
use SWH\UserProfile\Application\Service\MessageBusDispatcher;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\PlainPassword;
use SWH\UserProfile\Domain\User\ValueObject\UserId;
use SWH\UserProfile\Infrastructure\Http\Request\JsonBody;
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
        private readonly int $bioMaxLength,
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

        return $this->json($response, Response::HTTP_OK);
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
    public function createRole(JsonBody $body): JsonResponse
    {
        /** @var RoleTreeResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateRoleCommand(
            role: $body->string('role'),
            label: $body->string('label'),
            parentRole: $body->optionalString('parentRole'),
        ));

        return $this->json($response, Response::HTTP_CREATED);
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

        return $this->json($response, Response::HTTP_OK);
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
    public function createGroup(JsonBody $body): JsonResponse
    {
        /** @var GroupResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateGroupCommand(
            name: GroupName::fromString($body->string('name')),
            description: $body->optionalString('description'),
            role: UserRole::tryFromString($body->optionalString('role')),
        ));

        return $this->json($response, Response::HTTP_CREATED);
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

        /** @var UserListResponse $response */
        $response = $this->dispatcher->dispatchQuery(new ListUsersQuery(
            UserRole::tryFromString(\is_string($role) ? $role : null),
        ));

        return $this->json($response, Response::HTTP_OK);
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
                new OA\Property(
                    property: 'role',
                    type: 'string',
                    enum: ['ROLE_USER', 'ROLE_MODERATOR', 'ROLE_ADMIN'],
                    example: 'ROLE_USER',
                ),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ],
        ),
    )]
    #[OA\Response(response: 201, description: 'Użytkownik utworzony')]
    #[OA\Response(response: 400, description: 'Błąd walidacji')]
    public function create(JsonBody $body): JsonResponse
    {
        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new CreateUserCommand(
            email: Email::fromString($body->string('email')),
            displayName: DisplayName::fromString($body->string('displayName')),
            bio: Bio::fromOptionalString($body->optionalString('bio'), $this->bioMaxLength),
            role: UserRole::tryFromString($body->optionalString('role')),
            password: PlainPassword::fromOptionalString($body->optionalString('password')),
        ));

        return $this->json($response, Response::HTTP_CREATED);
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
    public function show(UserId $userId): JsonResponse
    {
        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchQuery(new GetUserQuery($userId));

        return $this->json($response, Response::HTTP_OK);
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
    public function updateProfile(JsonBody $body, UserId $userId): JsonResponse
    {
        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new UpdateProfileCommand(
            userId: $userId,
            bio: Bio::fromOptionalString($body->optionalString('bio'), $this->bioMaxLength),
            displayName: DisplayName::fromOptionalString($body->optionalString('displayName')),
        ));

        return $this->json($response, Response::HTTP_OK);
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
    public function assignRole(JsonBody $body, UserId $userId): JsonResponse
    {
        /** @var UserResponse $response */
        $response = $this->dispatcher->dispatchCommand(new AssignRoleCommand(
            userId: $userId,
            role: UserRole::fromString($body->string('role')),
        ));

        return $this->json($response, Response::HTTP_OK);
    }

    private function json(JsonHttpResponse $response, int $successCode): JsonResponse
    {
        return new JsonResponse($response->toArray(), $response->httpStatus($successCode));
    }
}

<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Functional;

use Mainfreme\UserProfile\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserProfileRestControllerTest extends WebTestCase
{
    private string $usersRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usersRoot = (new TestKernel('test', true))->getUsersRoot();

        if (is_dir($this->usersRoot)) {
            $this->removeDirectory($this->usersRoot);
        }

        mkdir($this->usersRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->usersRoot)) {
            $this->removeDirectory($this->usersRoot);
        }

        parent::tearDown();
    }

    public function test_create_user_with_bio_and_role(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'jan@example.com',
                'displayName' => 'Jan Kowalski',
                'bio' => 'Lubię Symfony',
                'role' => 'ROLE_USER',
                'password' => 'secret123',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('success', $payload['status']);
        self::assertSame('jan@example.com', $payload['user']['email']);
        self::assertSame('Lubię Symfony', $payload['user']['bio']);
        self::assertSame('ROLE_USER', $payload['user']['role']);
    }

    public function test_update_profile_changes_bio(): void
    {
        $client = static::createClient();
        $userId = $this->createUser($client, 'anna@example.com', 'Stare bio', 'ROLE_USER');

        $client->request(
            'PUT',
            '/api/users/'.$userId.'/profile',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'bio' => 'Nowe bio',
                'displayName' => 'Anna Nowak',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('Nowe bio', $payload['user']['bio']);
        self::assertSame('Anna Nowak', $payload['user']['displayName']);
        self::assertSame('ROLE_USER', $payload['user']['role']);
    }

    public function test_assign_role_updates_assigned_role(): void
    {
        $client = static::createClient();
        $userId = $this->createUser($client, 'admin.kandydat@example.com', 'Bio', 'ROLE_USER');

        $client->request(
            'PUT',
            '/api/users/'.$userId.'/role',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['role' => 'ROLE_ADMIN'], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('ROLE_ADMIN', $payload['user']['role']);
    }

    public function test_show_returns_not_found_for_missing_user(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/550e8400-e29b-41d4-a716-446655440000');

        self::assertResponseStatusCodeSame(404);
    }

    public function test_role_tree_returns_hierarchy(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/roles');

        self::assertResponseIsSuccessful();

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('success', $payload['status']);
        self::assertIsArray($payload['roles']);
        self::assertNotEmpty($payload['roles']);
        self::assertIsArray($payload['roles'][0]);
        self::assertSame('ROLE_ADMIN', $payload['roles'][0]['role']);
        self::assertSame('Administrator', $payload['roles'][0]['label']);
        self::assertIsArray($payload['roles'][0]['children']);
        self::assertIsArray($payload['roles'][0]['children'][0]);
        self::assertSame('ROLE_MODERATOR', $payload['roles'][0]['children'][0]['role']);
        self::assertIsArray($payload['roles'][0]['children'][0]['children']);
        self::assertIsArray($payload['roles'][0]['children'][0]['children'][0]);
        self::assertSame('ROLE_USER', $payload['roles'][0]['children'][0]['children'][0]['role']);
    }

    public function test_create_role_appends_to_tree(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/roles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'role' => 'ROLE_EDITOR',
                'label' => 'Redaktor',
                'parentRole' => 'ROLE_MODERATOR',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('success', $payload['status']);
        self::assertIsArray($payload['roles']);

        $client->request('GET', '/api/roles');
        $tree = $this->decodeResponse($client->getResponse()->getContent());
        self::assertIsArray($tree['roles'][0]['children'][0]['children']);
        $childRoles = array_map(
            static fn (mixed $node): string => \is_array($node) ? (string) ($node['role'] ?? '') : '',
            $tree['roles'][0]['children'][0]['children'],
        );
        self::assertContains('ROLE_EDITOR', $childRoles);
    }

    public function test_create_and_list_groups(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/groups',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Redakcja',
                'description' => 'Zespół redakcyjny',
                'role' => 'ROLE_MODERATOR',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('success', $payload['status']);
        self::assertIsArray($payload['group']);
        self::assertSame('Redakcja', $payload['group']['name']);
        self::assertSame('ROLE_MODERATOR', $payload['group']['role']);

        $client->request('GET', '/api/groups');
        self::assertResponseIsSuccessful();
        $list = $this->decodeResponse($client->getResponse()->getContent());
        self::assertCount(1, $list['groups']);
        self::assertSame('Redakcja', $list['groups'][0]['name']);
    }

    public function test_list_returns_created_users(): void
    {
        $client = static::createClient();
        $this->createUser($client, 'one@example.com', 'Bio 1', 'ROLE_USER');
        $this->createUser($client, 'two@example.com', 'Bio 2', 'ROLE_MODERATOR');

        $client->request('GET', '/api/users');

        self::assertResponseIsSuccessful();

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('success', $payload['status']);
        self::assertCount(2, $payload['users']);
    }

    public function test_swagger_ui_is_available(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc');

        self::assertResponseIsSuccessful();
    }

    public function test_swagger_json_is_available(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc.json');

        self::assertResponseIsSuccessful();

        $payload = $this->decodeResponse($client->getResponse()->getContent());
        self::assertSame('User Profile API', $payload['info']['title']);
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    private function createUser(KernelBrowser $client, string $email, string $bio, string $role): string
    {
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'displayName' => 'Użytkownik Testowy',
                'bio' => $bio,
                'role' => $role,
            ], \JSON_THROW_ON_ERROR),
        );

        $payload = $this->decodeResponse($client->getResponse()->getContent());

        self::assertSame('success', $payload['status']);
        self::assertIsString($payload['user']['id']);

        return $payload['user']['id'];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(string|false|null $content): array
    {
        self::assertNotFalse($content);
        self::assertNotNull($content);

        $payload = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);

        return $payload;
    }

    private function removeDirectory(string $directory): void
    {
        $items = scandir($directory);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}

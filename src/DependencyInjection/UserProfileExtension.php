<?php

declare(strict_types=1);

namespace SWH\UserProfile\DependencyInjection;

use InvalidArgumentException;
use SWH\UserProfile\Application\Command\CreateUser\CreateUserHandler;
use SWH\UserProfile\Application\Command\UpdateProfile\UpdateProfileHandler;
use SWH\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Domain\User\Port\PasswordHasherInterface;
use SWH\UserProfile\Domain\User\Port\RoleCatalogInterface;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use SWH\UserProfile\Infrastructure\Persistence\FilesystemGroupRepository;
use SWH\UserProfile\Infrastructure\Persistence\FilesystemRoleCatalog;
use SWH\UserProfile\Infrastructure\Persistence\FilesystemUserRepository;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryGroupRepository;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use SWH\UserProfile\Infrastructure\Security\NativePasswordHasher;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class UserProfileExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $roleTree = $this->buildRoleTree($config);
        $allowedRoles = $roleTree->flattenCodes();

        $container->setParameter('user_profile.bio_max_length', $config['bio_max_length']);
        $container->setParameter('user_profile.default_role', $config['default_role']);
        $container->setParameter('user_profile.allowed_roles', $allowedRoles);
        $container->setParameter('user_profile.role_tree', $roleTree->toArray());
        $container->setParameter('user_profile.storage.filesystem.root', $config['storage']['filesystem']['root']);

        $container->setAlias(PasswordHasherInterface::class, NativePasswordHasher::class);

        if ('filesystem' === $config['storage']['driver']) {
            $container->getDefinition(FilesystemUserRepository::class)
                ->setArgument('$rootDirectory', $config['storage']['filesystem']['root']);
            $container->setAlias(UserRepositoryInterface::class, FilesystemUserRepository::class);
            $container->setAlias(RoleCatalogInterface::class, FilesystemRoleCatalog::class);
            $container->setAlias(GroupRepositoryInterface::class, FilesystemGroupRepository::class);
        } else {
            $container->setAlias(UserRepositoryInterface::class, InMemoryUserRepository::class);
            $container->setAlias(RoleCatalogInterface::class, InMemoryRoleCatalog::class);
            $container->setAlias(GroupRepositoryInterface::class, InMemoryGroupRepository::class);
        }

        $container->getAlias(UserRepositoryInterface::class)->setPublic(true);

        $container->getDefinition(CreateUserHandler::class)
            ->setArgument('$defaultRole', $config['default_role'])
            ->setArgument('$bioMaxLength', $config['bio_max_length']);

        $container->getDefinition(UpdateProfileHandler::class)
            ->setArgument('$bioMaxLength', $config['bio_max_length']);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildRoleTree(array $config): RoleTree
    {
        try {
            /** @var list<mixed> $treeConfig */
            $treeConfig = \is_array($config['role_tree'] ?? null) ? array_values($config['role_tree']) : [];
            $roleTree = RoleTree::fromConfig($treeConfig);

            if ($roleTree->isEmpty()) {
                /** @var list<string> $allowedRoles */
                $allowedRoles = $config['allowed_roles'] ?? [];
                $roleTree = RoleTree::fromFlatList($allowedRoles);
            }
        } catch (UserDomainException|InvalidArgumentException $exception) {
            throw new InvalidConfigurationException($exception->getMessage(), 0, $exception);
        }

        if ($roleTree->isEmpty()) {
            throw new InvalidConfigurationException('Role tree (or allowed_roles) cannot be empty.');
        }

        $defaultRole = strtoupper(trim((string) $config['default_role']));

        if (!\in_array($defaultRole, $roleTree->flattenCodes(), true)) {
            throw new InvalidConfigurationException(\sprintf('default_role "%s" is not present in the role tree.', $defaultRole));
        }

        return $roleTree;
    }
}

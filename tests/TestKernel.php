<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests;

use SWH\UserProfile\UserProfileBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new NelmioApiDocBundle();
        yield new UserProfileBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test-secret',
            'test' => true,
            'assets' => [],
            'router' => [
                'utf8' => true,
            ],
            'profiler' => [
                'collect' => false,
            ],
        ]);

        $container->extension('twig', [
            'default_path' => $this->getProjectDir().'/templates',
        ]);

        $container->extension('user_profile', [
            'storage' => [
                'driver' => 'filesystem',
                'filesystem' => [
                    'root' => $this->getUsersRoot(),
                ],
            ],
            'bio_max_length' => 2000,
            'default_role' => 'ROLE_USER',
            'role_tree' => [
                [
                    'role' => 'ROLE_ADMIN',
                    'label' => 'Administrator',
                    'children' => [
                        [
                            'role' => 'ROLE_MODERATOR',
                            'label' => 'Moderator',
                            'children' => [
                                [
                                    'role' => 'ROLE_USER',
                                    'label' => 'Użytkownik',
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/../src/Resources/config/routes.yaml');
    }

    public function getUsersRoot(): string
    {
        return $this->getProjectDir().'/var/test-users';
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }
}

<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile;

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class UserProfileBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        if (!class_exists(NelmioApiDocBundle::class)) {
            return;
        }

        $container->prependExtensionConfig('nelmio_api_doc', [
            'documentation' => [
                'info' => [
                    'title' => 'User Profile API',
                    'description' => 'REST API do edycji profilu użytkownika (Bio) oraz przypisanej roli',
                    'version' => '1.0.0',
                ],
            ],
            'areas' => [
                'default' => [
                    'path_patterns' => ['^/api/(users|roles|groups)'],
                    'host_patterns' => [],
                ],
            ],
        ]);
    }
}

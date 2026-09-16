<?php

declare(strict_types=1);

namespace SWH\UserProfile\DependencyInjection;

use SWH\UserProfile\Domain\User\Model\RoleTree;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('user_profile');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('bio_max_length')
                    ->defaultValue(2000)
                    ->min(1)
                ->end()
                ->scalarNode('default_role')
                    ->defaultValue('ROLE_USER')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('allowed_roles')
                    ->info('Używane tylko gdy role_tree jest puste. W przeciwnym razie kody ról pochodzą z drzewa.')
                    ->scalarPrototype()->end()
                    ->defaultValue(['ROLE_USER', 'ROLE_MODERATOR', 'ROLE_ADMIN'])
                ->end()
                ->arrayNode('role_tree')
                    ->info('Hierarchia ról. Select na edycji użytkownika pokazuje całe drzewo.')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('role')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                            ->variableNode('children')->defaultValue([])->end()
                        ->end()
                    ->end()
                    ->defaultValue(RoleTree::defaultConfig())
                ->end()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('driver')
                            ->values(['memory', 'filesystem'])
                            ->defaultValue('filesystem')
                        ->end()
                        ->arrayNode('filesystem')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('root')
                                    ->defaultValue('%kernel.project_dir%/var/users')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

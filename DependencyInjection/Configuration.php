<?php
/**
 * Created by PhpStorm.
 * User: pascal
 * Date: 16.10.16
 * Time: 21:39
 */

namespace Creatissimo\MattermostBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('creatissimo_mattermost');

        $rootNode
            ->children()

                ->scalarNode('webhook')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()

                ->scalarNode('appname')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()

                ->scalarNode('botname')
                ->end()

                ->scalarNode('icon')
                ->end()

                ->scalarNode('channel')
                ->end()

                ->arrayNode('environments')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('webhook')->end()
                            ->scalarNode('appname')->end()
                            ->scalarNode('botname')->end()
                            ->scalarNode('icon')->end()
                            ->scalarNode('channel')->end()
                            ->arrayNode('exclude_exception')->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end();


        return $treeBuilder;
    }
}
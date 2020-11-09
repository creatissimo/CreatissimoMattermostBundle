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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('creatissimo_mattermost');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('creatissimo_mattermost');
        }

        $rootNode
            ->children()

                ->scalarNode('url')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode('botAccessToken')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode('appName')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode('channel')
                ->end()
                ->arrayNode('environments')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('url')->end()
                            ->scalarNode('botAccessToken')->end()
                            ->scalarNode('channel')->end()
                            ->booleanNode('enable')->defaultTrue()->end()
                            ->arrayNode('terminate')
                                ->children()
                                    ->booleanNode('enable')->defaultTrue()->end()
                                    ->arrayNode('exclude_exitcode')
                                        ->prototype('integer')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('exception')
                                ->children()
                                    ->booleanNode('enable')->defaultTrue()->end()
                                    ->booleanNode('trace')->defaultTrue()->end()
                                    ->arrayNode('exclude_class')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}

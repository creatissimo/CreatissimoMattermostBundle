<?php
namespace Creatissimo\MattermostBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CreatissimoMattermostExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $definition = $container->getDefinition('mattermost.service');
        $definition->addMethodCall('setConfiguration', array($config));
        $definition->addMethodCall('setWebhook', array($config['webhook']));
        $definition->addMethodCall('setAppname', array($config['appname']));
        $definition->addMethodCall('setEnvironmentConfigurations', array($config['environments']));
    }
}
<?php

namespace Fgms\SpecialOffersBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class FgmsSpecialOffersExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        //  This allows us to inject only the relevant config into
        //  the EmailNotificationListener, which makes it more testable
        $notifications = $config['notifications'];
        unset($config['notifications']);
        $container->setParameter('fgms_special_offers.config',$config);
        $container->setParameter('fgms_special_offers.notifications_config',$notifications);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}

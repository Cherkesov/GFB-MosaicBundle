<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 18:22
 */

namespace GFB\MosaicBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class GFBMosaicExtension
 * @package GFB\MosaicBundle\DependencyInjection
 */
class GFBMosaicExtension extends Extension
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $cfg = $this->processConfiguration($configuration, $config);

        foreach ($cfg as $propKey => $propVal) {
            $container->setParameter("gfb_mosaic.{$propKey}", $propVal);
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('admin.yml');
    }
}
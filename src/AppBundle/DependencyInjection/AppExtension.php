<?php
/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 14/08/2015
 * Time: 15:30
 */

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder\Finder;

class AppExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('app.host', $config['host']);
        $container->setParameter('app.port', $config['port']);
        $container->setParameter('app.path', $config['path']);
        $container->setParameter('app.username', $config['username']);
        $container->setParameter('app.password', $config['password']);
        $container->setParameter('app.home_directory', $config['home_directory']);

        $this->loadServices($container, __DIR__ . '/../Resources/config/services');
    }

    /**
     * Method to load the xml services files located in $directory folder (recursively)
     *
     * @param ContainerBuilder $container
     * @param $directory
     */
    private function loadServices(ContainerBuilder $container, $directory)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator($directory));

        $finder = new Finder();
        $finder->files()
            ->name('*.xml')
            ->in($directory);

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $loader->load($file->getRelativePathName());
        }
    }
}
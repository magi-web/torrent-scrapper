<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ScrappersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $mainServiceId = 'app.scrapper.engine';
        if ($container->hasDefinition($mainServiceId)) {
            $definition = $container->getDefinition(
                $mainServiceId
            );

            $taggedServices = $container->findTaggedServiceIds(
                'scrapper.client'
            );

            foreach ($taggedServices as $serviceId => $tagAttributes) {
                foreach ($tagAttributes as $attributes) {
                    $definition->addMethodCall(
                        'addClient',
                        array(new Reference($serviceId), $attributes['alias'])
                    );
                }
            }
        }
    }
}

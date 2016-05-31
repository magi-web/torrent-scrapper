<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\ScrappersCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ScrappersCompilerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}

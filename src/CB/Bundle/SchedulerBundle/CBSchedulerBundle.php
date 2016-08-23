<?php

namespace CB\Bundle\SchedulerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use CB\Bundle\SchedulerBundle\DependencyInjection\Compiler\SchedulerProviderPass;
class CBSchedulerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SchedulerProviderPass());
    }
}

<?php

namespace Wearejust\GrumPHPExtra\Extension;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wearejust\GrumPHPExtra\Task\PhpCsAutoFixer;
use Wearejust\GrumPHPExtra\Task\PhpCsAutoFixerV2;
use Wearejust\GrumPHPExtra\Task\Phpdoc;

class Loader implements ExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('task.php_cs_auto_fixer', PhpCsAutoFixer::class)
            ->addArgument(new Reference('config'))
            ->addArgument(new Reference('process_builder'))
            ->addArgument(new Reference('formatter.phpcsfixer'))
            ->addTag('grumphp.task', ['config' => 'php_cs_auto_fixer']);

        $container->register('task.php_cs_auto_fixerv2', PhpCsAutoFixerV2::class)
            ->addArgument(new Reference('config'))
            ->addArgument(new Reference('process_builder'))
            ->addArgument(new Reference('formatter.phpcsfixer'))
            ->addTag('grumphp.task', ['config' => 'php_cs_auto_fixerv2']);

        $container->register('task.phpdoc', Phpdoc::class)
            ->addArgument(new Reference('config'))
            ->addArgument(new Reference('process_builder'))
            ->addArgument(new Reference('formatter.raw_process'))
            ->addTag('grumphp.task', ['config' => 'phpdoc']);
    }
}

<?php

namespace Wearejust\GrumPHPExtra\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCsFixer;

/**
 * Php-cs-fixer task.
 */
class PhpCsAutoFixer extends PhpCsFixer
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php_cs_auto_fixer';
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();
        $this->formatter->resetCounter();

        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');
        $arguments->addOptionalArgument('--level=%s', $config['level']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--config-file=%s', $config['config_file']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalCommaSeparatedArgument('--fixers=%s', $config['fixers']);
        $arguments->add('fix');

        if ($context instanceof RunContext && $config['config_file'] !== null) {
            return $this->runOnAllFiles($context, $arguments);
        }

        return $this->runOnChangedFiles($context, $arguments, $files);
    }
}

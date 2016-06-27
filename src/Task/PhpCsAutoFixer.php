<?php

namespace Wearejust\GrumPHPExtra\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
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

    /**
     * @param ContextInterface           $context
     * @param ProcessArgumentsCollection $arguments
     *
     * @return TaskResult
     */
    private function runOnAllFiles(ContextInterface $context, ProcessArgumentsCollection $arguments)
    {
        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $messages = array($this->formatter->format($process));
            $suggestions = array($this->formatter->formatSuggestion($process));
            $errorMessage = $this->formatter->formatErrorMessage($messages, $suggestions);

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param ContextInterface           $context
     * @param ProcessArgumentsCollection $arguments
     * @param FilesCollection            $files
     *
     * @return TaskResult
     */
    private function runOnChangedFiles(
        ContextInterface $context,
        ProcessArgumentsCollection $arguments,
        FilesCollection $files
    ) {
        $hasErrors = false;
        $messages = array();
        $suggestions = array();

        foreach ($files as $file) {
            $fileArguments = new ProcessArgumentsCollection($arguments->getValues());
            $fileArguments->add($file);
            $process = $this->processBuilder->buildProcess($fileArguments);
            $process->run();

            if (!$process->isSuccessful()) {
                $hasErrors = true;
                $messages[] = $this->formatter->format($process);
                $suggestions[] = $this->formatter->formatSuggestion($process);
            }
        }

        if ($hasErrors) {
            $errorMessage = $this->formatter->formatErrorMessage($messages, $suggestions);

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}

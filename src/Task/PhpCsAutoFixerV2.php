<?php

namespace Wearejust\GrumPHPExtra\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCsFixerV2;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Php-cs-fixerv2 task.
 */
class PhpCsAutoFixerV2 extends PhpCsFixerV2
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'php_cs_auto_fixerv2';
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $this->formatter->resetCounter();

        $process = $this->runProcess($context, $config, $files, true);

        if (!$process->isSuccessful()) {

            $toAdd = $files->map(function(SplFileInfo $file) {
                return $file->getRelativePathname();
            });

            $this->runProcess($context, $config, $files, false);

            exec(sprintf('git add %s', implode(' ', $toAdd->toArray())));

            $process = $this->runProcess($context, $config, $files, false);
            $messages = [$this->formatter->format($process)];
            $suggestions = [$this->formatter->formatSuggestion($process)];
            $errorMessage = $this->formatter->formatErrorMessage($messages, $suggestions);

            return TaskResult::createNonBlockingFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param \GrumPHP\Task\Context\ContextInterface $context
     * @param                                        $config
     * @param                                        $files
     * @param                                        $dryRun
     *
     * @return \GrumPHP\Collection\ProcessArgumentsCollection
     */
    private function runProcess(ContextInterface $context, $config, $files, $dryRun)
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');

        if ($dryRun) {
            $arguments->add('--dry-run');
        }

        $arguments->addOptionalArgument('--allow-risky=%s', $config['allow_risky'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--cache-file=%s', $config['cache_file']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);

        if ($rules = $config['rules']) {
            $arguments->add(sprintf(
                '--rules=%s',
                // Comma-delimit rules if specified as a list; otherwise JSON-encode.
                array_values($rules) === $rules ? implode(',', $rules) : json_encode($rules)
            ));
        }

        $canUseIntersection = !($context instanceof RunContext) && $config['config_contains_finder'];

        $arguments->addOptionalArgument('--using-cache=%s', $config['using_cache'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--path-mode=intersection', $canUseIntersection);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalArgument('--diff', $config['diff']);
        $arguments->add('fix');

        if ($context instanceof GitPreCommitContext || !$config['config_contains_finder']) {
            $arguments->addFiles($files);
        }

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        return $process;
    }
}

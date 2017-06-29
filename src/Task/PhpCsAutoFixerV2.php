<?php

namespace Wearejust\GrumPHPExtra\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCsFixerV2;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php-cs-fixerv2 task.
 */
class PhpCsAutoFixerV2 extends PhpCsFixerV2
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php_cs_auto_fixerv2';
    }


    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $this->formatter->resetCounter();

        $arguments = $this->createProcess($config, true);

        if ($context instanceof RunContext && $config['config'] !== null) {
            $result = $this->runOnAllFiles($context, $arguments);
        }else {
            $result = $this->runOnChangedFiles($context, $arguments, $files);
        }

        if ($result->hasFailed()) {
            $arguments = $this->createProcess($config, false);

            if ($context instanceof RunContext && $config['config'] !== null) {
                $this->runOnAllFiles($context, $arguments);
            }else {
                $this->runOnChangedFiles($context, $arguments, $files);
            }
        }

        return $result;
    }

    /**
     * @param      $config
     * @param bool $dryRun
     *
     * @return \GrumPHP\Collection\ProcessArgumentsCollection
     */
    private function createProcess($config, $dryRun)
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');
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

        $arguments->addOptionalArgument('--using-cache=%s', $config['using_cache'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--path-mode=%s', $config['path_mode']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalArgument('--diff', $config['diff']);
        $arguments->addOptionalArgument('--dry-run', $dryRun);
        $arguments->add('fix');

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function runOnChangedFiles(
        ContextInterface $context,
        ProcessArgumentsCollection $arguments,
        FilesCollection $files
    ) {
        $result = parent::runOnChangedFiles($context, $arguments, $files);
        foreach ($files as $file) {
            exec(sprintf('git add %s', $file->getRelativePathname()));
        }
        if ($files) {
            // @todo Why to this moment commit is already done,
            // if the whole command is supposed to be run as pre-commit hook (i.e., before commit)?
            exec('git commit --amend --no-edit');
        }
        return $result;
    }
}

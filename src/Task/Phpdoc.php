<?php

namespace Wearejust\GrumPHPExtra\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\AbstractExternalTask;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SimpleXMLElement;

/**
 * Phpdoc task
 */
class Phpdoc extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phpdoc';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'config_file' => null,
                'target_folder' => null,
                'cache_folder' => null,
                'filename' => null,
                'directory' => null,
                'encoding' => null,
                'extensions' => null,
                'ignore' => null,
                'ignore_tags' => null,
                'ignore_symlinks' => null,
                'markers' => null,
                'title' => null,
                'force' => null,
                'visibility' => null,
                'default_package_name' => null,
                'source_code' => null,
                'progress_bar' => null,
                'template' => null,
                'quiet' => null,
                'ansi' => null,
                'no_ansi' => null,
                'no_interaction' => null]
        );

        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('target_folder', ['null', 'string']);
        $resolver->addAllowedTypes('cache_folder', ['null', 'string']);
        $resolver->addAllowedTypes('filename', ['null', 'string']);
        $resolver->addAllowedTypes('directory', ['null', 'string']);
        $resolver->addAllowedTypes('encoding', ['null', 'string']);
        $resolver->addAllowedTypes('extensions', ['null', 'string']);
        $resolver->addAllowedTypes('ignore', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_tags', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_symlinks', ['null', 'string']);
        $resolver->addAllowedTypes('markers', ['null', 'string']);
        $resolver->addAllowedTypes('title', ['null', 'string']);
        $resolver->addAllowedTypes('force', ['null', 'bool']);
        $resolver->addAllowedTypes('visibility', ['null', 'string']);
        $resolver->addAllowedTypes('default_package_name', ['null', 'string']);
        $resolver->addAllowedTypes('source_code', ['null', 'bool']);
        $resolver->addAllowedTypes('progress_bar', ['null', 'bool']);
        $resolver->addAllowedTypes('template', ['null', 'string']);
        $resolver->addAllowedTypes('quiet', ['null', 'bool']);
        $resolver->addAllowedTypes('ansi', ['null', 'bool']);
        $resolver->addAllowedTypes('no_ansi', ['null', 'bool']);
        $resolver->addAllowedTypes('no_interaction', ['null', 'bool']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        if ($context instanceof GitPreCommitContext) {
            $directoriesToDoc = $this->getDirectoriesToDoc($config);
            $filenamesToDoc =  $this->getFilenamesToDoc($config);

            $filesMatchingContext = array();

            array_walk(
                $directoriesToDoc, function (&$value) {
                    $value = pathinfo($value)['basename'];
                    $value = ltrim($value, './');
                    $value = rtrim($value, './');
                }
            );

            foreach ($filenamesToDoc as $filenameToDoc) {
                if ($context->getFiles()->name(pathinfo($filenameToDoc)['basename'])->count() > 0) {
                    $filesMatchingContext[] = $context->getFiles()->name(pathinfo($filenameToDoc)['basename']);
                }
            }

            if ($context->getFiles()->paths($directoriesToDoc)->count() > 0) {
                $filesMatchingContext[] = $context->getFiles()->paths($directoriesToDoc);
            }

            if (empty($filesMatchingContext)) {
                return TaskResult::createSkipped($this, $context);
            }
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpdoc');
        $arguments->addOptionalArgumentWithSeparatedValue('--config', $config['config_file']);
        $arguments->addOptionalArgumentWithSeparatedValue('--target', $config['target_folder']);
        $arguments->addOptionalArgumentWithSeparatedValue('--cache-folder', $config['cache_folder']);
        $arguments->addOptionalArgumentWithSeparatedValue('--filename', $config['filename']);
        $arguments->addOptionalArgumentWithSeparatedValue('--directory', $config['directory']);
        $arguments->addOptionalArgumentWithSeparatedValue('--encoding', $config['encoding']);
        $arguments->addOptionalArgumentWithSeparatedValue('--extensions', $config['extensions']);
        $arguments->addOptionalArgumentWithSeparatedValue('--ignore', $config['ignore']);
        $arguments->addOptionalArgumentWithSeparatedValue('--ignore-tags', $config['ignore_tags']);
        $arguments->addOptionalArgument('--ignore-symlinks', $config['ignore_symlinks']);
        $arguments->addOptionalArgumentWithSeparatedValue('--markers', $config['markers']);
        $arguments->addOptionalArgumentWithSeparatedValue('--title', $config['title']);
        $arguments->addOptionalArgument('--force', $config['force']);
        $arguments->addOptionalArgumentWithSeparatedValue('--visibility', $config['visibility']);
        $arguments->addOptionalArgumentWithSeparatedValue('--defaultpackagename', $config['default_package_name']);
        $arguments->addOptionalArgument('--sourcecode', $config['source_code']);
        $arguments->addOptionalArgument('--progressbar', $config['progress_bar']);
        $arguments->addOptionalArgumentWithSeparatedValue('--template', $config['template']);
        $arguments->addOptionalArgument('--quiet', $config['progress_bar']);
        $arguments->addOptionalArgument('--ansi', $config['progress_bar']);
        $arguments->addOptionalArgument('--no-ansi', $config['progress_bar']);
        $arguments->addOptionalArgument('--no-interaction', $config['progress_bar']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        if ($process->isSuccessful() && $context instanceof GitPreCommitContext) {
            $trueTargetFolder = $this->getTrueTargetFolder($config);

            $argumentsGit = $this->processBuilder->createArgumentsForCommand('git');
            $argumentsGit->addOptionalArgumentWithSeparatedValue('add', $trueTargetFolder . '*');

            $processGit = $this->processBuilder->buildProcess($argumentsGit);
            $processGit->run();

            if (!$processGit->isSuccessful()) {
                return TaskResult::createFailed($this, $context, $this->formatter->format($processGit));
            }
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param $config
     * @return array
     */
    private function getFilenamesToDoc($config)
    {
        $filenames = array();
        $configFileXML = null;

        if (empty($config['config_file']) && file_exists($config['config_file'])) {
            $configFileXML = new SimpleXMLElement(file_get_contents($config['config_file']));
            $filenames = array_merge($filenames, (array)$configFileXML->files->filename);
        } elseif (file_exists('phpdoc.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.xml'));
            $filenames = array_merge($filenames, (array)$configFileXML->files->filename);
        } elseif (file_exists('phpdoc.dist.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.dist.xml'));
            $filenames = array_merge($filenames, (array)$configFileXML->files->filename);
        }

        if (!empty($config['filename'])) {
            $filenames = array_merge($filenames, explode(',', $config['filename']));
        }

        return $filenames;
    }

    /**
     * @param $config
     * @return array
     */
    private function getDirectoriesToDoc(&$config)
    {
        $directories = array();
        $configFileXML = null;

        if (empty($config['config_file']) && file_exists($config['config_file'])) {
            $configFileXML = new SimpleXMLElement(file_get_contents($config['config_file']));
            $directories = array_merge($directories, (array)$configFileXML->files->directory);
        } elseif (file_exists('phpdoc.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.xml'));
            $directories = array_merge($directories, (array)$configFileXML->files->directory);
        } elseif (file_exists('phpdoc.dist.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.dist.xml'));
            $directories = array_merge($directories, (array)$configFileXML->files->directory);
        }

        if (!empty($config['directory'])) {
            $directories = array_merge($directories, explode(',', $config['directory']));
        }

        return $directories;
    }

    /**
     * @param $config
     * @return null|string
     */
    private function getTrueTargetFolder(&$config)
    {
        $trueTargetFolder = null;
        $configFileXML = null;

        if (($config['target_folder'])) {
            $trueTargetFolder = $config['target_folder'];
        } elseif ($config['config_file'] && file_exists($config['config_file'])) {
            $configFileXML = new SimpleXMLElement(file_get_contents($config['config_file']));
            $trueTargetFolder = $configFileXML->transformer->target;
        } elseif (file_exists('phpdoc.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.xml'));
            $trueTargetFolder = $configFileXML->transformer->target;
        } elseif (file_exists('phpdoc.dist.xml')) {
            $configFileXML = new SimpleXMLElement(file_get_contents('phpdoc.dist.xml'));
            $trueTargetFolder = $configFileXML->transformer->target;
        } else {
            $trueTargetFolder = 'output/';
        }

        $trueTargetFolder .= (strlen($trueTargetFolder) - 1 === strrpos($trueTargetFolder, '/') ? '' : '/');

        return $trueTargetFolder;
    }
}

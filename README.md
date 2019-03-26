# GrumPHP Extra tasks

This package is brought alive to extend the functionalities of the already existing [GrumPHP](https://github.com/phpro/grumphp).

## Installation

The easiest way to install this package is through composer:
	
	composer require --dev wearejust/grumphp-extra-tasks

Add the extension loader to your `grumphp.yml`

```yaml
parameters:
    extensions:
        - Wearejust\GrumPHPExtra\Extension\Loader
```

## Usage

### PhpCsAutoFixer

In [grumphp core](https://github.com/phpro/grumphp)
it is not possible to use the php-cs-fixer [to auto fix your files according to your config](https://github.com/phpro/grumphp/issues/110).
This package adds this missing feature.

The configuration of this custom task is the same as [the already existing task](https://github.com/phpro/grumphp/blob/master/doc/tasks/php_cs_fixer.md),
only specify the new `php_cs_auto_fixer` configuration key. For example:

```yaml
parameters:
    tasks:
        php_cs_auto_fixer:
            config_file: .php_cs
            config: ~
            fixers: []
            level: ~
            verbose: true
```

### PhpCsAutoFixerV2

In [grumphp core](https://github.com/phpro/grumphp)
it is not possible to use the php-cs-fixer [to auto fix your files according to your config](https://github.com/phpro/grumphp/issues/110).
This package adds this missing feature.

The configuration of this custom task is the same as [the already existing task](https://github.com/phpro/grumphp/blob/master/doc/tasks/php_cs_fixer.md),
only specify the new `php_cs_auto_fixerv2` configuration key. For example:

```yaml
parameters:
    tasks:
        php_cs_auto_fixerv2:
            allow_risky: false
            cache_file: ~
            config: ~
            rules: []
            using_cache: true
            path_mode: ~
            verbose: true
            diff: false
            triggered_by: ['php']
```

### Phpdoc

In [grumphp core](https://github.com/phpro/grumphp)
there is no phpdoc tasks [to generate phpDoc if necessary (and add it) before commit](https://github.com/phpro/grumphp/pull/253).
This package adds this missing feature.

To use this task, just specify if inside `grumphp.yml` in the `tasks:` section.

```yaml
parameters:
    tasks:
        phpdoc:
            config_file: ~
            target_folder: ~
            cache_folder: ~
            filename: ~
            directory: ~
            encoding: ~
            extensions: ~
            ignore: ~
            ignore_tags: ~
            ignore_symlinks: ~
            markers: ~
            title: ~
            force: ~
            visibility: ~
            default_package_name: ~
            source_code: ~
            progress_bar: ~
            template: ~
            quiet: ~
            ansi: ~
            no_ansi: ~
            no_interaction: ~
```

**config_file**
*Default: `null`*

Without config_file parameter phpdoc will search for a phpdoc.dist.xml config file.
This file can be overload by phpdoc.xml.
If no file found, no config file will be used.

**target_folder**
*Default: `null`*

Without this parameter the doc will be generated in an `output/` folder.

**cache_folder**
*Default: `null`*

Without this parameter, cache will be placed in the `target_folder`.

**filename**
*Default: `null`*

Comma separated file list to documents.

**directory**
*Default: `null`*
Comma separated directory list to documents.

**encoding**
*Default: `null`*

Without this parameter, encoding will be `'UTF-8'`.

**extensions**
*Default: `null`*

Comma separated file extension list. Contains extension of file to parse.
Without this parameter, parsed file are :
* php
* php3
* phtml

**ignore**
*Default: `null`*

Comma separated list of paths to skip when parsing.

**ignore_tags**
*Default: `null`*

Comma separated list of tags to skip when parsing.

**ignore_symlinks**
*Default: `false`*
Tells the parser not to follow symlinks.

**markers**
*Default: `null`*

Provide a comma-separated list of markers to parse (TODO ...).

**title**
*Default: `null`*

Specify a title for the documentation.

**force**
*Default: `null`*

Ignore exceptions and continue parsing.

**visibility**
*Default: `null`*

Provide a comma-separated list of visibility scopes to parse.
This parameter may be used to tell phpDocumentor to only parse public properties and methods, or public and protected.

**default_package_name**
*Default: `null`*

Default package name

**source_code**
*Default: `null`*

When this parameter is provided the parser will add a compressed, base64-encoded version of the parsed file’s source as child element of the <file> element.
This information can then be picked up by the transformer to generate a syntax highlighted view of the file’s source code and even have direct links to specific lines.

**progress_bar**
*Default: `null`*

Display progress bar during the process.

**template**
*Default: `null`*

Specify a template to use. Without this parameter the template named "clean" will be used.

**quiet**
*Default: `null`*

With this option, only errors will be displayed.

**ansi**
*Default: `null`*

Force ANSI output.

**no_ansi**
*Default: `null`*

Disable ANSI output.

**no_interaction**
*Default: `null`*

##License
This package is licensed under the MIT License.  		

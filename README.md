#GrumPHP Extra tasks
This package is brought alive to extend the functionalities of the already existing [GrumPHP](https://github.com/phpro/grumphp). 

##Installation
Add this package using composer, firstly add the packages repository


  Then, require the this repository

```json
	composer require --dev wearejust/grumphp-extra-tasks
```

##Usage

At this moment there is one extra task. This task is created to implemented the existing functionality of the [php-cs-fixer](https://github.com/phpro/grumphp/blob/master/doc/tasks/php_cs_fixer.md) and extend it with autofixing your files.

### PhpCsAutoFixer
In this main package it is not possible to use the php-cs-fixer [to auto fix your files according to your config](https://github.com/phpro/grumphp/issues/110). This package adds this missing feature.

The configuration of this custom task is the same as the already [existing](https://github.com/phpro/grumphp/blob/master/doc/tasks/php_cs_fixer.md) task, only specify the new `php_cs_auto_fixer` configuration key. For example:

```yaml
parameters:
    tasks:
        php_cs_auto_fixer:
            config_file: .php_cs
            config: ~
            fixers: []
            level: ~
            verbose: true
    extensions:
        - Wearejust\GrumPHPExtra\Extension\Loader
```
##License
This package is licensed under the MIT License.  		

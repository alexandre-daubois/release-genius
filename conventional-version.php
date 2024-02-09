#!/usr/bin/env php
<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (\PHP_VERSION_ID < 80200) {
    fwrite(
        \STDERR,
        \sprintf("You must use at least PHP 8.2.0, and you're using PHP %s. Please consider upgrading your PHP binary.", \PHP_VERSION)
    );

    exit(1);
}

if (isset($GLOBALS['_composer_autoload_path'])) {
    \define('LOCAL_COMPOSER_INSTALL', $GLOBALS['_composer_autoload_path']);
} else {
    foreach ([__DIR__.'/../../autoload.php', __DIR__.'/../vendor/autoload.php', __DIR__.'/vendor/autoload.php'] as $file) {
        if (\file_exists($file)) {
            \define('LOCAL_COMPOSER_INSTALL', $file);

            break;
        }
    }

    unset($file);
}

if (!defined('LOCAL_COMPOSER_INSTALL')) {
    \fwrite(
        \STDERR,
        'Composer has not been setup. Please consider running `composer install`.'.\PHP_EOL
    );

    exit(1);
}

require LOCAL_COMPOSER_INSTALL;

use ConventionalVersion\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

require __DIR__.'/vendor/autoload.php';

(new SingleCommandApplication())
    ->setName('Conventional Version')
    ->setVersion('0.1')
    ->addArgument('release type', InputArgument::REQUIRED, 'The type of release to be generated (major, minor, patch)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        try {
            return Runner::run($input, $output);
        } catch (Throwable) {
            return Command::FAILURE;
        }
    })
    ->run();

<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

/**
 * This is just a simple Wrapper around Symfony's Process component. This will
 * allow us to mock some of the git commands in tests.
 */
interface CommandRunnerInterface
{
    public function run(string $cmd): string;
}

<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

use Symfony\Component\Process\Process;

/**
 * This is just a simple Wrapper around Symfony's Process component. This will
 * allow us to mock some of the git commands in tests.
 */
class CommandRunner implements CommandRunnerInterface
{
    /**
     * Runs a command and returns its output. If the command fails, an exception
     * is thrown.
     *
     * @throws \RuntimeException
     */
    public function run(string $cmd): string
    {
        $process = Process::fromShellCommandline($cmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException();
        }

        return $process->getOutput();
    }
}

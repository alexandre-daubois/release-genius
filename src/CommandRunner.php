<?php

namespace ConventionalVersion;

use Symfony\Component\Process\Process;

/**
 * This is just a simple Wrapper around Symfony's Process component. This will
 * allow us to mock some of the git commands in tests.
 */
class CommandRunner implements CommandRunnerInterface
{
    /**
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

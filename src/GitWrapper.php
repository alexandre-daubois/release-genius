<?php

namespace ConventionalVersion;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;

final class GitWrapper
{
    private ?string $executable;

    public function checkRequirements(SymfonyStyle $io): void
    {
        $executableFinder = new ExecutableFinder();

        $this->executable = $executableFinder->find('git');
        if (null === $this->executable) {
            throw new \RuntimeException('The "git" executable could not be found. Is it installed in your system?');
        }

        $io->writeln(' <fg=green;options=bold>✓</> <options=bold>git executable was found</>');

        if (!\is_dir('.git')) {
            throw new \RuntimeException('The ".git" directory could not be found. Are you sure you are in a Git repository?');
        }

        $io->writeln(' <fg=green;options=bold>✓</> <options=bold>.git directory found</>');

        if (!\is_readable('.git')) {
            throw new \RuntimeException('The ".git" directory is not readable. Please check your file permissions.');
        }

        $io->writeln(' <fg=green;options=bold>✓</> <options=bold>.git directory is readable</>');
    }
}

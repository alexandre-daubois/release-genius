<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;

final class GitWrapper
{
    private ?string $executable;

    public function __construct(private readonly CommandRunnerInterface $commandRunner)
    {
    }

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

        $io->writeln(sprintf(' <fg=green;options=bold>✓</> <options=bold>.git directory found under "%s"</>', \getcwd()));

        if (!\is_readable('.git')) {
            throw new \RuntimeException('The ".git" directory is not readable. Please check your file permissions.');
        }

        $io->writeln(' <fg=green;options=bold>✓</> <options=bold>.git directory is readable</>');
    }

    public function getLatestTag(): Semver
    {
        try {
            $result = $this->commandRunner->run($this->executable.' describe --tags `git rev-list --tags --max-count=1`');
        } catch (\Throwable) {
            throw new \RuntimeException('Could not get the last tag from the repository.');
        }

        if (1 !== preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $result, $matches)) {
            throw new \RuntimeException('The last tag does not follow the SemVer format.');
        }

        return new Semver((int) $matches[1], (int) $matches[2], (int) $matches[3], 'v' === $result[0]);
    }

    public function setExecutable(?string $executable): void
    {
        $this->executable = $executable;
    }
}

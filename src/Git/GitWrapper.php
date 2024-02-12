<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Git;

use ConventionalVersion\Changelog\Changelog;
use ConventionalVersion\CommandRunnerInterface;
use ConventionalVersion\Git\Model\Commit;
use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\Model\Semver;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;

/**
 * This class is a wrapper around the "git" executable. It provides a way to interact with the Git repository
 * from PHP code.
 */
final class GitWrapper
{
    private ?string $executable;

    public function __construct(private readonly CommandRunnerInterface $commandRunner)
    {
    }

    /**
     * This method checks if the requirements for the GitWrapper are met. It
     * checks if the "git" executable is available, if the ".git" directory
     * is present, and if it is readable.
     *
     * If any of these checks fail, an exception is thrown. Otherwise, it
     * prints a success message.
     *
     * This is necessary because to initialize the GitWrapper, we need to
     * have a working Git installation and a Git repository.
     *
     * @throws \RuntimeException
     */
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

    /**
     * This method returns the latest tag from the repository. It does so by
     * running the following command:
     *
     *    git describe --tags `git rev-list --tags --max-count=1`
     *
     * This command returns the latest tag in the repository. If the tag is not
     * in the SemVer format, an exception is thrown.
     *
     * @throws \RuntimeException
     */
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

    /**
     * Get the relevant commits between the last tag and the current HEAD.
     * This is what we use to generate the changelog.
     */
    public function parseRelevantCommits(Semver $lastTag, Semver $nextTag): Changelog
    {
        try {
            $result = $this->commandRunner->run(sprintf('%s log %s..HEAD --oneline', $this->executable, $lastTag));
        } catch (\Throwable $e) {
            throw new \RuntimeException('Could not get the commit messages from the repository: '.$e->getMessage());
        }

        $rawCommits = explode(\PHP_EOL, trim($result));
        $changeLog = new Changelog($lastTag, $nextTag);

        foreach ($rawCommits as $rawCommit) {
            if ('' === $rawCommit) {
                // skip empty lines that may happen when parsing the command output
                continue;
            }

            try {
                $commit = Commit::fromString($rawCommit);
            } catch (\Throwable) {
                $commit = RawCommit::fromString($rawCommit);
            }

            $changeLog->commits[] = $commit;
        }

        return $changeLog;
    }

    /**
     * This method is used to create a new tag in the repository. It does so by
     * running the following command:
     *
     *   git tag -a <version> -m "chore(release): <version>"
     *
     * @throws \RuntimeException
     */
    public function createTag(Semver $version, string $changeLogPath, bool $skipVendors): void
    {
        $existingTags = $this->commandRunner->run(sprintf('%s tag --list', $this->executable));
        if (str_contains($existingTags, $version)) {
            throw new \RuntimeException(sprintf('The tag "%s" already exists.', $version));
        }

        if (file_exists('package.json') && !$skipVendors) {
            $this->commandRunner->run(sprintf('%s add package.json', $this->executable));
        }

        if (file_exists('composer.json') && !$skipVendors) {
            $this->commandRunner->run(sprintf('%s add composer.json', $this->executable));
        }

        $this->commandRunner->run(sprintf('%s add %s', $this->executable, $changeLogPath));
        $this->commandRunner->run(sprintf('%s commit --allow-empty -m "chore(release): %s"', $this->executable, $version));
        $this->commandRunner->run(sprintf('%s tag -a %s -m "%s"', $this->executable, $version, $version));
    }

    /**
     * This method is used to get the URL of a remote. It does so by running the
     * following command:
     *
     *  git remote get-url <remote>
     */
    public function getRemoteUrl(string $remote): string
    {
        return trim($this->commandRunner->run(sprintf('%s remote get-url %s', $this->executable, $remote)));
    }

    /**
     * This method is used to set the "git" executable. It is useful for testing
     * purposes, as it allows us to inject a mock or a stub of the "git" executable.
     *
     * @internal
     */
    public function setExecutable(?string $executable): void
    {
        $this->executable = $executable;
    }
}

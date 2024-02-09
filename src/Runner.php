<?php

namespace ConventionalVersion;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Runner
{
    public function __construct()
    {
    }

    /**
     * @throws \Throwable
     */
    public static function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gitWrapper = new GitWrapper(new CommandRunner());

        try {
            if (null === $release = ReleaseType::tryFrom($input->getArgument('release type'))) {
                throw new \InvalidArgumentException(sprintf('Expected one of "major", "minor", or "patch", but got "%s".', $input->getArgument('release type')));
            }

            $io->section('Checking requirements');
            $gitWrapper->checkRequirements($io);

            $latestTag = $gitWrapper->getLatestTag();
            $nextTag = $latestTag->next($release);

            $io->section(sprintf('Creating a new %s release', $release->value));
            $io->comment(sprintf('The latest tag is: <options=bold>%s</>', $latestTag));
            $io->comment(sprintf('The next tag will be: <options=bold>%s</>', $nextTag));
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            throw $throwable;
        }

        return 0;
    }
}
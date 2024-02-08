<?php

namespace ConventionalVersion;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Runner
{
    /**
     * @throws \Throwable
     */
    public static function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gitWrapper = new GitWrapper();

        try {
            if (null === $release = ReleaseType::tryFrom($input->getArgument('release type'))) {
                throw new \InvalidArgumentException(sprintf('Expected one of "major", "minor", or "patch", but got "%s".', $input->getArgument('release type')));
            }

            $io->info('Checking requirements...');
            $gitWrapper->checkRequirements($io);

            $io->info(sprintf('Creating a new %s release...', $release->value));
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            throw $throwable;
        }

        return 0;
    }
}

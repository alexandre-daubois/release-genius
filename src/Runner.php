<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

use ConventionalVersion\Changelog\MarkdownChangelogDumper;
use ConventionalVersion\Changelog\WritingMode;
use ConventionalVersion\Git\GitWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This class is the entry point of the application.
 */
class Runner
{
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

            if (null === $writingMode = WritingMode::tryFrom($input->getOption('mode'))) {
                throw new \InvalidArgumentException(sprintf('Expected one of "prepend", "append", or "overwrite", but got "%s".', $input->getOption('mode')));
            }

            $path = $input->getOption('path');

            $io->section('Checking requirements');
            $gitWrapper->checkRequirements($io);

            $latestTag = $gitWrapper->getLatestTag();
            $nextTag = $latestTag->next($release);

            $io->section(sprintf('Creating a new %s release', $release->value));
            $io->comment(sprintf('The latest tag is: <options=bold>%s</>', $latestTag));
            $io->comment(sprintf('The next tag will be: <options=bold>%s</>', $nextTag));

            $changelog = $gitWrapper->parseRelevantCommits($latestTag, $nextTag);
            $io->section('Generated changelog');

            $dumper = new MarkdownChangelogDumper();
            $io->write($dumper->dump($changelog));

            $helper = new QuestionHelper();
            $question = new ConfirmationQuestion('Do you confirm this action (y/n) ? ', false);

            $io->warning(sprintf('This will write the changelog to the file "%s" in the current directory. A new git tag "%s" will also be created.', $path, $nextTag));
            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }

            $dumper->dumpToFile($changelog, $path, $writingMode);
            $io->success(sprintf('The changelog has been written to "%s"', $path));
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            throw $throwable;
        }

        return 0;
    }
}

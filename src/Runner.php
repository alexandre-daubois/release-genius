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
use ConventionalVersion\Git\Model\Semver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This class is the entry point of the application.
 */
final class Runner
{
    private static GitWrapper $gitWrapper;
    private static SymfonyStyle $io;

    /**
     * @throws \Throwable
     */
    public static function run(InputInterface $input, OutputInterface $output): int
    {
        Runner::$io = new SymfonyStyle($input, $output);
        Runner::$gitWrapper = new GitWrapper(new CommandRunner());

        try {
            Runner::$io->section('Checking requirements');
            Runner::$gitWrapper->checkRequirements(Runner::$io);

            if (!$input->getOption('init')) {
                return Runner::newRelease($input, $output);
            }

            return Runner::init($input, $output);
        } catch (\Throwable $throwable) {
            Runner::$io->error($throwable->getMessage());

            throw $throwable;
        }
    }

    private static function newRelease(InputInterface $input, OutputInterface $output): int
    {
        $releaseType = $input->getArgument('release type');

        if (null === $releaseType || null === $release = ReleaseType::tryFrom($releaseType)) {
            throw new \InvalidArgumentException('Expected release type to be provided and one of "major", "minor", or "patch".');
        }

        if (null === $writingMode = WritingMode::tryFrom($input->getOption('mode'))) {
            throw new \InvalidArgumentException(sprintf('Expected one of "prepend", "append", or "overwrite", but got "%s".', $input->getOption('mode')));
        }

        $path = $input->getOption('path');

        $latestTag = Runner::$gitWrapper->getLatestTag();
        $nextTag = $latestTag->next($release);

        Runner::$io->section(sprintf('Creating a new %s release', $release->value));
        Runner::$io->comment(sprintf('The latest tag is: <options=bold>%s</>', $latestTag));
        Runner::$io->comment(sprintf('The next tag will be: <options=bold>%s</>', $nextTag));

        $changelog = Runner::$gitWrapper->parseRelevantCommits($latestTag, $nextTag);
        Runner::$io->section('Generated changelog');

        $dumper = new MarkdownChangelogDumper();
        Runner::$io->write($dumper->dump($changelog));

        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion('Do you confirm this action (y/n) ? ', false);

        Runner::$io->warning(sprintf('This will write the changelog to the file "%s" in the current directory. A new git tag "%s" will also be created.', $path, $nextTag));
        if (!$helper->ask($input, $output, $question)) {
            return Command::INVALID;
        }

        $dumper->dumpToFile($changelog, $path, $writingMode);
        Runner::$io->success(sprintf('The changelog has been written to "%s"', $path));

        Runner::$gitWrapper->createTag($nextTag);
        Runner::$io->success(sprintf('The tag "%s" has been created', $nextTag));

        Runner::$io->info("Don't forget to push the tag to the remote repository with the following command:");
        Runner::$io->writeln(sprintf('  <options=bold>git push origin %s</>', $nextTag));

        return Command::SUCCESS;
    }

    private static function init(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getOption('path');

        $questionHelper = new QuestionHelper();
        $question = new Question("\nWhat is the first version of your project? ", '0.1.0');
        $question->setValidator(function (string $answer): Semver {
            try {
                return Semver::fromString($answer);
            } catch (\Throwable) {
                throw new \RuntimeException('The version must follow the SemVer format.');
            }
        });

        $firstVersion = $questionHelper->ask($input, $output, $question);

        $dumper = new MarkdownChangelogDumper();
        $dumper->init($path, $firstVersion);
        Runner::$gitWrapper->createTag($firstVersion);

        Runner::$io->success(sprintf('The changelog has been initialized in "%s"', $path));
        Runner::$io->success(sprintf('The tag "%s" has been created', $firstVersion));

        Runner::$io->note("Don't forget to push the tag to the remote repository with the following command:");
        Runner::$io->writeln('  <options=bold>git push origin && git push origin --tags</>');

        return Command::SUCCESS;
    }
}

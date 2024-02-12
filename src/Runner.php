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
use ConventionalVersion\Git\RemoteAdapter\EmptyRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapterGuesser;
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
        self::$io = new SymfonyStyle($input, $output);
        self::$gitWrapper = new GitWrapper(new CommandRunner());

        try {
            self::$io->section('Checking requirements');
            self::$gitWrapper->checkRequirements(self::$io);

            if (!$input->getOption('init')) {
                return self::newRelease($input, $output);
            }

            return self::init($input, $output);
        } catch (\Throwable $throwable) {
            self::$io->error($throwable->getMessage());

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

        $latestTag = self::$gitWrapper->getLatestTag();
        $nextTag = $latestTag->next($release);

        self::$io->section(sprintf('Creating a new %s release', $release->value));
        self::$io->comment(sprintf('The latest tag is: <options=bold>%s</>', $latestTag));
        self::$io->comment(sprintf('The next tag will be: <options=bold>%s</>', $nextTag));

        $changelog = self::$gitWrapper->parseRelevantCommits($latestTag, $nextTag);

        if ('none' === $input->getOption('remote')) {
            $remoteAdapter = new EmptyRemoteAdapter();
        } else {
            $remoteAdapter = RemoteAdapterGuesser::guess(
                self::$gitWrapper->getRemoteUrl($input->getOption('remote')),
                $input->getOption('remote-type'),
            );
        }

        self::$io->section('Generated changelog');

        $dumper = new MarkdownChangelogDumper($remoteAdapter);
        self::$io->write($dumper->dump($changelog));

        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion('Do you confirm this action (y/n) ? ', false);

        self::$io->warning(sprintf('This will write the changelog to the file "%s" in the current directory. A new git tag "%s" will also be created. If "composer.json" and/or "package.json" files are found, their version number will be updated as well.', $path, $nextTag));
        if (!$helper->ask($input, $output, $question)) {
            return Command::INVALID;
        }

        $dumper->dumpToFile($changelog, $path, $writingMode);
        self::$io->success(sprintf('The changelog has been written to "%s"', $path));

        $skipVendors = $input->getOption('skip-vendors');
        if (file_exists('package.json') && !$skipVendors) {
            try {
                VendorsJsonFileUpdater::update($nextTag, 'package.json');
                self::$io->success('The "package.json" file has been updated');
            } catch (Exception\VendorFileNotFoundException) {
            }
        }

        if (file_exists('composer.json') && !$skipVendors) {
            try {
                VendorsJsonFileUpdater::update($nextTag, 'composer.json');
                self::$io->success('The "composer.json" file has been updated');
            } catch (Exception\VendorFileNotFoundException) {
            }
        }

        self::$gitWrapper->createTag($nextTag, $path, $skipVendors);
        self::$io->success(sprintf('The tag "%s" has been created', $nextTag));

        self::$io->info("Don't forget to push the tag to the remote repository with the following command:");
        self::$io->writeln('  <options=bold>git push && git push --tags</>');

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

        $dumper = new MarkdownChangelogDumper(new EmptyRemoteAdapter());
        $dumper->init($path, $firstVersion);
        self::$gitWrapper->createTag($firstVersion, $path, false);

        self::$io->success(sprintf('The changelog has been initialized in "%s"', $path));
        self::$io->success(sprintf('The tag "%s" has been created', $firstVersion));

        self::$io->note("Don't forget to push the tag to the remote repository with the following command:");
        self::$io->writeln('  <options=bold>git push origin && git push origin --tags</>');

        return Command::SUCCESS;
    }
}

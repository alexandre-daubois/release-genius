<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Changelog;

use ConventionalVersion\Git\Model\Commit;
use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\Model\Semver;
use ConventionalVersion\Git\RemoteAdapter\RemoteAdapterInterface;
use Symfony\Component\Clock\DatePoint;

readonly class MarkdownChangelogDumper implements ChangelogDumperInterface, FileChangelogDumperInterface
{
    public function __construct(private RemoteAdapterInterface $remoteAdapter)
    {
    }

    /**
     * Dumps the changelog to a string, in the Markdown format.
     *
     * @throws \RuntimeException
     */
    public function dump(Changelog $changelog): string
    {
        if (null !== $compare = $this->remoteAdapter->getCompareUrl($changelog->fromVersion, $changelog->toVersion)) {
            $output = sprintf('## [%s](%s) (%s)', ltrim($changelog->toVersion, 'v'), $compare, (new DatePoint())->format('Y-m-d'));
        } else {
            $output = sprintf('## %s (%s)', ltrim($changelog->toVersion, 'v'), (new DatePoint())->format('Y-m-d'));
        }

        $output .= "\n\n";

        if (0 === count($changelog->commits)) {
            $output .= " * _(empty release)_\n";

            return $output;
        }

        foreach ($changelog->commits as $commit) {
            if (null !== $hashUrl = $this->remoteAdapter->getCommitUrl($commit)) {
                $output .= sprintf(' * %s [%s](%s)', $commit->message, $hashUrl, $commit->hash)."\n";

                continue;
            }

            $output .= sprintf(' * %s (%s)', $commit, $commit->hash)."\n";
        }

        return $output;
    }

    /**
     * Three writing modes are supported:
     *
     * - WritingMode::Append: Append the content to the file.
     * - WritingMode::Overwrite: Overwrite the file with the content.
     * - WritingMode::Prepend: Try to find a title in the changelog file and prepend the content to it.
     *
     * The default mode is WritingMode::Prepend.
     *
     * @throws \RuntimeException
     */
    public function dumpToFile(
        Changelog $changelog,
        string $changelogFilePath,
        WritingMode $writingMode = WritingMode::Prepend
    ): void {
        $content = static::dump($changelog);

        $changelogFile = new \SplFileObject($changelogFilePath, $writingMode === WritingMode::Overwrite ? 'w+' : 'a+');

        match ($writingMode) {
            WritingMode::Append => $changelogFile->fwrite("\n".$content),
            WritingMode::Overwrite => $changelogFile->fwrite($content),
            WritingMode::Prepend => $this->prependToFile($changelogFilePath, $content),
        };
    }

    /**
     * Initializes a new changelog file with the given version without any commits.
     */
    public function init(string $changelogFilePath, Semver $firstVersion): void
    {
        $now = (new DatePoint())->format('Y-m-d');
        $changelogFile = new \SplFileObject($changelogFilePath, 'w+');
        $changelogFile->fwrite(<<<MARKDOWN
# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## $firstVersion ($now)

 * Initial release

MARKDOWN);
    }

    /**
     * This method tries to find a title in the changelog file and prepend the content to it.
     * The title must start with a `##` followed by a space and the version number.
     */
    private function prependToFile(string $changelogFilePath, string $content): void
    {
        $fileContent = file_get_contents($changelogFilePath);
        if (false === $fileContent) {
            throw new \RuntimeException(sprintf('Could not read the file "%s".', $changelogFilePath));
        }

        // this regex will match a title like `## v1.0.0` or `## 1.0.0`
        // it also supported urlized titles like `## [v1.0.0](...)`
        preg_match('/^##\s(\[)?(v)?\d+\.\d+\.\d+/m', $fileContent, $matches, \PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            throw new \RuntimeException('Could not find a release entry in the change log file. Please add a title like `## v1.0.0` to the file to be able to prepend something.');
        }

        $positionToInsert = $matches[0][1];
        $before = substr($fileContent, 0, $positionToInsert);
        $after = substr($fileContent, $positionToInsert);

        file_put_contents($changelogFilePath, $before.$content."\n".$after);
    }
}

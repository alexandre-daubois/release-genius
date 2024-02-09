<?php

namespace Changelog;

use ConventionalVersion\Changelog\Changelog;
use ConventionalVersion\Changelog\MarkdownChangelogDumper;
use ConventionalVersion\Changelog\WritingMode;
use ConventionalVersion\Git\Model\Commit;
use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\Model\Semver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkdownChangelogDumper::class)]
class MarkdownChangelogDumperTest extends TestCase
{
    public function testDump(): void
    {
        $changelog = $this->createSampleChangelog();
        $dumper = new MarkdownChangelogDumper();

        $expected = <<<MARKDOWN
## 2.0.0

 * fix(bug): Fix the bug (hash)
 * feat(feature): Add the feature (hash)
 * hash This is a raw commit

MARKDOWN;

        $this->assertSame($expected, $dumper->dump($changelog));
    }

    public function testDumpToFilePrepend(): void
    {
        $changelog = $this->createSampleChangelog();
        $dumper = new MarkdownChangelogDumper();

        $changelogFilePath = __DIR__.'/../sandbox/'.__METHOD__.'.md';
        file_put_contents($changelogFilePath, "Changelog\n=========\n\n## v1.2.3\n\n * Some test commit\n");

        $expected = <<<MARKDOWN
Changelog
=========

## 2.0.0

 * fix(bug): Fix the bug (hash)
 * feat(feature): Add the feature (hash)
 * hash This is a raw commit

## v1.2.3

 * Some test commit

MARKDOWN;

        $dumper->dumpToFile($changelog, $changelogFilePath);

        $this->assertFileExists($changelogFilePath);
        $this->assertSame($expected, file_get_contents($changelogFilePath));

        unlink($changelogFilePath);
    }

    public function testDumpToFilePrependOnInvalidExistingFile(): void
    {
        $changelog = $this->createSampleChangelog();
        $dumper = new MarkdownChangelogDumper();

        $changelogFilePath = __DIR__.'/../sandbox/'.__METHOD__.'.md';
        file_put_contents($changelogFilePath, 'Some invalid content');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find a release entry in the change log file. Please add a title like `## v1.0.0` to the file to be able to prepend something.');

        $dumper->dumpToFile($changelog, $changelogFilePath);
    }

    public function testDumpToFileAppend(): void
    {
        $changelog = $this->createSampleChangelog();
        $dumper = new MarkdownChangelogDumper();

        $changelogFilePath = __DIR__.'/../sandbox/'.__METHOD__.'.md';
        file_put_contents($changelogFilePath, "Changelog\n=========\n\n## v1.2.3\n\n * Some test commit\n");

        $expected = <<<MARKDOWN
Changelog
=========

## v1.2.3

 * Some test commit

## 2.0.0

 * fix(bug): Fix the bug (hash)
 * feat(feature): Add the feature (hash)
 * hash This is a raw commit

MARKDOWN;

        $dumper->dumpToFile($changelog, $changelogFilePath, WritingMode::Append);

        $this->assertFileExists($changelogFilePath);
        $this->assertSame($expected, file_get_contents($changelogFilePath));

        unlink($changelogFilePath);
    }

    public function testDumpToFileOverwrite(): void
    {
        $changelog = $this->createSampleChangelog();
        $dumper = new MarkdownChangelogDumper();

        $changelogFilePath = __DIR__.'/../sandbox/'.__METHOD__.'.md';
        file_put_contents($changelogFilePath, "Changelog\n=========\n\n## v1.2.3\n\n * Some test commit\n");

        $expected = <<<MARKDOWN
## 2.0.0

 * fix(bug): Fix the bug (hash)
 * feat(feature): Add the feature (hash)
 * hash This is a raw commit

MARKDOWN;

        $dumper->dumpToFile($changelog, $changelogFilePath, WritingMode::Overwrite);

        $this->assertFileExists($changelogFilePath);
        $this->assertSame($expected, file_get_contents($changelogFilePath));

        unlink($changelogFilePath);
    }

    private function createSampleChangelog(): Changelog
    {
        $changelog = new Changelog(Semver::fromString('1.0.0'), Semver::fromString('2.0.0'));
        $changelog->commits = [
            new Commit('hash', 'fix', 'bug', 'Fix the bug'),
            new Commit('hash', 'feat', 'feature', 'Add the feature'),
            new RawCommit('hash This is a raw commit'),
        ];

        return $changelog;
    }
}

<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Changelog;

interface FileChangelogDumperInterface
{
    public function dumpToFile(Changelog $changelog, string $changelogFilePath, WritingMode $writingMode): void;
}

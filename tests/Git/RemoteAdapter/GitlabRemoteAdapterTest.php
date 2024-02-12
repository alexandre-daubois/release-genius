<?php

namespace ConventionalVersion\Tests\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\RemoteAdapter\GitlabRemoteAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitlabRemoteAdapter::class)]
class GitlabRemoteAdapterTest extends TestCase
{
    #[DataProvider('provideValidRemotes')]
    public function testValidRemotes(string $remote): void
    {
        $adapter = new GitlabRemoteAdapter($remote);

        $this->assertSame('https://gitlab.com/owner/repo', $adapter->getRemoteUrl());
    }

    public static function provideValidRemotes(): \Generator
    {
        yield ['https://gitlab.com/owner/repo.git'];

        yield ['git@gitlab.com:owner/repo.git'];

        yield ['user@gitlab.com:owner/repo.git'];
    }

    public function testGetCommitUrl(): void
    {
        $adapter = new GitlabRemoteAdapter('https://git.company.com/owner/repo');

        $commit = new RawCommit('hash', 'message');
        $this->assertSame('https://git.company.com/owner/repo/-/commit/hash', $adapter->getCommitUrl($commit));
    }

    public function testCompareUrl(): void
    {
        $adapter = new GitlabRemoteAdapter('https://gitlab.com/owner/repo');

        $this->assertSame('https://gitlab.com/owner/repo/-/compare/base...head', $adapter->getCompareUrl('base', 'head'));
    }
}

<?php

namespace ConventionalVersion\Tests\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\RemoteAdapter\GithubRemoteAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GithubRemoteAdapter::class)]
class GithubRemoteAdapterTest extends TestCase
{
    #[DataProvider('provideValidRemotes')]
    public function testValidRemotes(string $remote): void
    {
        $adapter = new GithubRemoteAdapter($remote);

        $this->assertSame('https://github.com/owner/repo', $adapter->getRemoteUrl());
    }

    public static function provideValidRemotes(): \Generator
    {
        yield ['https://github.com/owner/repo.git'];

        yield ['git@github.com:owner/repo.git'];

        yield ['user@github.com:owner/repo.git'];
    }

    public function testHostedRemote(): void
    {
        $adapter = new GithubRemoteAdapter('git@github.company.com/owner/repo.git');

        $this->assertSame('https://github.company.com/owner/repo', $adapter->getRemoteUrl());
    }

    public function testGetCommitUrl(): void
    {
        $adapter = new GithubRemoteAdapter('https://github.com/owner/repo');

        $commit = new RawCommit('hash', 'message');
        $this->assertSame('https://github.com/owner/repo/commit/hash', $adapter->getCommitUrl($commit));
    }

    public function testCompareUrl(): void
    {
        $adapter = new GithubRemoteAdapter('https://github.com/owner/repo');

        $this->assertSame('https://github.com/owner/repo/compare/base...head', $adapter->getCompareUrl('base', 'head'));
    }
}

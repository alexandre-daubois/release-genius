<?php

namespace ConventionalVersion\Tests\Git;

use ConventionalVersion\Git\RemoteAdapter\EmptyRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapter\GithubRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapter\GitlabRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapterGuesser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoteAdapterGuesser::class)]
class RemoteAdapterGuesserTest extends TestCase
{
    public function testGuessGithub(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://github.com/owner/repo', null);

        $this->assertInstanceOf(GithubRemoteAdapter::class, $adapter);
    }

    public function testGuessGitlab(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://gitlab.com/owner/repo', null);

        $this->assertInstanceOf(GitlabRemoteAdapter::class, $adapter);
    }

    public function testGuessNothing(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://example.com/owner/repo', null);

        $this->assertInstanceOf(EmptyRemoteAdapter::class, $adapter);
    }

    public function testGuessExplicitGithub(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://example.com/owner/repo', 'github');

        $this->assertInstanceOf(GithubRemoteAdapter::class, $adapter);
    }

    public function testGuessExplicitGitlab(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://example.com/owner/repo', 'gitlab');

        $this->assertInstanceOf(GitlabRemoteAdapter::class, $adapter);
    }

    public function testGuessNoneExplicit(): void
    {
        $adapter = RemoteAdapterGuesser::guess('https://example.com/owner/repo', 'none');

        $this->assertInstanceOf(EmptyRemoteAdapter::class, $adapter);
    }
}

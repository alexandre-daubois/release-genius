<?php

namespace ConventionalVersion\Git;

use ConventionalVersion\Git\RemoteAdapter\EmptyRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapter\GithubRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapter\GitlabRemoteAdapter;
use ConventionalVersion\Git\RemoteAdapter\RemoteAdapterInterface;

class RemoteAdapterGuesser
{
    public static function guess(string $remote, ?string $explicitRemoteType): RemoteAdapterInterface
    {
        if (null !== $explicitRemoteType) {
            return match ($explicitRemoteType) {
                'github' => new GithubRemoteAdapter($remote),
                'gitlab' => new GitlabRemoteAdapter($remote),
                default => new EmptyRemoteAdapter(),
            };
        }

        if (preg_match('/github\.com\//', $remote)) {
            return new GithubRemoteAdapter($remote);
        }

        if (preg_match('/gitlab\.com\//', $remote)) {
            return new GitlabRemoteAdapter($remote);
        }

        return new EmptyRemoteAdapter();
    }
}

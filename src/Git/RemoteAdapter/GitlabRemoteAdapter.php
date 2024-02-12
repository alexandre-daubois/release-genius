<?php

namespace ConventionalVersion\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\CommitInterface;

class GitlabRemoteAdapter implements RemoteAdapterInterface
{
    private string $remote;

    public function __construct(string $rawRemote)
    {
        $rawRemote = strtr($rawRemote, [':' => '/']);

        if (str_starts_with($rawRemote, 'https///')) {
            $rawRemote = strtr($rawRemote, ['https///' => 'https://']);
        } else {
            $rawRemote = 'https://'.$rawRemote;
        }

        $rawRemote = strstr($rawRemote, '.git', true) ?: $rawRemote;

        $rawRemote = parse_url($rawRemote);
        if (!isset($rawRemote['host'], $rawRemote['path'])) {
            throw new \InvalidArgumentException('Invalid remote URL');
        }

        $this->remote = 'https://'.$rawRemote['host'].strtr($rawRemote['path'], [':' => '/']);
    }

    public function getRemoteUrl(): ?string
    {
        return $this->remote;
    }

    public function getCommitUrl(CommitInterface $commit): ?string
    {
        return $this->remote.'/-/commit/'.$commit->getHash();
    }

    public function getCompareUrl(string $base, string $head): ?string
    {
        return $this->remote.'/-/compare/'.$base.'...'.$head;
    }
}

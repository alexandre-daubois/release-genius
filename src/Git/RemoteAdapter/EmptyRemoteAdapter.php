<?php

namespace ConventionalVersion\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\CommitInterface;

class EmptyRemoteAdapter implements RemoteAdapterInterface
{
    public function getRemoteUrl(): ?string
    {
        return null;
    }

    public function getCommitUrl(CommitInterface $commit): ?string
    {
        return null;
    }

    public function getCompareUrl(string $base, string $head): ?string
    {
        return null;
    }
}

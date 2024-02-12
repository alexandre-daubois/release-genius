<?php

namespace ConventionalVersion\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\CommitInterface;

interface RemoteAdapterInterface
{
    /**
     * Returns the URL of the remote repository.
     */
    public function getRemoteUrl(): ?string;

    /**
     * Returns the URL of the commit with the given hash.
     */
    public function getCommitUrl(CommitInterface $commit): ?string;

    /**
     * Returns the URL to compare the given base and head commits.
     */
    public function getCompareUrl(string $base, string $head): ?string;
}

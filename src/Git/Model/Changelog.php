<?php

namespace ConventionalVersion\Git\Model;

class Changelog
{
    /**
     * @param array<CommitInterface> $commits
     */
    public function __construct(public array $commits = [])
    {
    }
}

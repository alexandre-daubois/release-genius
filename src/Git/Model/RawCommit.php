<?php

namespace ConventionalVersion\Git\Model;

class RawCommit implements CommitInterface
{
    public function __construct(public string $rawCommit)
    {
    }

    public function __toString(): string
    {
        return $this->rawCommit;
    }

    public static function fromString(string $commit): CommitInterface
    {
        return new self($commit);
    }
}

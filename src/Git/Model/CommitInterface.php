<?php

namespace ConventionalVersion\Git\Model;

interface CommitInterface
{
    public function __toString(): string;

    public static function fromString(string $commit): self;

    public function getHash(): string;
}

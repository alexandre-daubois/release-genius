<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Git\Model;

/**
 * A commit that has not, or could not, been parsed into its parts.
 */
class RawCommit implements CommitInterface
{
    public function __construct(
        public string $hash,
        public string $message
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }

    public static function fromString(string $commit): CommitInterface
    {
        $commitSha = substr($commit, 0, 10);
        $commit = substr($commit, 11);

        return new self($commitSha, $commit);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

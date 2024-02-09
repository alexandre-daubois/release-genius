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

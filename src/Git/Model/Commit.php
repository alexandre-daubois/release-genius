<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Git\Model;

/**
 * Represents a commit message in a git repository.
 */
class Commit implements CommitInterface
{
    public function __construct(
        public string $hash,
        public string $type,
        public string $scope,
        public string $message,
        public bool $breakingChange = false,
    ) {
    }

    /**
     * Dumps the commit to a string.
     */
    public function __toString(): string
    {
        return sprintf('%s(%s)%s: %s', $this->type, $this->scope, $this->breakingChange ? '!' : '', $this->message);
    }

    /**
     * Create a Commit from a string. The string should be in the format of:
     *    <hash> <type>(<scope>): <description>
     *
     * An exclamation mark (!) after the type/scope is used to indicate a breaking change.
     * Example of valid strings:
     *
     *  5786cdac88 feat(scope): message
     *  5786cdac88 fix: message
     *  5786cdac88 revert(scope): message
     */
    public static function fromString(string $commit): self
    {
        $commitSha = substr($commit, 0, 10);
        $commit = substr($commit, 11);

        preg_match('/(?<type>\w+)(\((?<scope>.+?)\))?(?<breaking>!)?(:)?\s(?<description>[^\r\n]+)/', $commit, $matches);

        if (empty($matches)) {
            throw new \InvalidArgumentException('Invalid commit message');
        }

        return new self($commitSha, $matches['type'], $matches['scope'], $matches['description'], '!' === $matches['breaking']);
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

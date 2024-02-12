<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Changelog;

use ConventionalVersion\Git\Model\Commit;
use ConventionalVersion\Git\Model\CommitInterface;
use ConventionalVersion\Git\Model\Semver;

class Changelog
{
    /**
     * @param array<CommitInterface> $commits
     */
    public function __construct(
        public Semver $fromVersion,
        public Semver $toVersion,
        public array $commits = []
    ) {
    }

    /**
     * @return array{
     *     features: array<int, CommitInterface>,
     *     fixes: array<int, CommitInterface>,
     *     misc: array<int, CommitInterface>,
     *     others: array<int, CommitInterface>
     * }
     */
    public function sortedCommits(): array
    {
        /** @var array<Commit> $sortableCommits */
        $sortableCommits = array_filter($this->commits, fn (CommitInterface $commit) => $commit instanceof Commit);

        return [
            'features' => array_filter($sortableCommits, fn (Commit $commit) => 'feat' === $commit->type),
            'fixes' => array_filter($sortableCommits, fn (Commit $commit) => 'fix' === $commit->type),
            'misc' => array_filter($sortableCommits, fn (Commit $commit) => !\in_array($commit->type, ['feat', 'fix'])),
            'others' => array_diff($this->commits, $sortableCommits),
        ];
    }
}

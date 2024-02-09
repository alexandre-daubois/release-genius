<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Changelog;

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
}

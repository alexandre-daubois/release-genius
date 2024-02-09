<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

class Semver
{
    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public bool $usesPrefix = false,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('v%d.%d.%d', $this->major, $this->minor, $this->patch);
    }

    public function next(ReleaseType $releaseType): self
    {
        return match ($releaseType) {
            ReleaseType::Major => new self($this->major + 1, 0, 0, $this->usesPrefix),
            ReleaseType::Minor => new self($this->major, $this->minor + 1, 0, $this->usesPrefix),
            ReleaseType::Patch => new self($this->major, $this->minor, $this->patch + 1, $this->usesPrefix),
        };
    }
}

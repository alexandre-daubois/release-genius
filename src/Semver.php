<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

/**
 * This class represents a semantic version.
 */
class Semver
{
    /**
     * @param int $major The major version number.
     * @param int $minor The minor version number.
     * @param int $patch The patch version number.
     * @param bool $usesPrefix Whether the version uses a "v" prefix.
     */
    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public bool $usesPrefix = false,
    ) {
    }

    /**
     * @return string The string representation of the version, in the
     *              "vX.Y.Z" format if the version uses a prefix, or "X.Y.Z"
     */
    public function __toString(): string
    {
        return sprintf('%s%d.%d.%d', $this->usesPrefix ? 'v' : '', $this->major, $this->minor, $this->patch);
    }

    /**
     * Returns a new instance of Semver with the next version. The release type
     * will determine which part of the version will be incremented.
     */
    public function next(ReleaseType $releaseType): static
    {
        return match ($releaseType) {
            ReleaseType::Major => new self($this->major + 1, 0, 0, $this->usesPrefix),
            ReleaseType::Minor => new self($this->major, $this->minor + 1, 0, $this->usesPrefix),
            ReleaseType::Patch => new self($this->major, $this->minor, $this->patch + 1, $this->usesPrefix),
        };
    }
}

<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Git\Model;

use ConventionalVersion\ReleaseType;

/**
 * This class represents a semantic version.
 */
class Semver
{
    /**
     * @param int  $major      the major version number
     * @param int  $minor      the minor version number
     * @param int  $patch      the patch version number
     * @param bool $usesPrefix whether the version uses a "v" prefix
     */
    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public bool $usesPrefix = false,
    ) {
    }

    /**
     * Creates a new instance of Semver from a string. The string must be in the
     * "vX.Y.Z" format if the version uses a prefix, or "X.Y.Z" otherwise.
     */
    public static function fromString(string $version): self
    {
        $matches = [];
        if (!preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $version, $matches)) {
            throw new \InvalidArgumentException(sprintf('The version "%s" is not a valid Semver version', $version));
        }

        return new self((int) $matches[1], (int) $matches[2], (int) $matches[3], $version[0] === 'v');
    }

    /**
     * @return string The string representation of the version, in the
     *                "vX.Y.Z" format if the version uses a prefix, or "X.Y.Z"
     */
    public function __toString(): string
    {
        return sprintf('%s%d.%d.%d', $this->usesPrefix ? 'v' : '', $this->major, $this->minor, $this->patch);
    }

    /**
     * Returns a new instance of Semver with the next version. The release type
     * will determine which part of the version will be incremented.
     */
    public function next(ReleaseType $releaseType): self
    {
        return match ($releaseType) {
            ReleaseType::Major => new self($this->major + 1, 0, 0, $this->usesPrefix),
            ReleaseType::Minor => new self($this->major, $this->minor + 1, 0, $this->usesPrefix),
            ReleaseType::Patch => new self($this->major, $this->minor, $this->patch + 1, $this->usesPrefix),
        };
    }
}

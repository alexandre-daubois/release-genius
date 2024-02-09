<?php

namespace ConventionalVersion\Tests;

use ConventionalVersion\ReleaseType;
use ConventionalVersion\Semver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Semver::class)]
class SemverTest extends TestCase
{
    public function testCreateSemver(): void
    {
        $semver = new Semver(1, 2, 3);

        $this->assertSame(1, $semver->major);
        $this->assertSame(2, $semver->minor);
        $this->assertSame(3, $semver->patch);
    }

    public function testNextMajor(): void
    {
        $semver = new Semver(1, 2, 3);
        $next = $semver->next(ReleaseType::Major);

        $this->assertSame(2, $next->major);
        $this->assertSame(0, $next->minor);
        $this->assertSame(0, $next->patch);
    }

    public function testNextMinor(): void
    {
        $semver = new Semver(1, 2, 3);
        $next = $semver->next(ReleaseType::Minor);

        $this->assertSame(1, $next->major);
        $this->assertSame(3, $next->minor);
        $this->assertSame(0, $next->patch);
    }

    public function testNextPatch(): void
    {
        $semver = new Semver(1, 2, 3);
        $next = $semver->next(ReleaseType::Patch);

        $this->assertSame(1, $next->major);
        $this->assertSame(2, $next->minor);
        $this->assertSame(4, $next->patch);
    }

    public function testSemverToString(): void
    {
        $semver = new Semver(1, 2, 3);

        $this->assertSame('1.2.3', (string) $semver);
    }

    public function testSemverToStringWithPrefix(): void
    {
        $semver = new Semver(1, 2, 3, true);

        $this->assertSame('v1.2.3', (string) $semver);
    }
}

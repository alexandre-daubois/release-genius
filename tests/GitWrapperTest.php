<?php

namespace ConventionalVersion\Tests;

use ConventionalVersion\CommandRunnerInterface;
use ConventionalVersion\GitWrapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitWrapper::class)]
class GitWrapperTest extends TestCase
{
    public function testGetLatestTag(): void
    {
        $runner = $this->createMock(CommandRunnerInterface::class);
        $runner->method('run')->willReturn('v1.2.3');

        $git = new GitWrapper($runner);
        $git->setExecutable('git');

        $semver = $git->getLatestTag();

        $this->assertSame(1, $semver->major);
        $this->assertSame(2, $semver->minor);
        $this->assertSame(3, $semver->patch);
    }

    public function testGetLatestTagWithoutPrefix(): void
    {
        $runner = $this->createMock(CommandRunnerInterface::class);
        $runner->method('run')->willReturn('1.2.3');

        $git = new GitWrapper($runner);
        $git->setExecutable('git');

        $semver = $git->getLatestTag();

        $this->assertSame(1, $semver->major);
        $this->assertSame(2, $semver->minor);
        $this->assertSame(3, $semver->patch);
    }

    public function testGetLatestTagWithUnknownPrefixThrows(): void
    {
        $runner = $this->createMock(CommandRunnerInterface::class);
        $runner->method('run')->willReturn('foo1.2.3');

        $git = new GitWrapper($runner);
        $git->setExecutable('git');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last tag does not follow the SemVer format.');

        $git->getLatestTag();
    }

    public function testGetLatestTagNoTagsThrows(): void
    {
        $runner = $this->createMock(CommandRunnerInterface::class);
        $runner->method('run')->willReturn('');

        $git = new GitWrapper($runner);
        $git->setExecutable('git');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last tag does not follow the SemVer format.');

        $git->getLatestTag();
    }

    public function testGetLatestTagNoPatchThrows(): void
    {
        $runner = $this->createMock(CommandRunnerInterface::class);
        $runner->method('run')->willReturn('v1.2');

        $git = new GitWrapper($runner);
        $git->setExecutable('git');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last tag does not follow the SemVer format.');

        $git->getLatestTag();
    }
}

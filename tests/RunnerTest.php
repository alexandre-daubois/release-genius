<?php

namespace ConventionalVersion\Tests;

use ConventionalVersion\Runner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(Runner::class)]
class RunnerTest extends TestCase
{
    public function testInvalidReleaseTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected release type to be provided and one of "major", "minor", or "patch"');

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturn('invalid');

        $output = $this->createMock(OutputInterface::class);

        $this->assertSame(Command::FAILURE, Runner::run($input, $output));
    }
}

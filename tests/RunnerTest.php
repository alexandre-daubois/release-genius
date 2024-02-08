<?php

namespace ConventionalVersion\Tests;

use ConventionalVersion\Runner;
use InvalidArgumentException;
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of "major", "minor", or "patch", but got "invalid".');

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturn('invalid');

        $output = $this->createMock(OutputInterface::class);

        $this->assertSame(Command::FAILURE, Runner::run($input, $output));
    }
}

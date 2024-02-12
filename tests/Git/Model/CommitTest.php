<?php

namespace ConventionalVersion\Tests\Git\Model;

use ConventionalVersion\Git\Model\Commit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Commit::class)]
class CommitTest extends TestCase
{
    public function testCreateCommit(): void
    {
        $commit = new Commit('hash', 'type', 'scope', 'message');

        $this->assertSame('hash', $commit->hash);
        $this->assertSame('type', $commit->type);
        $this->assertSame('scope', $commit->scope);
        $this->assertSame('message', $commit->message);
    }

    public function testCommitToString(): void
    {
        $commit = new Commit('hash', 'type', 'scope', 'message');

        $this->assertSame('type(scope): message', (string) $commit);
    }

    #[DataProvider('provideCommitStrings')]
    public function testCommitFromString(string $commitString, string $type, string $scope, string $message, bool $breaking): void
    {
        $commit = Commit::fromString($commitString);

        $this->assertSame('5786cdac88', $commit->hash);
        $this->assertSame($type, $commit->type);
        $this->assertSame($scope, $commit->scope);
        $this->assertSame($message, $commit->message);
        $this->assertSame($breaking, $commit->breakingChange);
    }

    #[DataProvider('provideInvalidCommitStrings')]
    public function testInvalidCommitFromString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid commit message');

        Commit::fromString('5786cdac88 (scope): message');
    }

    /**
     * @return array<array{string, string, string, string, bool}>
     */
    public static function provideCommitStrings(): array
    {
        return [
            'full commit' => ['5786cdac88 feat(scope): message', 'feat', 'scope', 'message', false],
            'no scope' => ['5786cdac88 fix: message', 'fix', '', 'message', false],
            'no message' => ['5786cdac88 revert(scope): message', 'revert', 'scope', 'message', false],
            'no scope or message' => ['5786cdac88 chore: message', 'chore', '', 'message', false],
            'no scope or message with space' => ['5786cdac88 ci: message', 'ci', '', 'message', false],
            'no colon and no message' => ['5786cdac88 test(scope): message', 'test', 'scope', 'message', false],
            'breaking change' => ['5786cdac88 feat(scope)!: message', 'feat', 'scope', 'message', true],
        ];
    }

    /**
     * @return array<array{string}>
     */
    public static function provideInvalidCommitStrings(): array
    {
        return [
            'no type' => ['5786cdac88 (scope): message'],
            'no message' => ['5786cdac88 feat(scope):'],
            'no type or message' => ['5786cdac88 (scope):'],
            'no type or scope' => ['5786cdac88 : message'],
            'no type or scope or message' => ['5786cdac88 :'],
            'no semi colon' => ['5786cdac88 feat(scope) message'],
        ];
    }
}

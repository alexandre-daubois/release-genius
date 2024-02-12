<?php

namespace ConventionalVersion\Tests\Git\RemoteAdapter;

use ConventionalVersion\Git\Model\RawCommit;
use ConventionalVersion\Git\RemoteAdapter\EmptyRemoteAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmptyRemoteAdapter::class)]
class EmptyRemoteAdapterTest extends TestCase
{
    public function testGetRemoteUrl(): void
    {
        $adapter = new EmptyRemoteAdapter();
        $this->assertNull($adapter->getRemoteUrl());
    }

    public function testGetCommitUrl(): void
    {
        $adapter = new EmptyRemoteAdapter();
        $this->assertNull($adapter->getCommitUrl(new RawCommit('hash', 'message')));
    }

    public function testCompareUrl(): void
    {
        $adapter = new EmptyRemoteAdapter();
        $this->assertNull($adapter->getCompareUrl('base', 'head'));
    }
}

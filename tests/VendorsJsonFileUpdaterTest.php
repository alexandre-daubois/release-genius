<?php

namespace ConventionalVersion\Tests;

use ConventionalVersion\Exception\VendorFileNotFoundException;
use ConventionalVersion\Git\Model\Semver;
use ConventionalVersion\VendorsJsonFileUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VendorsJsonFileUpdater::class)]
class VendorsJsonFileUpdaterTest extends TestCase
{
    public function testUpdateFileNotFound(): void
    {
        $this->expectException(VendorFileNotFoundException::class);

        VendorsJsonFileUpdater::update(new Semver(1, 0, 0), 'invalid');
    }

    public function testUpdateVendorsJsonFile(): void
    {
        $filename = __DIR__.'/sandbox/package.json';
        $semver = new Semver(3, 145, 6);

        file_put_contents($filename, <<<JSON
{
    "name": "app",
    "version": "3.143.5",
    "description": "My app",
    "private": true,
    "main": "src/AppBundle/Resources/public/index.js"
}
JSON);

        VendorsJsonFileUpdater::update($semver, $filename);

        $content = file_get_contents($filename);
        if (false === $content) {
            $this->fail('The file could not be read');
        }

        $this->assertStringContainsString('"version": "3.145.6"', $content);

        unlink($filename);
    }
}

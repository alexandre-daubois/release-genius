<?php

namespace ConventionalVersion;

use ConventionalVersion\Exception\VendorFileNotFoundException;
use ConventionalVersion\Git\Model\Semver;

class VendorsJsonFileUpdater
{
    public static function update(Semver $semver, string $filename): void
    {
        $packageJson = file_get_contents($filename);
        if (false === $packageJson) {
            throw new VendorFileNotFoundException(sprintf('"%s" not found', $filename));
        }

        $packageJson = preg_replace('/"version": "[^"]+"/', '"version": "'.$semver.'"', $packageJson);
        file_put_contents($filename, $packageJson);
    }
}

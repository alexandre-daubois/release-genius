<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion;

/**
 * The application will accept one of these three release types as an argument.
 */
enum ReleaseType: string
{
    case Major = 'major';
    case Minor = 'minor';
    case Patch = 'patch';
}

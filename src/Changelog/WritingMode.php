<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConventionalVersion\Changelog;

enum WritingMode: string
{
    case Append = 'append';
    case Prepend = 'prepend';
    case Overwrite = 'overwrite';
}

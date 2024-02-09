<?php

namespace ConventionalVersion;

enum ReleaseType: string
{
    case Major = 'major';
    case Minor = 'minor';
    case Patch = 'patch';
}

<?php

namespace ConventionalVersion;

interface CommandRunnerInterface
{
    public function run(string $cmd): string;
}

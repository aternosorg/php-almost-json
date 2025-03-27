<?php

namespace Tests;

use Aternos\PhpAlmostJson\AlmostJsonParser;
use PHPUnit\Framework\TestCase;

class AlmostJsonTestCase extends TestCase
{
    protected AlmostJsonParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new AlmostJsonParser();
    }
}

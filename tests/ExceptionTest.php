<?php

namespace Tests;

use Aternos\PhpAlmostJson\Exception\AlmostJsonException;

class ExceptionTest extends AlmostJsonTestCase
{
    public function testException(): void
    {
        $exception = new AlmostJsonException("Test", 5);
        $this->assertEquals("Test at offset 5", $exception->getMessage());
        $this->assertEquals(5, $exception->getOffset());
    }
}

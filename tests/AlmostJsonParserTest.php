<?php

namespace Tests;

use Aternos\PhpAlmostJson\AlmostJsonParser;
use Aternos\PhpAlmostJson\Exception\MaxDepthException;
use Aternos\PhpAlmostJson\Input;

class AlmostJsonParserTest extends AlmostJsonTestCase
{
    public function testParseJson(): void
    {
        $input = new Input('{"key": "value"}');
        $parser = new AlmostJsonParser();
        $parsed = $parser->parse($input, true);
        $this->assertEquals(["key" => "value"], $parsed);
    }

    public function testMaxDepth(): void
    {
        $input = new Input('{"key": {"key": {"key": {"key": {"key": {"key": {"key": {"key": {"key": {"key": "value"}}}}}}}}}}');
        $parser = new AlmostJsonParser();
        $parser->setMaxDepth(5);
        $this->assertEquals(5, $parser->getMaxDepth());
        $this->expectException(MaxDepthException::class);
        $parser->parse($input, true);
    }
}

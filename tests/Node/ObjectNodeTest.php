<?php

namespace Tests\Node;

use Aternos\AlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\AlmostJson\Exception\UnexpectedInputException;
use Aternos\AlmostJson\Input;
use Aternos\AlmostJson\Node\ObjectNode;
use stdClass;

class ObjectNodeTest extends NodeTestCase
{
    public function testParseObject(): void
    {
        $input = new Input('{"key": "value"}');
        $node = new ObjectNode();
        $node->read($input, $this->parser);
        $this->assertCount(1, $node->getChildren());
        $this->assertEquals(["key" => "value"], $node->toNative(true));

        $obj = $node->toNative();
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertEquals("value", $obj->key);
    }

    public function testTrailingComma(): void
    {
        $input = new Input('{"key": "value",}');
        $node = new ObjectNode();
        $node->read($input, $this->parser);
        $this->assertEquals(["key" => "value"], $node->toNative(true));
    }

    public function testUnquotedKey(): void
    {
        $input = new Input('{key: "value"}');
        $node = new ObjectNode();
        $node->read($input, $this->parser);
        $this->assertEquals(["key" => "value"], $node->toNative(true));
    }

    public function testMissingOpen(): void
    {
        $input = new Input('"key": "value"}');
        $node = new ObjectNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testEmpty(): void
    {
        $input = new Input('{}');
        $node = new ObjectNode();
        $node->read($input, $this->parser);
        $this->assertCount(0, $node->getChildren());
    }

    public function testMissingColon(): void
    {
        $input = new Input('{"key" "value"}');
        $node = new ObjectNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testMissingValue(): void
    {
        $input = new Input('{"key":}');
        $node = new ObjectNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testImplicitClose(): void
    {
        $input = new Input('{"key": "value"');
        $node = new ObjectNode();
        $node->read($input, $this->parser);
        $this->assertEquals(["key" => "value"], $node->toNative(true));
    }

    public function testDetect(): void
    {
        $input = new Input('{"key": "value"}');
        $this->assertTrue(ObjectNode::detect($input, $this->parser));

        $input = new Input("[1, 2, 3]");
        $this->assertFalse(ObjectNode::detect($input, $this->parser));
    }
}

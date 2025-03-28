<?php

namespace Tests\Node;

use Aternos\PhpAlmostJson\AlmostJsonParser;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\Node\NumberNode;

class NumberNodeTest extends NodeTestCase
{
    public function testParseNumber(): void
    {
        $input = new Input("123.45");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(123.45, $node->getValue());
        $this->assertEquals(123.45, $node->toNative());

        $input = new Input("123_456");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(123456, $node->getValue());
    }

    public function testHex(): void
    {
        $input = new Input("0x1A");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(26, $node->getValue());
        $this->assertEquals(26, $node->toNative());

        $input = new Input("0x1_a");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(26, $node->getValue());

        $this->expectException(UnexpectedInputException::class);
        $input = new Input("0xG");
        $node = new NumberNode();
        $node->read($input, $this->parser);
    }

    public function testBinary(): void
    {
        $input = new Input("0b1101");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(13, $node->getValue());
        $this->assertEquals(13, $node->toNative());

        $input = new Input("0b11_01");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(13, $node->getValue());

        $this->expectException(UnexpectedInputException::class);
        $input = new Input("0b2");
        $node = new NumberNode();
        $node->read($input, $this->parser);
    }

    public function testOctal(): void
    {
        $input = new Input("0o755");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(493, $node->getValue());
        $this->assertEquals(493, $node->toNative());

        $input = new Input("0o7_55");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(493, $node->getValue());

        $this->expectException(UnexpectedInputException::class);
        $input = new Input("0o8");
        $node = new NumberNode();
        $node->read($input, $this->parser);
    }

    public function testOctalZeroPrefix(): void
    {
        $parser = (new AlmostJsonParser())->setZeroPrefixOctal(true);
        $input = new Input("0755");
        $node = new NumberNode();
        $node->read($input, $parser);
        $this->assertEquals(493, $node->getValue());
        $this->assertEquals(493, $node->toNative());

        $input = new Input("07_55");
        $node = new NumberNode();
        $node->read($input, $parser);
        $this->assertEquals(493, $node->getValue());
    }

    public function testParseZeroInOctalZeroPrefixMode(): void
    {
        $parser = (new AlmostJsonParser())->setZeroPrefixOctal(true);
        $input = new Input("0");
        $node = new NumberNode();
        $node->read($input, $parser);
        $this->assertEquals(0, $node->getValue());
        $this->assertEquals(0, $node->toNative());
    }

    public function testInfinity(): void
    {
        $input = new Input("Infinity");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(INF, $node->getValue());
        $this->assertEquals(INF, $node->toNative());

        $input = new Input("-Infinity");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(-INF, $node->getValue());
        $this->assertEquals(-INF, $node->toNative());
    }

    public function testNaN(): void
    {
        $input = new Input("NaN");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertNan($node->getValue());
        $this->assertNan($node->toNative());

        $input = new Input("-NaN");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertNan($node->getValue());
        $this->assertNan($node->toNative());
    }

    public function testInt(): void
    {
        $input = new Input("123");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertIsInt($node->getValue());
        $this->assertEquals(123, $node->getValue());
        $this->assertEquals(123, $node->toNative());
    }

    public function testFloat(): void
    {
        $input = new Input("123.456");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertIsFloat($node->getValue());
        $this->assertEquals(123.456, $node->getValue());
        $this->assertEquals(123.456, $node->toNative());
    }

    public function testExponent(): void
    {
        $input = new Input("1.23e4");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(12300, $node->getValue());
        $this->assertEquals(12300, $node->toNative());

        $input = new Input("1.23E-4");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(0.000123, $node->getValue());
        $this->assertEquals(0.000123, $node->toNative());
    }

    public function testExponentWithoutDecimal(): void
    {
        $input = new Input("123e4");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertIsFloat($node->getValue());
        $this->assertEquals(1230000, $node->getValue());
        $this->assertEquals(1230000, $node->toNative());

        $input = new Input("123E-4");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(0.0123, $node->getValue());
        $this->assertEquals(0.0123, $node->toNative());
    }

    public function testOnlyDecimal(): void
    {
        $input = new Input(".123");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(0.123, $node->getValue());
        $this->assertEquals(0.123, $node->toNative());

        $input = new Input(".123e4");
        $node = new NumberNode();
        $node->read($input, $this->parser);
        $this->assertEquals(1230, $node->getValue());
        $this->assertEquals(1230, $node->toNative());
    }

    public function testTrailingPoint(): void
    {
        $input = new Input("123.");
        $node = new NumberNode();
        $node->read($input, $this->parser);

        $this->assertEquals(123.0, $node->getValue());
    }

    public function testDetect(): void
    {
        $input = new Input("123");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("123.456");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input(".123");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("0x1A");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("0b1101");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("0o755");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("Infinity");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("-Infinity");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("NaN");
        $this->assertTrue(NumberNode::detect($input, $this->parser));

        $input = new Input("notanumber");
        $this->assertFalse(NumberNode::detect($input, $this->parser));
    }
}

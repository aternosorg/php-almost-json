<?php

namespace Tests\Node;

use Aternos\PhpAlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\Node\StringNode;
use PHPUnit\Framework\Attributes\TestWith;

class StringNodeTest extends NodeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->parser->setTopLevelUnquotedStringAllowed(true);
    }

    public function testParseString(): void
    {
        $input = new Input('"Hello, World!"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello, World!", $node->getValue());
        $this->assertEquals("Hello, World!", $node->toNative());
    }

    public function testSingleQuotes(): void
    {
        $input = new Input("'Hello, World!'");
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello, World!", $node->getValue());
        $this->assertEquals("Hello, World!", $node->toNative());
    }

    public function testBackTicks(): void
    {
        $input = new Input("`Hello, World!`");
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello, World!", $node->getValue());
        $this->assertEquals("Hello, World!", $node->toNative());
    }

    public function testEscapedQuotes(): void
    {
        $input = new Input('"Hello, \"World!"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello, \"World!", $node->getValue());

        $input = new Input("'Hello, \'World!'");
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello, 'World!", $node->getValue());
    }

    public function testInconsistentQuotes(): void
    {
        $input = new Input('"Hello, World!\'');
        $node = new StringNode();
        $this->expectException(UnexpectedEndOfInputException::class);
        $node->read($input, $this->parser);
    }

    public function testUnquoted(): void
    {
        $input = new Input("Hello-World!");
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello-World!", $node->getValue());
    }

    #[TestWith(["{"])]
    #[TestWith(["}"])]
    #[TestWith(["["])]
    #[TestWith(["]"])]
    #[TestWith([":"])]
    #[TestWith([","])]
    #[TestWith([" "])]
    #[TestWith(["\t"])]
    #[TestWith(["\n"])]
    #[TestWith(["\r"])]
    public function testUnquotedEnd(string $char): void
    {
        $input = new Input("Hello" . $char . "World!");
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("Hello", $node->getValue());
        $this->assertEquals(5, $input->tell());
    }

    #[TestWith(["b", "\x08"])]
    #[TestWith(["f", "\x0C"])]
    #[TestWith(["n", "\n"])]
    #[TestWith(["r", "\r"])]
    #[TestWith(["t", "\t"])]
    #[TestWith(["v", "\v"])]
    #[TestWith(["0", "\0"])]
    public function testSpecialEscape(string $char, string $expected): void
    {
        $input = new Input('"\\' . $char . '"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals($expected, $node->getValue());
    }

    public function testAsciiEscape(): void
    {
        $input = new Input('"\\x41"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("A", $node->getValue());
    }

    public function testIncompleteAsciiEscape(): void
    {
        $input = new Input('"\\x4 test"');
        $node = new StringNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testUnicodeEscape(): void
    {
        $input = new Input('"\\u0041"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("A", $node->getValue());
    }

    public function testIncompleteUnicodeEscape(): void
    {
        $input = new Input('"\\u004 test"');
        $node = new StringNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testBackslashEscape(): void
    {
        $input = new Input('"\\\\"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("\\", $node->getValue());
    }

    public function testIgnoreUnnecessaryEscape(): void
    {
        $input = new Input('"\a\c\d"');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("acd", $node->getValue());
    }

    public function testEmptyUnquotedString(): void
    {
        $input = new Input("");
        $node = new StringNode();
        $this->expectException(UnexpectedEndOfInputException::class);
        $node->read($input, $this->parser);
    }

    public function testEmptyUnquotedStringFollowedByOtherData(): void
    {
        $input = new Input(", 1");
        $node = new StringNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testEmptyQuotedString(): void
    {
        $input = new Input('""');
        $node = new StringNode();
        $node->read($input, $this->parser);
        $this->assertEquals("", $node->getValue());
        $this->assertEquals("", $node->toNative());
    }

    public function testMissingClosingQuote(): void
    {
        $input = new Input('"Hello, World!');
        $node = new StringNode();
        $this->expectException(UnexpectedEndOfInputException::class);
        $node->read($input, $this->parser);
    }

    public function testReadCharAtEndOfInput(): void
    {
        $input = new Input('');
        $node = new StringNode();
        $reflection = new \ReflectionClass($node);
        $method = $reflection->getMethod('readChar');
        $this->expectException(UnexpectedEndOfInputException::class);
        $method->invoke($node, $input);
    }

    public function testDisallowUnquotedStringAtTopLevel(): void
    {
        $this->parser->setTopLevelUnquotedStringAllowed(false);
        $input = new Input("Hello, World!");
        $node = new StringNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }
}

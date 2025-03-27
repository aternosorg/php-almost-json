<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\AlmostJsonParser;

class StringNode extends AlmostJsonNode
{
    protected const QUOTE = ["'", '"'];
    protected const UNQUOTED_DISALLOWED = ["{", "}", "[", "]", ":", ",", " ", "\t", "\n", "\r"];
    protected const ESCAPE = "\\";
    protected const ESCAPE_UNICODE = "u";
    protected const ESCAPE_ASCII = "x";

    protected const SPECIAL_ESCAPES = [
        "b" => "\x08",
        "f" => "\x0c",
        "n" => "\n",
        "r" => "\r",
        "t" => "\t"
    ];

    protected string $value;

    /**
     * @inheritDoc
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool
    {
        $next = $input->peek();
        return in_array($next, static::QUOTE, true) || !in_array($next, static::UNQUOTED_DISALLOWED, true);
    }

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @inheritDoc
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void
    {
        if (!$input->check(...static::QUOTE)) {
            $this->readUnquoted($input);
            return;
        }

        $quote = $input->read();
        $result = "";
        while ($input->valid() && !$input->check($quote)) {
            $result .= $this->readChar($input);
        }

        $input->assert($quote);
        $input->read();
        $this->value = $result;
    }

    /**
     * @param Input $input
     * @return void
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    protected function readUnquoted(Input $input): void
    {
        if ($input->check(...static::UNQUOTED_DISALLOWED)) {
            throw new UnexpectedInputException("Expected unquoted string" .
                ", got " . $input->peek(16), $input->tell());
        }

        $result = "";
        while ($input->valid() && !$input->check(...static::UNQUOTED_DISALLOWED)) {
            $result .= $this->readChar($input);
        }

        $this->value = $result;
    }

    /**
     * @param Input $input
     * @return string
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    protected function readChar(Input $input): string
    {
        if (!$input->valid()) {
            throw new UnexpectedEndOfInputException("Expected character, got end of input", $input->tell());
        }

        if (!$input->check(static::ESCAPE)) {
            return $input->read();
        }

        $input->read();
        $input->assertValid();
        if (!$input->checkInsensitive(static::ESCAPE_UNICODE, static::ESCAPE_ASCII, ...array_keys(static::SPECIAL_ESCAPES))) {
            return $input->read();
        }

        $escapeType = $input->read();
        if (array_key_exists($escapeType, static::SPECIAL_ESCAPES)) {
            return static::SPECIAL_ESCAPES[$escapeType];
        }

        $length = 2;
        if ($escapeType === static::ESCAPE_UNICODE) {
            $length = 4;
        }

        $hex = "";
        for ($i = 0; $i < $length; $i++) {
            $input->assertValid();
            $input->assert(...static::HEX);
            $hex .= $input->read();
        }

        return mb_chr(hexdec($hex), $input->getEncoding());
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): string
    {
        return $this->value;
    }
}

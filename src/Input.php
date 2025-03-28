<?php

namespace Aternos\PhpAlmostJson;

use Aternos\PhpAlmostJson\Comment\BlockComment;
use Aternos\PhpAlmostJson\Comment\LineComment;
use Aternos\PhpAlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;

class Input
{
    protected const WHITESPACE = [" ", "\t", "\n", "\r"];

    protected int $offset = 0;

    /**
     * @var string[]
     */
    protected array $input;

    /**
     * @param string $input
     * @param string|null $encoding
     */
    public function __construct(
        string $input,
        protected ?string $encoding = "UTF-8"
    )
    {
        $this->input = mb_str_split($input, encoding: $encoding);
    }

    /**
     * @param int $length
     * @return string
     */
    public function read(int $length = 1): string
    {
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            if (!$this->valid()) {
                break;
            }
            $result .= $this->input[$this->offset++];
        }

        return $result;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function skip(int $length = 1): static
    {
        for ($i = 0; $i < $length; $i++) {
            if (!$this->valid()) {
                break;
            }
            $this->offset++;
        }
        return $this;
    }

    /**
     * @param string ...$expected
     * @return string
     */
    public function readAll(string ...$expected): string
    {
        $result = "";
        do {
            $found = false;
            foreach ($expected as $value) {
                if ($this->check($value)) {
                    $result .= $this->read(mb_strlen($value));
                    $found = true;
                    break;
                }
            }
        } while ($found);
        return $result;
    }

    /**
     * @param string ...$expected
     * @return $this
     */
    public function skipAll(string ...$expected): static
    {
        do {
            $found = false;
            foreach ($expected as $value) {
                if ($this->check($value)) {
                    $this->skip(mb_strlen($value));
                    $found = true;
                    break;
                }
            }
        } while ($found);
        return $this;
    }

    /**
     * @param int $length
     * @return string
     */
    public function peek(int $length = 1): string
    {
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            if ($this->offset + $i >= count($this->input)) {
                break;
            }
            $result .= $this->input[$this->offset + $i];
        }
        return $result;
    }

    /**
     * @return int
     */
    public function tell(): int
    {
        return $this->offset;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->offset < count($this->input);
    }

    /**
     * @return $this
     * @throws UnexpectedEndOfInputException
     */
    public function assertValid(): static
    {
        if (!$this->valid()) {
            throw new UnexpectedEndOfInputException("Unexpected end of JSON input", $this->tell());
        }
        return $this;
    }

    /**
     * @param string ...$expected
     * @return bool
     */
    public function check(string ...$expected): bool
    {
        foreach ($expected as $value) {
            if ($this->peek(mb_strlen($value)) === $value) {
                return true;
            }
        }
        return false;
    }

    public function checkInsensitive(string ...$expected): bool
    {
        foreach ($expected as $value) {
            if (strcasecmp($this->peek(mb_strlen($value)), $value) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string ...$expected
     * @return $this
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    public function assert(string ...$expected): static
    {
        $this->assertValid();
        if (!$this->check(...$expected)) {
            throw new UnexpectedInputException("Expected " . implode(" or ", $expected) .
                ", got " . $this->peek(16), $this->tell());
        }
        return $this;
    }

    /**
     * @param string ...$expected
     * @return $this
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    public function assertInsensitive(string ...$expected): static
    {
        $this->assertValid();
        if (!$this->checkInsensitive(...$expected)) {
            throw new UnexpectedInputException("Expected " . implode(" or ", $expected) .
                ", got " . $this->peek(16), $this->tell());
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function skipWhitespace(): static
    {
        do {
            if ($this->check(...static::WHITESPACE)) {
                $this->skip();
                continue;
            }

            if (LineComment::detect($this)) {
                LineComment::skip($this);
                continue;
            }

            if (BlockComment::detect($this)) {
                BlockComment::skip($this);
                continue;
            }

            break;
        } while ($this->valid());
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }
}

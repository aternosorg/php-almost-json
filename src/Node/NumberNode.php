<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\AlmostJsonParser;

class NumberNode extends AlmostJsonNode
{
    protected const OCTAL = ["0", "1", "2", "3", "4", "5", "6", "7"];
    protected const BINARY = ["0", "1"];
    protected const DECIMAL = ".";
    protected const EXPONENT = ["e", "E"];
    protected const SIGN = ["+", "-"];
    protected const INFINITY = "Infinity";
    protected const NAN = "NaN";

    protected int|float $value = 0;

    /**
     * @inheritDoc
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool
    {
        return $input->check(static::INFINITY, static::NAN, static::DECIMAL, ...static::SIGN, ...static::NUMBERS);
    }

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @inheritDoc
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void
    {
        $sign = $this->readSign($input);
        $input->assert(static::INFINITY, static::NAN, static::DECIMAL, ...static::NUMBERS);

        if ($input->checkInsensitive("0x")) {
            $input->skip(2);
            $input->assert(...static::HEX);
            $str = $input->readAll(...static::HEX);
            $value = hexdec($str);
        } else if ($input->checkInsensitive("0b")) {
            $input->skip(2);
            $input->assert(...static::BINARY);
            $str = $input->readAll(...static::BINARY);
            $value = bindec($str);
        } else if ($parser->isZeroPrefixOctal() && $input->check("0")) {
            $input->skip();
            if (!$input->check(...static::OCTAL)) {
                $value = 0;
            } else {
                $str = $input->readAll(...static::OCTAL);
                $value = octdec($str);
            }
        } else if ($input->checkInsensitive("0o")) {
            $input->skip(2);
            $input->assert(...static::OCTAL);
            $str = $input->readAll(...static::OCTAL);
            $value = octdec($str);
        } else if ($input->checkInsensitive("Infinity")) {
            $input->skip(8);
            $value = INF;
        } else if ($input->checkInsensitive("NaN")) {
            $input->skip(3);
            $value = NAN;
        } else {
            $float = false;
            $input->assert(static::DECIMAL, ...static::NUMBERS);
            $str = $input->readAll(...static::NUMBERS);
            if ($input->check(static::DECIMAL)) {
                $str .= $input->read();
                if ($input->check(...static::NUMBERS)) {
                    $str .= $input->readAll(...static::NUMBERS);
                } else {
                    $str .= "0";
                }
                $float = true;
            }
            if ($input->check(...static::EXPONENT)) {
                $str .= $input->read();
                $str .= $this->readSign($input);
                $input->assert(...static::NUMBERS);
                $str .= $input->readAll(...static::NUMBERS);
                $float = true;
            }

            if ($float) {
                $value = floatval($str);
            } else {
                $value = intval($str);
            }
        }

        if ($sign === "-") {
            $value = -$value;
        }

        $this->value = $value;
    }

    /**
     * @param Input $input
     * @return string
     */
    protected function readSign(Input $input): string
    {
        if ($input->check(...static::SIGN)) {
            return $input->read();
        }
        return "+";
    }

    /**
     * @return int|float
     */
    public function getValue(): int|float
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): int|float
    {
        return $this->value;
    }
}

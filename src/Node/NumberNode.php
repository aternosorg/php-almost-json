<?php

namespace Aternos\AlmostJson\Node;

use Aternos\AlmostJson\Input;
use Aternos\AlmostJson\AlmostJsonParser;

class NumberNode extends AlmostJsonNode
{
    protected const UNDERSCORE = "_";
    protected const OCTAL = ["0", "1", "2", "3", "4", "5", "6", "7", self::UNDERSCORE];
    protected const BINARY = ["0", "1", self::UNDERSCORE];
    protected const HEX_AND_UNDERSCORE = [...self::HEX, self::UNDERSCORE];
    protected const NUMBERS_AND_UNDERSCORE = [...self::NUMBERS, self::UNDERSCORE];
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
            $input->assert(...static::HEX_AND_UNDERSCORE);
            $str = $input->readAll(...static::HEX_AND_UNDERSCORE);
            $value = hexdec(str_replace(static::UNDERSCORE, "", $str));
        } else if ($input->checkInsensitive("0b")) {
            $input->skip(2);
            $input->assert(...static::BINARY);
            $str = $input->readAll(...static::BINARY);
            $value = bindec(str_replace(static::UNDERSCORE, "", $str));
        } else if ($parser->isZeroPrefixOctal() && $input->check("0")) {
            $input->skip();
            if (!$input->check(...static::OCTAL)) {
                $value = 0;
            } else {
                $str = $input->readAll(...static::OCTAL);
                $value = octdec(str_replace(static::UNDERSCORE, "", $str));
            }
        } else if ($input->checkInsensitive("0o")) {
            $input->skip(2);
            $input->assert(...static::OCTAL);
            $str = $input->readAll(...static::OCTAL);
            $value = octdec(str_replace(static::UNDERSCORE, "", $str));
        } else if ($input->checkInsensitive("Infinity")) {
            $input->skip(8);
            $value = INF;
        } else if ($input->checkInsensitive("NaN")) {
            $input->skip(3);
            $value = NAN;
        } else {
            $float = false;
            $input->assert(static::DECIMAL, ...static::NUMBERS_AND_UNDERSCORE);
            $str = $input->readAll(...static::NUMBERS_AND_UNDERSCORE);
            if ($input->check(static::DECIMAL)) {
                $str .= $input->read();
                if ($input->check(...static::NUMBERS_AND_UNDERSCORE)) {
                    $str .= $input->readAll(...static::NUMBERS_AND_UNDERSCORE);
                } else {
                    $str .= "0";
                }
                $float = true;
            }
            if ($input->check(...static::EXPONENT)) {
                $str .= $input->read();
                $str .= $this->readSign($input);
                $input->assert(...static::NUMBERS_AND_UNDERSCORE);
                $str .= $input->readAll(...static::NUMBERS_AND_UNDERSCORE);
                $float = true;
            }

            $str = str_replace(static::UNDERSCORE, "", $str);
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

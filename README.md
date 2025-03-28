# aternos/almost-json

A library to parse all those things that are kind of JSON but not quite.

## Why?

Over the years, many accidentally or intentionally broken JSON parsers have emerged.
If a software uses such a parser, users of that software might start writing broken JSON
that is accepted by that parser, but not actually valid.  
This makes writing code that deals with files originally created for such software very
annoying, since some files will just randomly not work with a standard JSON parser because
someone decided that wrapping a string in quotes was just way too much work for them.

This library aims to correctly parse normal JSON, while also accommodating as many weird
quirks as possible.

## Supported JSON quirks

When parsing valid JSON, this library should return the exact same result as PHP's built-in
`json_decode` function. Additionally, the following quirks are supported:

- Line comments (`//` and `#`)
- Block comments (`/* ... */`)
- Trailing commas
- Unquoted keys
- Strings
  - Single quotes (`'`) instead of double quotes (`"`)
  - Backticks (`` ` ``) instead of double quotes (`"`)
  - Unquoted strings
  - Escape sequences `\v` and `\0`
  - Ignore invalid escape sequences (e.g. `\a` => `a`)
  - Line continuation (`\` at the end of a line)
  - Literal newlines
- Numbers
  - Hexadecimal numbers (`0x...`)
  - Binary numbers (`0b...`)
  - Octal numbers (`0o...`)
  - Octal numbers with only `0` as prefix (`0...`) (not enabled by default)
  - Allow leading zeros (e.g. `0123`) for decimal numbers if octal numbers are enabled
  - Exponential notation with `e` or `E`
  - Underscores in numbers (e.g. `1_000_000`)
  - Decimal numbers with a trailing dot (e.g. `1.`)
  - Decimal numbers with a leading dot (e.g. `.1`)
  - Float values `NaN`, `Infinity`, and `-Infinity`
  - Underscores in values (e.g. `1_000_000.0`)
- Case-insensitive boolean values
- Case-insensitive null

## Usage

### Installation

```bash
composer require aternos/almost-json
```

### Parsing

```php
$parser = new \Aternos\AlmostJson\AlmostJsonParser();
$parsed = $parser->parseString('{key: "value"}', assoc: true);

// => ['key' => 'value']
```

By default, the parser will assume the string is encoded as UTF-8. To use a different encoding
you can call the `parse` method, which accepts an `Input` object instead of a string.

```php
$parser = new \Aternos\AlmostJson\AlmostJsonParser();

$input = new \Aternos\AlmostJson\Input('{key: "value"}', encoding: "ISO-8859-1");
$parsed = $parser->parse($input, assoc: true);
```

To automatically detect the correct encoding, you can pass the result of the `mb_detect_encoding` function.

### Options

There are a number of options that can be set for a parser instance.

```php

$parser = new \Aternos\AlmostJson\AlmostJsonParser();
$parser->setMaxDepth(512) // Maximum depth of the JSON tree
    ->setZeroPrefixOctal(true) // Parse numbers with leading zero as octal
    ->setTopLevelUnquotedStringAllowed(true); // Allow the root of the JSON tree to be an unquoted string

```

## Performance

This library is almost certainly significantly slower than PHP's built-in JSON implementation.
If performance is critical, and most files are expected to be valid JSON, it might make sense
to first try to use the built-in JSON parser, and only fall back to this library if it fails.

```php

try {
    $parsed = json_decode($string, true, flags: JSON_THROW_ON_ERROR);
} catch (\JsonException $e) {
    $parser = new \Aternos\AlmostJson\AlmostJsonParser();
    $parsed = $parser->parseString($string, assoc: true);
}

```

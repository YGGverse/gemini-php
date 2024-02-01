<?php

declare(strict_types=1);

namespace Yggverse\Gemini\Dokuwiki;

class Reader
{
    private array $_rules =
    [
        // Headers
        '/^([\s]?)#([^#]+)/' => '$1#$2' . PHP_EOL,
        '/^([\s]?)##([^#]+)/' => '$1##$2' . PHP_EOL,
        '/^([\s]?)###([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]?)####([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]?)#####([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]?)######([^#]+)/' => '$1###$2' . PHP_EOL,

        '/^[\s]?[=]{6}([^=]+)[=]{6}/' => '# $1' . PHP_EOL,
        '/^[\s]?[=]{5}([^=]+)[=]{5}/' => '## $1' . PHP_EOL,
        '/^[\s]?[=]{4}([^=]+)[=]{4}/' => '### $1' . PHP_EOL,
        '/^[\s]?[=]{3}([^=]+)[=]{3}/' => '### $1' . PHP_EOL,
        '/^[\s]?[=]{2}([^=]+)[=]{2}/' => '### $1' . PHP_EOL,
        '/^[\s]?[=]{1}([^=]+)[=]{1}/' => '### $1' . PHP_EOL,

        // Tags
        '/\*\*([^\*]{2,})\*\*/' => '$1',
        '/\'\'([^\']{2,})\'\'/' => '$1',
        '/\%\%([^\%]{2,})\%\%/' => '$1',
        '/\/\/([^\/]{2,})\/\//' => '$1',
        '/([^:]{1})\/\/([^\/]{2,})\/\//' => '$1 $2',

        // Links

        /// Detect IPv6 (used as no idea how to resolve square quotes in rules below)
        '/\[\[([^\[]+)\[([A-f:0-9]*)\]([^\]]+)\]\]/' => '$1~IPv6:open~$2~IPv6:close~$3',

        /// Remove extra spaces
        '/\[\[\s?([^\|]+)\s?\|\s?([^\]]+)\s?\]\]/' => '[[$1|$2]]',
        '/\[\[\s?([^\]]+)\s?\]\]/' => '[[$1]]',

        '/\{\{\s?([^\|]+)\s?\|\s?([^\}]+)\s?\}\}/' => '{{$1|$2}}',
        '/\{\{\s?([^\}]+)\s?\}\}/' => '{{$1}}',

        /// Wikipedia
        '/\[\[wp([A-z]{2,})>([^\|]+)\|([^\]]+)\]\]/ui' => '$3 ( https://$1.wikipedia.org/wiki/$2 )',
        '/\[\[wp>([^\|]+)\|([^\]]+)\]\]/i' => '$2 ( https://en.wikipedia.org/wiki/$1 )',
        '/\[\[wp([A-z]{2,})>([^\]]+)\]\]/i' => '$2 ( https://$1.wikipedia.org/wiki/$2 )',
        '/\[\[wp>([^\]]+)\]\]/i' => '$1 ( https://en.wikipedia.org/wiki/$1 )',

        /// Dokuwiki
        '/\[\[doku>([^\|]+)\|([^\]]+)\]\]/i' => '$2( https://www.dokuwiki.org/$1 )',
        '/\[\[doku>([^\]]+)\]\]/i' => '$1( https://www.dokuwiki.org/$1 )',

        /// Index
        '/\{\{indexmenu>:([^\}]+)\}\}/i' => '', // @TODO
        '/\{\{indexmenu_n>[\d]+\}\}/i' => '', // @TODO

        // Related
        '/\[\[this>([^\|]+)\|([^\]]+)\]\]/i' => '$2',

        /// Relative
        '/\[\[(?!https?:|this|doku|wp[A-z]{0,2})([^\|]+)\|([^\]]+)\]\]/i' => ' $2$3 ( /$1 )',
        '/\[\[(?!https?:|this|doku|wp[A-z]{0,2})([^\]]+)\]\]/i' => ' $2 ( /$1 )',

        /// Absolute
        '/\[\[(https?:)([^\|]+)\|([^\]]+)\]\]/i' => '$3 ( $1$2 )',
        '/\[\[(https?:)([^\]]+)\]\]/i' => '$1$2', // @TODO

        /// Apply macros
        '/~IPv6:open~/' => '[',
        '/~IPv6:close~/' => ']',

        // List
        '/^[\s]?-/' => '* ',
        '/^[\s]+\*/' => '*',

        // Separators
        '/[\\\]{2}/' => PHP_EOL,

        // Plugins
        '/~~DISCUSSION~~/' => '', // @TODO
        '/~~INFO:syntaxplugins~~/' => '', // @TODO

        // Final corrections
        '/[\n\r]+[.,;:]+/' => PHP_EOL
    ];

    public function __construct(?array $rules = null)
    {
        if ($rules)
        {
            $this->_rules = $rules;
        }
    }

    public function getRules(): array
    {
        $this->_rules;
    }

    public function setRules(array $rules)
    {
        $this->_rules = $rules;
    }

    public function getRule(string $key, string $value): ?string
    {
        $this->_rules[$key] = isset($this->_rules[$key]) ? $value : null;
    }

    public function setRule(string $key, string $value): void
    {
        $this->_rules[$key] = $value;
    }

    public function toGemini(string $data, ?array &$lines = []): string
    {
        $raw = false;

        foreach ((array) explode(PHP_EOL, $data) as $line)
        {
            // Skip any formating in lines between code tag
            if (!$raw && preg_match('/<(code|file)([^>])*>/i', $line, $matches))
            {
                // Prepend tag meta or filename as plain description
                if (!empty($matches[0]))
                {
                    $lines[] = preg_replace(
                        '/^<.*\s(.+)>$/',
                        '$1',
                        $matches[0]
                    );
                }

                $lines[] = '```';
                $lines[] = $line;

                $raw = true;

                // Make sure inline tag closed
                if (preg_match('/<\/(code|file)>/i', $line))
                {
                    $lines[] = $line;
                    $lines[] = '```';

                    $raw = false;

                    continue;
                }

                continue;
            }

            if ($raw && preg_match('/<\/(code|file)>/i', $line))
            {
                $lines[] = $line;
                $lines[] = '```';

                $raw = false;

                continue;
            }

            if ($raw)
            {
                $lines[] = $line;

                continue;
            }

            // Apply common line rules
            $lines[] = preg_replace(
                array_keys(
                    $this->_rules
                ),
                array_values(
                    $this->_rules
                ),
                $line
            );
        }

        return preg_replace(
            '/[\n\r]{2,}/',
            PHP_EOL . PHP_EOL,
            strip_tags(
                implode(
                    PHP_EOL,
                    $lines
                )
            )
        );
    }

    public function getH1(string $data): ?string
    {
        foreach ((array) explode(PHP_EOL, $data) as $line)
        {
            preg_match_all(
                '/^[\s]?#([^#]+)/',
                $line,
                $matches
            );

            if (!empty($matches[1]))
            {
                return trim(
                    $matches[1]
                );

                break;
            }
        }
    }
}
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

        // Tags (@TODO make per-line internal content processing)
        '/<code>/i' => PHP_EOL . '```' . PHP_EOL,
        '/<\/code>/i' => PHP_EOL . '```' . PHP_EOL,

        '/<file>/i' => PHP_EOL . '```' . PHP_EOL,
        '/<file[\s]?[-]?[\s]?([^>]+)>/i' => '$1' . PHP_EOL . '```' . PHP_EOL,
        '/<\/file>/i' => '```',

        '/\*\*([^\*]{2,})\*\*/' => '$1',
        '/\'\'([^\']{2,})\'\'/' => '$1',
        '/\%\%([^\%]{2,})\%\%/' => '$1',
        '/([^:]{1})\/\/([^\/]{2,})\/\//' => '$1 $2',

        // Links

        /// Wikipedia
        '/\[\[wp([A-z]{2,})>([^\|]+)\|([^\]]+)\]\]/i' => PHP_EOL . '=> https://$1.wikipedia.org/wiki/$2 $3' . PHP_EOL,
        '/\[\[wp>([^\|]+)\|([^\]]+)\]\]/i' => PHP_EOL . '=> https://en.wikipedia.org/wiki/$1 $2' . PHP_EOL,

        /// Dokuwiki
        '/\[\[doku>([^\|]+)\|([^\]]+)\]\]/i' => PHP_EOL . '=> https://www.dokuwiki.org/$1 $2' . PHP_EOL,

        /// Index
        '/\{\{indexmenu>:([^\}]+)\}\}/i' => PHP_EOL . '=> $1' . PHP_EOL, // @TODO
        '/\{\{indexmenu_n>[\d]+\}\}/i' => '', // @TODO

        /// Relative
        '/\[\[(?!https?:|gemini:|this>|doku>|wp[A-z]{0,2}>)([^\|]+)\|([^\]]+)\]\]/i' => PHP_EOL . '=> /$1 $2$3' . PHP_EOL,
        '/\[\[(?!https?:|gemini:|this>|doku>|wp[A-z]{0,2}>)([^\]]+)\]\]/i' => PHP_EOL . '=> /$1 $2' . PHP_EOL,

        // Related
        '/\[\[this>([^\|]+)\|([^\]]+)\]\]/i' => '$2',

        /// Absolute
        '/\[\[(https?:|gemini:)([^\|]+)\|([^\]]+)\]\]/i' => PHP_EOL . '=> $1$2 $3' . PHP_EOL,

        // List
        '/^[\s]?-/' => '* ',
        '/^[\s]+\*/' => '*',

        // Separators
        '/[\\\]{2}/' => PHP_EOL,

        // Plugins
        '/~~DISCUSSION~~/' => '', // @TODO

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

    public function toGemini(string $data): string
    {
        $lines = [];

        foreach ((array) explode(PHP_EOL, $data) as $line)
        {
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
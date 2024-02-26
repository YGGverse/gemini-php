<?php

declare(strict_types=1);

namespace Yggverse\Gemini\Dokuwiki;

use dekor\ArrayToTextTable;

class Reader
{
    private array $_macros =
    [
        '~URL:base~' => null,
        '~IPv6:open~' => '[',
        '~IPv6:close~' => ']',
        '~LINE:break~' => PHP_EOL
    ];

    private array $_rule =
    [
        // Headers
        '/^([\s]*)#([^#]+)/' => '$1#$2' . PHP_EOL,
        '/^([\s]*)##([^#]+)/' => '$1##$2' . PHP_EOL,
        '/^([\s]*)###([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]*)####([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]*)#####([^#]+)/' => '$1###$2' . PHP_EOL,
        '/^([\s]*)######([^#]+)/' => '$1###$2' . PHP_EOL,

        '/^[\s]*[=]{6}([^=]+)[=]{6}/' => '# $1' . PHP_EOL,
        '/^[\s]*[=]{5}([^=]+)[=]{5}/' => '## $1' . PHP_EOL,
        '/^[\s]*[=]{4}([^=]+)[=]{4}/' => '### $1' . PHP_EOL,
        '/^[\s]*[=]{3}([^=]+)[=]{3}/' => '### $1' . PHP_EOL,
        '/^[\s]*[=]{2}([^=]+)[=]{2}/' => '### $1' . PHP_EOL,
        '/^[\s]*[=]{1}([^=]+)[=]{1}/' => '### $1' . PHP_EOL,

        // Tags
        '/\*\*/' => '',
        '/\'\'/' => '',
        '/\%\%/' => '',
        '/(?<!:)\/\//' => '',

        // Remove extra spaces
        '/(\s)\s+/' => '$1',

        // Links

        /// Detect IPv6 (used as no idea how to resolve square quotes in rules below)
        '/\[\[([^\[]+)\[([A-f:0-9]*)\]([^\]]+)\]\]/' => '$1~IPv6:open~$2~IPv6:close~$3',

        /// Remove extra chars
        '/\[\[\s*\:?([^\|]+)\s*\|\s*([^\]]+)\s*\]\]/' => '[[$1|$2]]',
        '/\[\[\s*\:?([^\]]+)\s*\]\]/' => '[[$1]]',

        '/\{\{\s*\:?([^\|]+)\s*\|\s*([^\}]+)\s*\}\}/' => '{{$1|$2}}',
        '/\{\{\s*\:?([^\}]+)\s*\}\}/' => '{{$1}}',

        /// Wikipedia
        '/\[\[wp([A-z]{2,})>([^\|]+)\|([^\]]+)\]\]/ui' => '$3 ( https://$1.wikipedia.org/wiki/$2 )',
        '/\[\[wp>([^\|]+)\|([^\]]+)\]\]/i' => '$2 ( https://en.wikipedia.org/wiki/$1 )',
        '/\[\[wp([A-z]{2,})>([^\]]+)\]\]/i' => '$2 ( https://$1.wikipedia.org/wiki/$2 )',
        '/\[\[wp>([^\]]+)\]\]/i' => '$1 ( https://en.wikipedia.org/wiki/$1 )',

        /// Dokuwiki
        '/\[\[doku>([^\|]+)\|([^\]]+)\]\]/i' => '$2( https://www.dokuwiki.org/$1 )',
        '/\[\[doku>([^\]]+)\]\]/i' => '$1( https://www.dokuwiki.org/$1 )',

        /// Index
        /// Useful with src/Dokuwiki/Helper.php
        '/\{\{indexmenu>:([^\}]+)\}\}/i' => '',
        '/\{\{indexmenu_n>[\d]+\}\}/i' => '',

        // Related
        '/\[\[this>([^\|]+)\|([^\]]+)\]\]/i' => '$2',

        /// Relative
        '/\[\[(?!https?:|this|doku|wp[A-z]{0,2})([^\|]+)\|([^\]]+)\]\]/i' => ' $2$3 ( ~URL:base~$1 )',
        '/\[\[(?!https?:|this|doku|wp[A-z]{0,2})([^\]]+)\]\]/i' => ' $2 ( ~URL:base~$1 )',

        /// Absolute
        '/\[\[(https?:)([^\|]+)\|([^\]]+)\]\]/i' => '$3 ( $1$2 )',
        '/\[\[(https?:)([^\]]+)\]\]/i' => '$1$2', // @TODO

        /// Media
        '/\{\{(?!https?:)([^\|]+)\|([^\}]+)\}\}/i' => PHP_EOL . '=> /$1$2' . PHP_EOL,
        '/\{\{(?!https?:)([^\}]+)\}\}/i' => PHP_EOL . '=> /$1$2' . PHP_EOL,

        // List
        '/^[\s]?-/' => '* ',
        '/^[\s]+\*/' => '*',

        // Separators
        '/[\\\]{2}/' => '~LINE:break~',

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
            $this->_rule = $rules;
        }
    }

    // Macros operations
    public function getMacroses(): array
    {
        $this->_macros;
    }

    public function setMacroses(array $macros)
    {
        $this->_macros = $macros;
    }

    public function getMacros(string $key, string $value): ?string
    {
        $this->_macros[$key] = isset($this->_macros[$key]) ? $value : null;
    }

    public function setMacros(string $key, ?string $value): void
    {
        if ($value)
        {
            $this->_macros[$key] = $value;
        }

        else
        {
            unset(
                $this->_macros[$key]
            );
        }
    }

    // Rule operations
    public function getRules(): array
    {
        $this->_rule;
    }

    public function setRules(array $rules)
    {
        $this->_rule = $rules;
    }

    public function getRule(string $key, string $value): ?string
    {
        $this->_rule[$key] = isset($this->_rule[$key]) ? $value : null;
    }

    public function setRule(string $key, ?string $value): void
    {
        if ($value)
        {
            $this->_rule[$key] = $value;
        }

        else
        {
            unset(
                $this->_rule[$key]
            );
        }
    }

    // Convert DokuWiki text to Gemini
    public function toGemini(?string $data, ?array &$lines = []): ?string
    {
        if (empty($data))
        {
            return null;
        }

        $raw = false;

        $lines = [];

        foreach ((array) explode(PHP_EOL, $data) as $line)
        {
            // Skip any formatting in lines between code tag
            if (!$raw && preg_match('/<(code|file)([^>]*)>/i', $line, $matches))
            {
                // Prepend tag meta or filename as plain description
                if (!empty($matches[0]))
                {
                    $lines[] = preg_replace(
                        '/<(code|file)[\s-]*([^>]*)>/i',
                        '$2',
                        $matches[0]
                    );
                }

                $lines[] = '```';
                $lines[] = preg_replace(
                    '/<\/?(code|file)[^>]*>/i',
                    '',
                    $line
                );

                $raw = true;

                // Make sure inline tag closed
                if (preg_match('/<\/(code|file)>/i', $line))
                {
                    $lines[] = '```';

                    $raw = false;

                    continue;
                }

                continue;
            }

            if ($raw && preg_match('/<\/(code|file)>/i', $line))
            {
                $lines[] = preg_replace(
                    '/<\/(code|file)>/i',
                    '',
                    $line
                );

                $lines[] = '```';

                $raw = false;

                continue;
            }

            if ($raw)
            {
                $lines[] = preg_replace(
                    '/^```/',
                    ' ```',
                    $line
                );

                continue;
            }

            // Apply config
            $lines[] = preg_replace(
                array_keys(
                    $this->_rule
                ),
                array_values(
                    $this->_rule
                ),
                strip_tags(
                    $line
                )
            );
        }

        // ASCII table
        $table = false;

        $rows = [];

        $th = [];

        foreach ($lines as $index => $line)
        {
            // Strip line breaks
            $line = str_replace(
                '~LINE:break~',
                ' ',
                $line
            );

            // Header
            if (!$table && preg_match_all('/\^([^\^]+)/', $line, $matches))
            {
                if (!empty($matches[1]))
                {
                    $table = true;

                    $rows = [];

                    $th = [];

                    foreach ($matches[1] as $value)
                    {
                        $th[] = trim(
                            $value
                        );
                    }

                    unset(
                        $lines[$index]
                    );

                    continue;
                }
            }

            // Body
            if ($table)
            {
                $table = false;

                if (preg_match(sprintf('/%s\|/', str_repeat('\|(.*)', count($th))), $line, $matches))
                {
                    if (count($matches) == count($th) + 1)
                    {
                        $table = true;

                        $row = [];
                        foreach ($th as $offset => $column)
                        {
                            $row[$column] = trim(
                                $matches[$offset + 1]
                            );
                        }

                        $rows[] = $row;

                        unset(
                            $lines[$index]
                        );
                    }
                }

                if (!$table && $rows)
                {
                    $builder = new ArrayToTextTable(
                        $rows
                    );

                    $lines[$index] = '```' . PHP_EOL . $builder->render() . PHP_EOL . '```';
                }
            }
        }

        // Merge lines
        return preg_replace(
            '/[\n\r]{2,}/',
            PHP_EOL . PHP_EOL,
            str_replace(
                array_keys(
                    $this->_macros
                ),
                array_values(
                    $this->_macros
                ),
                implode(
                    PHP_EOL,
                    $lines
                )
            )
        );
    }

    public function getH1(?string $gemini, ?string $regex = '/^[\s]?#([^#]+)/'): ?string
    {
        foreach ((array) explode(PHP_EOL, (string) $gemini) as $line)
        {
            preg_match(
                $regex,
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

        return null;
    }

    public function getLinks(?string $gemini, ?string $regex = '/(https?|gemini):\/\/\S+/'): array
    {
        $links = [];

        if (empty($gemini))
        {
            return $links;
        }

        preg_match_all(
            $regex,
            $gemini,
            $matches
        );

        if (!empty($matches[0]))
        {
            foreach ((array) $matches[0] as $link)
            {
                $links[] = trim(
                    $link
                );
            }
        }

        return array_unique(
            $links
        );
    }
}
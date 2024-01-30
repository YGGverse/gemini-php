<?php

declare(strict_types=1);

namespace Yggverse\Gemini;

class Dokuwiki
{
    private array $_dictionary =
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

        // Links
        '/\{\{([^:]+):([^\}]+)\}\}/' => PHP_EOL . '=> $1 $1' . PHP_EOL, // @TODO
        '/\{\{indexmenu\>:([^\}]+)\}\}/' => PHP_EOL . '=> $1 $1' . PHP_EOL, // @TODO
        '/\[\[wp([A-z]{2})\>([^\|]+)\|([^\]\]]+)\]\]/' => PHP_EOL . '=> https://$1.wikipedia.org/wiki/$2 $3' . PHP_EOL,
        '/\[\[wp\>([^\|]+)\|([^\]\]]+)\]\]/' => PHP_EOL . '=> https://en.wikipedia.org/wiki/$1 $2' . PHP_EOL,
        '/\[\[([^|]+)\|([^\]\]]+)\]\]/' => PHP_EOL . '=> $1 $2' . PHP_EOL,

        // Tags
        '/<code>/i' => '```',
        '/<\/code>/i' => '```',
        '/<wrap[^>]+>([^<]?)/i' => '$1',
        '/<\/wrap>/i' => '$1',

        '/<file>/i' => '```',
        '/<file[\s]+-[\s]+([^>]+)>/i' => '$1```',
        '/<\/file>/i' => '```',

        //'/[*]+([^*]+)[*]+/' => '$1', // @TODO bugged, e.g. crontab tasks
        '/\'\'([^\']+)\'\'/' => '$1',
        '/%%([^%]+)%%/' => '$1',
        '/\/\/^:([^\/]+)\/\//' => '$1',

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

    public function __construct(?array $dictionary = null)
    {
        if ($dictionary)
        {
            $this->_dictionary = $dictionary;
        }
    }

    public function getDictionary(): array
    {
        $this->_dictionary;
    }

    public function setDictionary(array $dictionary)
    {
        $this->_dictionary = $dictionary;
    }

    public function getRule(string $key, string $value): ?string
    {
        $this->_dictionary[$key] = isset($this->_dictionary[$key]) ? $value : null;
    }

    public function setRule(string $key, string $value): void
    {
        $this->_dictionary[$key] = $value;
    }

    public function toGemini(string $data): string
    {
        $lines = [];

        foreach ((array) explode(PHP_EOL, $data) as $line)
        {
            $lines[] = preg_replace(
                array_keys(
                    $this->_dictionary
                ),
                array_values(
                    $this->_dictionary
                ),
                $line
            );
        }

        return preg_replace(
            '/[\n\r]{2,}/',
            PHP_EOL . PHP_EOL,
            implode(
                PHP_EOL,
                $lines
            )
        );
    }
}
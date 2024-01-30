<?php

declare(strict_types=1);

namespace Yggverse\Gemini;

class Dokuwiki
{
    private array $_dictionary =
    [
        // Headers
        '/^[\s]?#([^#]+)/' => "# $1\n\r",
        '/^[\s]?##([^#]+)/' => "## $1\n\r",
        '/^[\s]?###([^#]+)/' => "### $1\n\r",
        '/^[\s]?####([^#]+)/' => "### $1\n\r",
        '/^[\s]?#####([^#]+)/' => "### $1\n\r",
        '/^[\s]?######([^#]+)/' => "### $1\n\r",

        '/^[\s]?[=]{6}([^=]+)[=]{6}/' => "# $1\n\r",
        '/^[\s]?[=]{5}([^=]+)[=]{5}/' => "## $1\n\r",
        '/^[\s]?[=]{4}([^=]+)[=]{4}/' => "### $1\n\r",
        '/^[\s]?[=]{3}([^=]+)[=]{3}/' => "### $1\n\r",
        '/^[\s]?[=]{2}([^=]+)[=]{2}/' => "### $1\n\r",
        '/^[\s]?[=]{1}([^=]+)[=]{1}/' => "### $1\n\r",

        // Links
        // '/\{\{([^\:]+)\:([^\}\}]+)\}\}/' => "=> /$1 $1\n\r", // @TODO
        '/\{\{indexmenu\>\:([^\}\}]+)\}\}/' => "=> /$1 $1\n\r", // @TODO
        '/\[\[wp([A-z]{2})\>([^\|]+)\|([^\]\]]+)\]\]/' => "=> https://$1.wikipedia.org/wiki/$2 $3\n\r",
        '/\[\[wp\>([^\|]+)\|([^\]\]]+)\]\]/' => "=> https://en.wikipedia.org/wiki/$1 $2\n\r",
        '/\[\[([^|]+)\|([^\]\]]+)\]\]/' => "=> $1 $2\n\r",

        // Tags
        '/<code>/' => '```',
        '/<\/code>/' => '```',

        '/<file>/' => '```',
        '/<file\s-\s([^>]+)>/' => "$1\n\r" . '```',
        '/<\/file>/' => '```',

        '/[*]+([^*]+)[*]+/' => '*$1*',
        '/[\'\']+([^\']+)[\'\']+/' => '```$1```',

        // List
        '/^-/' => '* ',

        // Separators
        '/[-]+/' => '-',
        '/[ ]+/' => ' ',
        '/[\\\]+/' => "\n\r",
        '/[\n\r]{1,}/' => "\n\r",
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
            $lines[] = trim(
                preg_replace(
                    array_keys(
                        $this->_dictionary
                    ),
                    array_values(
                        $this->_dictionary
                    ),
                    trim(
                        $line
                    )
                )
            );
        }

        return implode(
            PHP_EOL,
            $lines
        );
    }
}
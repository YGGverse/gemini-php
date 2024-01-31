<?php

declare(strict_types=1);

namespace Yggverse\Gemini\Dokuwiki;

class Filesystem
{
    private $_path;
    private $_tree = [];
    private $_list = [];

    public function __construct(string $path)
    {
        $this->_path = rtrim(
            $path,
            '/'
        );

        $this->_index(
            $this->_path
        );
    }

    public function getTree(): array
    {
        return $this->_tree;
    }

    public function getList(): array
    {
        return $this->_list;
    }

    public function getPagePathByUri(string $uri): ?string
    {
        $uri = urldecode(
            $uri
        );

        $path = sprintf(
            '%s/pages/%s.txt',
            $this->_path,
            str_replace(
                ':',
                '/',
                $uri
            )
        );

        if (!in_array($path, $this->_list) || !is_file($path) || !is_readable($path))
        {
            return null;
        }

        return $path;
    }

    public function getPageUriByPath(string $path): ?string
    {
        $path = str_replace(
            sprintf(
                '%s/pages/',
                $this->_path
            ),
            null,
            $path
        );

        $path = trim(
            $path,
            '/'
        );

        $path = basename(
            $path
        );

        $path = str_replace(
            [
                '/',
                '.txt'
            ],
            [
                ':',
                null
            ],
            $path
        );

        return urlencode(
            $path
        );
    }

    private function _index(string $path): void
    {
        foreach ((array) scandir($path) as $file)
        {
            if (in_array($file, ['.', '..']))
            {
                continue;
            }

            $file = sprintf(
                '%s/%s',
                $path,
                $file
            );

            switch (true)
            {
                case is_dir($file):

                    $this->_index($file);

                break;

                case is_file($file):

                    $this->_tree[$path][] = $file;

                    $this->_list[] = $file;

                break;
            }
        }
    }
}
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

    public function getPagePathsByPath(string $path): ?array
    {
        if (isset($this->_tree[$path]))
        {
            return $this->_tree[$path];
        }

        return null;
    }

    public function getPagePathByUri(string $uri): ?string
    {
        $path = sprintf(
            '%s/pages/%s.txt',
            $this->_path,
            str_replace(
                ':',
                '/',
                mb_strtolower(
                    urldecode(
                        $uri
                    )
                )
            )
        );

        if (!$this->isPath($path))
        {
            return null;
        }

        return $path;
    }

    public function getPageUriByPath(string $path): ?string
    {
        if (!$this->isPath($path))
        {
            return null;
        }

        $path = str_replace(
            sprintf(
                '%s/pages/',
                $this->_path
            ),
            '',
            $path
        );

        $path = trim(
            $path,
            '/'
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

        return $path;
    }

    public function getDirectoryPathByUri(string $uri = ''): ?string
    {
        $path = rtrim(
            sprintf(
                '%s/pages/%s',
                $this->_path,
                str_replace(
                    ':',
                    '/',
                    mb_strtolower(
                        urldecode(
                            $uri
                        )
                    )
                )
            ),
            '/'
        );

        if (!isset($this->_tree[$path]) || !is_dir($path) || !is_readable($path))
        {
            return null;
        }

        return $path;
    }

    public function getDirectoryUriByPath(string $path): ?string
    {
        if (!isset($this->_tree[$path]) || !is_dir($path) || !is_readable($path))
        {
            return null;
        }

        $path = str_replace(
            sprintf(
                '%s/pages',
                $this->_path
            ),
            '',
            $path
        );

        $path = trim(
            $path,
            '/'
        );

        $path = str_replace(
            [
                '/'
            ],
            [
                ':'
            ],
            $path
        );

        return $path;
    }

    public function getMediaPathByUri(string $uri): ?string
    {
        $path = sprintf(
            '%s/media/%s',
            $this->_path,
            str_replace(
                ':',
                '/',
                mb_strtolower(
                    urldecode(
                        $uri
                    )
                )
            )
        );

        if (!$this->isPath($path))
        {
            return null;
        }

        return $path;
    }

    public function getMimeByPath(?string $path): ?string
    {
        if ($this->isPath($path))
        {
            if ($mime = mime_content_type($path))
            {
                return $mime;
            }
        }

        return null;
    }

    public function getDataByPath(?string $path): ?string
    {
        if ($this->isPath($path))
        {
            if ($data = file_get_contents($path))
            {
                return $data;
            }
        }

        return null;
    }

    public function isPath(?string $path): bool
    {
        if (in_array($path, $this->_list) && is_file($path) && is_readable($path))
        {
            return true;
        }

        return false;
    }

    private function _index(string $path, ?array $blacklist = ['.', '..', 'sidebar.txt', '__template.txt']): void
    {
        foreach ((array) scandir($path) as $file)
        {
            if (in_array($file, $blacklist))
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

                    if (!isset($this->_tree[$path]))
                    {
                        $this->_tree[$path] = [];
                    }

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
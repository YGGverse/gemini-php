<?php

declare(strict_types=1);

namespace Yggverse\Gemini\Dokuwiki;

class Helper
{
    private \Yggverse\Gemini\Dokuwiki\Filesystem $_filesystem;
    private \Yggverse\Gemini\Dokuwiki\Reader $_reader;

    public function __construct(
        \Yggverse\Gemini\Dokuwiki\Filesystem $filesystem,
        \Yggverse\Gemini\Dokuwiki\Reader $reader
    ) {
        $this->_filesystem = $filesystem;
        $this->_reader = $reader;
    }

    public function getChildrenSectionLinksByUri(?string $uri = ''): array
    {
        $sections = [];

        if ($directory = $this->_filesystem->getDirectoryPathByUri($uri))
        {
            foreach ((array) $this->_filesystem->getTree() as $path => $files)
            {
                if (str_starts_with($path, $directory) && $path != $directory)
                {
                    // Init link name
                    $h1 = null;

                    // Init this directory URI
                    $thisUri = $this->_filesystem->getDirectoryUriByPath(
                        $path
                    );

                    // Skip sections deeper this level
                    if (substr_count($thisUri, ':') > ($uri ? substr_count($uri, ':') + 1 : 0))
                    {
                        continue;
                    }

                    // Get section names
                    $segments = [];

                    foreach ((array) explode(':', $thisUri) as $segment)
                    {
                        $segments[] = $segment;

                        // Find section index if exists
                        if ($file = $this->_filesystem->getPagePathByUri(implode(':', $segments) . ':' . $segment))
                        {
                            $h1 = $this->_reader->getH1(
                                $this->_reader->toGemini(
                                    file_get_contents(
                                        $file
                                    )
                                )
                            );
                        }

                        // Find section page if exists
                        else if ($file = $this->_filesystem->getPagePathByUri(implode(':', $segments)))
                        {
                            $h1 = $this->_reader->getH1(
                                $this->_reader->toGemini(
                                    file_get_contents(
                                        $file
                                    )
                                )
                            );
                        }

                        // Reset title of undefined segment
                        else
                        {
                            $h1 = null;
                        }
                    }

                    // Register section link
                    $sections[] = sprintf(
                        '=> /%s %s',
                        $thisUri,
                        $h1
                    );
                }
            }
        }

        // Keep unique
        $sections = array_unique(
            $sections
        );

        // Sort asc
        sort(
            $sections
        );

        return $sections;
    }

    public function getChildrenPageLinksByUri(?string $uri = ''): array
    {
        $pages = [];

        if ($directory = $this->_filesystem->getDirectoryPathByUri($uri))
        {
            foreach ((array) $this->_filesystem->getPagePathsByPath($directory) as $file)
            {
                $pages[] = sprintf(
                    '=> /%s %s',
                    $this->_filesystem->getPageUriByPath(
                        $file
                    ),
                    $this->_reader->getH1(
                        $this->_reader->toGemini(
                            file_get_contents(
                                $file
                            )
                        )
                    )
                );
            }
        }

        // Keep unique
        $pages = array_unique(
            $pages
        );

        // Sort asc
        sort(
            $pages
        );

        return $pages;
    }
}
# gemini-php

PHP 8 Library for Gemini Protocol

## Usage

```
composer require yggverse/gemini:dev-main
```

## DokuWiki

Toolkit provides DokuWiki API for Gemini.

Allows to simple deploy new apps or make existing website mirror

### Examples

* [DokuWiki Server for Gemini Protocol](https://github.com/YGGverse/dokuwiki-gemini-server)

### Reader

Read DokuWiki and convert to Gemini

```
$reader = new \Yggverse\Gemini\Dokuwiki\Reader(
    // optional regex rule set array
);
```

#### Reader::getRules
#### Reader::setRules
#### Reader::getRule
#### Reader::setRule

Get or change existing regex rule (or just skip by using build-in set)

```
echo $reader->setRule(
    '/subject/ui',
    'replacement'
);
```

#### Reader::getMacroses
#### Reader::setMacroses
#### Reader::getMacros
#### Reader::setMacros

```
echo $reader->setMacros(
    '~my-macros-key~',
    '~my-macros-value~',
);
```

#### Reader::toGemini

Convert DokuWiki text to Gemini markup

As wiki has lot of inline links, to make converted document well-readable, this method does not replace links with new line `=>` macros, but uses inline context: `Name ( URL )`. This model useful with `Reader::getLinks` method, that for example appends all those related links to the document footer.

If you don't like this implementation, feel free to change it by `Reader::setRule` method!

```
echo $reader->toGemini(
    file_get_contents(
        '/host/data/pages/index.txt'
    )
);
```

#### Reader::getH1

Get document title

```
$gemini = $reader->toGemini(
    file_get_contents(
        '/host/data/pages/index.txt'
    )
);

echo $reader->getH1(
    $gemini
);
```

#### Reader::getLinks

Get document links

```
$gemini = $reader->toGemini(
    file_get_contents(
        '/host/data/pages/index.txt'
    )
);

echo $reader->getLinks(
    $gemini
);
```

### Filesystem

Provides methods for simple and secure interaction with DokuWiki file storage

```
$filesystem = new \Yggverse\Gemini\Dokuwiki\Filesystem(
    '/host/data' // storage location
);
```

#### Filesystem::getList

Return simple array of all files in storage

```
var_dump (
    $filesystem->getList(
        'hello:world'
    )
)
```

#### Filesystem::getTree

Return all files under the storage folder in tree format

```
var_dump (
    $filesystem->getTree(
        'hello:world'
    )
)
```

#### Filesystem::getPagePathsByPath

Return pages under the given data directory

```
var_dump (
    $filesystem->getPagePathsByPath(
        // absolute path to target data directory (e.g. Filesystem::getDirectoryPathByUri)
    )
)
```

#### Filesystem::getDirectoryPathByUri
#### Filesystem::getPagePathByUri

Return absolute path to stored page file

```
var_dump (
    $filesystem->getPagePathByUri(
        'hello:world'
    )
)
```

#### Filesystem::getDirectoryUriByPath
#### Filesystem::getPageUriByPath

Return page URI in `dokuwiki:format`

```
var_dump (
    $filesystem->getPageUriByPath(
        '/full/path/to/page.txt'
    )
)
```
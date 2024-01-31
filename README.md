# gemini-php

PHP 8 Library for Gemini Protocol

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

#### Reader::toGemini

Convert DokuWiki to Gemini markup

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
echo $reader->getH1(
    file_get_contents(
        '/host/data/pages/index.txt'
    )
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

#### Filesystem::getPagePathByUri

Return absolute path to stored page file

```
var_dump (
    $filesystem->getPagePathByUri(
        'hello:world'
    )
)
```

#### Filesystem::getPageUriByPath

Return page URI in `dokuwiki:format`

```
var_dump (
    $filesystem->getPageUriByPath(
        '/full/path/to/page.txt'
    )
)
```
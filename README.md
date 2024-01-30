# gemini-php

PHP 8 Library for Gemini Protocol

## DokuWiki

### Convert

```
$dokuwiki = new \Yggverse\Gemini\Dokuwiki();

echo $dokuwiki->toGemini(
    file_get_contents(
        'data/pages/index.txt'
    )
);
```
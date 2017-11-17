# resolve_uri

[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]

Resolves relative urls, like a browser would.

This function returns a new URI based on base path ([RFC3986][1] URL, URN, Windows path, relative path or file) and a new path.

## Install

### Via Composer

```bash
composer require peterpostmann/resolve_uri
```
If you dont want to use composer copy the `resolve_uri.php` file and from [peterpostmann\php-parse_uri][2] the `parse_uri.php` file and include it into your project.

## Usage

~~~PHP
use function peterpostmann\uri\resolve_uri;

string resolve_uri ( string basePath, string newPath) 

~~~

### Example

#### parse URIs

~~~PHP
use function peterpostmann\uri\resolve_uri;

echo resolve_uri('http://a/b/c/d;p?q#x',    'x')."\n";
echo resolve_uri('C:\path\file1.ext',       'file2.ext')."\n";
echo resolve_uri('file://smb/path/to/file', '/new/path/x.ext')."\n";
~~~


The above example will output:

```PHP
http://a/b/c/x
C:\path\file2.ext
file://smb/new/path/x.ext
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-travis]: https://travis-ci.org/peterpostmann/php-resolve_uri

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/peterpostmann/php-resolve_uri/master.svg?style=flat-square

[1]: https://tools.ietf.org/html/rfc3986/
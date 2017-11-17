<?php

use function peterpostmann\uri\resolve_uri;

class ResolveTest extends \PHPUnit\Framework\TestCase {
    
    const BASE_URI = 'http://a/b/c/d;p?q#x';

    /**
     * @dataProvider resolveProvider
     */
    function testResolve($base, $update, $expected) {

        $this->assertEquals(
            $expected,
            resolve_uri($base, $update)
        );

    }

    // based on https://github.com/thephpleague/uri-manipulations/blob/master/tests/UriModifierTest.php
    public function resolveProvider()
    {
        return [
            'base uri 1'              => [self::BASE_URI, '',              self::BASE_URI],
            'base uri 2'              => ['',             self::BASE_URI,  self::BASE_URI],
            'scheme 1'                => [self::BASE_URI, 'http://d/e/f',  'http://d/e/f'],
            'scheme 2'                => [self::BASE_URI, 'C:\d\e\f',      'C:\d\e\f'],
            'scheme 3'                => [self::BASE_URI, 'C:/d/e/f',      'C:\d\e\f'],
            'scheme 4'                => [self::BASE_URI, '\\\\d\e\f',     'file://d/e/f'],
            'scheme 5'                => [self::BASE_URI, '\\\\d/e/f',     'file://d/e/f'],
            'path 1'                  => [self::BASE_URI, 'g',             'http://a/b/c/g'],
            'path 2'                  => [self::BASE_URI, './g',           'http://a/b/c/g'],
            'path 3'                  => [self::BASE_URI, 'g/',            'http://a/b/c/g/'],
            'path 4'                  => [self::BASE_URI, '/g',            'http://a/g'],
            'authority'               => [self::BASE_URI, '//g',           'http://g'],
            'query'                   => [self::BASE_URI, '?y',            'http://a/b/c/d;p?y'],
            'path + query'            => [self::BASE_URI, 'g?y',           'http://a/b/c/g?y'],
            'fragment'                => [self::BASE_URI, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment'         => [self::BASE_URI, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [self::BASE_URI, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1'            => [self::BASE_URI, '.',             'http://a/b/c/'],
            'single dot 2'            => [self::BASE_URI, './',            'http://a/b/c/'],
            'single dot 3'            => [self::BASE_URI, './g/.',         'http://a/b/c/g/'],
            'single dot 4'            => [self::BASE_URI, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1'            => [self::BASE_URI, '..',            'http://a/b/'],
            'double dot 2'            => [self::BASE_URI, '../',           'http://a/b/'],
            'double dot 3'            => [self::BASE_URI, '../g',          'http://a/b/g'],
            'double dot 4'            => [self::BASE_URI, '../..',         'http://a/'],
            'double dot 5'            => [self::BASE_URI, '../../',        'http://a/'],
            'double dot 6'            => [self::BASE_URI, '../../g',       'http://a/g'],
            'double dot 7'            => [self::BASE_URI, '../../../g',    'http://a/g'],
            'double dot 8'            => [self::BASE_URI, '../../../../g', 'http://a/g'],
            'double dot 9'            => [self::BASE_URI, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes'        => [self::BASE_URI, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1'          => [self::BASE_URI, ';x',            'http://a/b/c/;x'],
            'complex path 2'          => [self::BASE_URI, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3'          => [self::BASE_URI, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4'          => [self::BASE_URI, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5'          => [self::BASE_URI, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [self::BASE_URI, '/./g',          'http://a/g'],
            'dot segments presence 2' => [self::BASE_URI, '/../g',         'http://a/g'],
            'dot segments presence 3' => [self::BASE_URI, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [self::BASE_URI, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [self::BASE_URI, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [self::BASE_URI, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',        'http://h:b@a/y'],
            '2 relative paths 1'      => ['a/b',          '../..',         '/'],
            '2 relative paths 2'      => ['a/b',          './.',           'a/'],
            '2 relative paths 3'      => ['a/b',          '../c',          'c'],
            '2 relative paths 4'      => ['a/b',          'c/..',          'a/'],
            '2 relative paths 5'      => ['a/b',          'c/.',           'a/c/'],
            '2 relative paths 6'      => ['a/b',          '../../c',       '/c'],
            'windows path 1'          => ['C:\a\b\c',     '..\d',          'C:\a\d'],
            'windows path 2'          => ['C:\a\b\c',     '../d',          'C:\a\d'],
            'windows path 3'          => ['C:/a/b/c',     '../d',          'C:\a\d'],
            'windows path 4'          => ['C:/a/b/c',     '../d',          'C:\a\d'],
            'windows path 5'          => ['C:\a',         '../..',         'C:\\'],
            'windows path 6'          => ['C:\a',         '../../d',       'C:\d'],
            'windows path 7'          => ['C:\a',         '?m',            'C:\a?m'],
            'windows path 8'          => ['C:\a',         '#x',            'C:\a?#x'],
            'smb path 1'              => ['\\\\a\b\c',    '..\d',          'file://a/d'],
            'smb path 2'              => ['\\\\a\b\c',    '../d',          'file://a/d'],
            'smb path 3'              => ['\\\\a/b/c',    '../d',          'file://a/d'],
            'smb path 4'              => ['\\\\a/b/c',    '../d',          'file://a/d'],
            'smb path 5'              => ['\\\\a\b',      '../..',         'file://a/'],
            'smb path 6'              => ['\\\\a\b',      '../../d',       'file://a/d'],
        ];
    }

}

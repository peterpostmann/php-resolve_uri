<?php

namespace peterpostmann\uri;

/**
 * Resolves relative urls, like a browser would.
 *
 * This function takes a basePath, which itself _may_ also be relative, and
 * then applies the relative path on top of it.
 *
 * @param string $basePath
 * @param string $newPath
 * @return string
 */
function resolve_uri($baseUri, $newUri)
{
    $parse = function ($uri) {

        $data = parse_uri($uri);
        $data += [
            'hasScheme'    => isset($data['scheme']) && !empty($data['scheme']),
            'hasHost'      => isset($data['host']),
            'hasPort'      => isset($data['port']),
            'hasUser'      => isset($data['user']),
            'hasPass'      => isset($data['pass']),
            'hasPath'      => isset($data['path']),
            'hasQuery'     => isset($data['query']),
            'hasFragment'  => isset($data['fragment']),
            'hasDocument'  => isset($data['_document']),
            'isWinUri'     => $data['_protocol'] === true,
            'isRelative'   => $data['_protocol'] === false,
        ];
        return $data;
    };

    $base  = $parse($baseUri);
    $delta = $parse($newUri);

    // If the new path has a scheme, it's absolute and we can just return that.
    if ($delta['hasScheme'] || $delta['isWinUri']) {
        return $delta['_uri'];
    }

    // Return base URI if delta URI is empty
    if (!$delta['_uri']) {
        return $base['_uri'];
    }

    // Convert Slashes
    $base['path']  = str_replace('\\', '/', $base['path']);
    $delta['path'] = str_replace('\\', '/', $delta['path']);

    $newParts = [
        'scheme'    => $base['scheme'],
        'host'      => $delta['host']  ? $delta['host'] : $base['host'],
        'port'      => $delta['host']  ? $delta['port'] : $base['port'],
        'user'      => $delta['host']  ? $delta['user'] : $base['user'],
        'pass'      => $delta['host']  ? $delta['pass'] : $base['pass'],
        'query'     => (!$delta['hasDocument'] && $delta['hasFragment']) ?  $base['query'] : $delta['query'],
        'fragment'  => $delta['fragment'],
    ];

    if ($delta['isRelative']) {
        $path = $base['path'];
        if (strpos((string)$path, '/') !== false) {
            // Remove last component from base path.
            $path = substr($path, 0, strrpos($path, '/'));
        }
        $path .= '/' . $delta['path'];
    } else {
        $path = $delta['hasPath'] ? $delta['path'] : ($base['path'] ?: '/');
    }

    // Removing .. and .
    $parts        = explode('/', $path);
    $newPathParts = [];
    $addLeadingSlash  = false;
    $addTrailingSlash = false;

    // Store and remove drive letter
    if ($base['isWinUri']) {
        $driveLetter = $parts[0];
        $parts[0] = '';
    }

    // Store and remove leading Slash
    if (empty($parts[0])) {
        $addLeadingSlash = true;
        array_shift($parts);
    }

    // Resolve URI
    for ($i = 0; $i < count($parts); $i++) {
        $addTrailingSlash = false;
        $singleDot = ($parts[$i] === '.');
        $doubleDot = ($parts[$i] === '..');

        if ($singleDot || $doubleDot) {
            $addTrailingSlash = true;

            if ($doubleDot) {
                if (count($newPathParts) == 0) {
                    $addLeadingSlash = true;
                }
                array_pop($newPathParts);
            }
            continue;
        }
    
        $newPathParts[] = $parts[$i];
    }

    if ($addLeadingSlash) {
        array_unshift($newPathParts, '');
    }
    if ($addTrailingSlash) {
        $newPathParts[] = '';
    }

    $newParts['path'] = implode('/', $newPathParts);

    // Add drive letter and convert slashes
    if ($base['isWinUri']) {
        $newParts['path'] = $driveLetter.$newParts['path'];
        $newParts['path'] = str_replace('/', '\\', $newParts['path']);

        // # is a valid windows file-/dirname
        if (isset($newParts['fragment']) && !isset($newParts['query'])) {
            $newParts['query'] = '';
        }
    }

    return build_uri($newParts);
}

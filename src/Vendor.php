<?php

namespace Libmf;

class Vendor
{
    public const VERSION = 'master-2';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'libmf.so',
            'checksum' => '5a22ec277a14ab8e3b8efacfec7fe57e5ac4192ea60e233d7e6db38db755a67e'
        ],
        'aarch64-linux' => [
            'file' => 'libmf.arm64.so',
            'checksum' => '223ef5d1213b883c8cb8623bf07bf45167cd48585a5f2b59618cea034c72ad61'
        ],
        'x86_64-darwin' => [
            'file' => 'libmf.dylib',
            'checksum' => '6e3451feeded62a2e761647aef7c2a0e7dbeeee83ce8d4ab06586f5820f7ebf9'
        ],
        'arm64-darwin' => [
            'file' => 'libmf.arm64.dylib',
            'checksum' => '063c1dc39a6fda12ea2616d518fa319b8ab58faa65b174f176861cf8f8eaae0d'
        ],
        'x64-windows' => [
            'file' => 'mf.dll',
            'checksum' => '8b0e53ab50ca3e2b365424652107db382dff47a26220c092b89729f9c3b8d7e7'
        ]
    ];

    public static function check($event = null)
    {
        $dest = self::defaultLib();
        if (file_exists($dest)) {
            echo "✔ LIBMF found\n";
            return;
        }

        $dir = self::libDir();
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        echo "Downloading LIBMF...\n";

        $file = self::libFile();
        $url = self::withVersion("https://github.com/ankane/ml-builds/releases/download/libmf-{{version}}/$file");
        $contents = file_get_contents($url);

        $checksum = hash('sha256', $contents);
        if ($checksum != self::platform('checksum')) {
            throw new Exception("Bad checksum: $checksum");
        }

        file_put_contents($dest, $contents);

        echo "✔ Success\n";
    }

    public static function defaultLib()
    {
        return self::libDir() . '/' . self::libFile();
    }

    private static function libDir()
    {
        return __DIR__ . '/../lib';
    }

    private static function libFile()
    {
        return self::platform('file');
    }

    private static function platform($key)
    {
        return self::PLATFORMS[self::platformKey()][$key];
    }

    private static function platformKey()
    {
        if (PHP_OS_FAMILY == 'Windows') {
            return 'x64-windows';
        } elseif (PHP_OS_FAMILY == 'Darwin') {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-darwin';
            } else {
                return 'arm64-darwin';
            }
        } else {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-linux';
            } else {
                return 'aarch64-linux';
            }
        }
    }

    private static function withVersion($str)
    {
        return str_replace('{{version}}', self::VERSION, $str);
    }
}

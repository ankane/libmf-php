<?php

namespace Libmf;

class Vendor
{
    public const VERSION = '3d5570a';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'libmf.so',
            'checksum' => '2197628cfff98ede7269edc191ec8b7ff6e04edd4d20088938637ddefa596f40'
        ],
        'aarch64-linux' => [
            'file' => 'libmf.arm64.so',
            'checksum' => '99d315522ebd118318dad42ffeda08683cbdbd76c5e609cf7a494f9155feca2f'
        ],
        'x86_64-darwin' => [
            'file' => 'libmf.dylib',
            'checksum' => 'a6ea218370dbb489119e8a561089beea860a05ae0c30e58cc26d5f980d6cb8a2'
        ],
        'arm64-darwin' => [
            'file' => 'libmf.arm64.dylib',
            'checksum' => 'fd88da76cb1b9cfdc02fc7dc14a61229195ae9fdf845c78ede7701bb72dfe4e2'
        ],
        'x64-windows' => [
            'file' => 'mf.dll',
            'checksum' => 'c65eec5ef25482780f8b8f429d55d58ebf494288f84ccb689a2e7e88346fdc40'
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

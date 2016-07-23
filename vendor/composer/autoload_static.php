<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd9072928aaaec2c0b04866d450355f5a
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Identicon\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Identicon\\' => 
        array (
            0 => __DIR__ . '/..' . '/yzalis/identicon/src/Identicon',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd9072928aaaec2c0b04866d450355f5a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd9072928aaaec2c0b04866d450355f5a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
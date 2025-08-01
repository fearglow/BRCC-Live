<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0314494c3e921696f5c9bcdde01b4ce0
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/src/Twilio',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0314494c3e921696f5c9bcdde01b4ce0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0314494c3e921696f5c9bcdde01b4ce0::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

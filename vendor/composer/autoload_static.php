<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4f47798a6b6d803a866089c477bce7cf
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\EventDispatcher\\' => 34,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\EventDispatcher\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/event-dispatcher',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Tmilos\\Value\\Tests\\' => 
            array (
                0 => __DIR__ . '/..' . '/tmilos/value/tests',
            ),
            'Tmilos\\Value\\' => 
            array (
                0 => __DIR__ . '/..' . '/tmilos/value/src',
            ),
            'Tmilos\\GoldParser\\' => 
            array (
                0 => __DIR__ . '/..' . '/tmilos/gold-parser/src',
            ),
            'Tests\\Tmilos\\GolderParser\\' => 
            array (
                0 => __DIR__ . '/..' . '/tmilos/gold-parser/tests',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4f47798a6b6d803a866089c477bce7cf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4f47798a6b6d803a866089c477bce7cf::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit4f47798a6b6d803a866089c477bce7cf::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
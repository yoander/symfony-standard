<?php
use Meliacuba\Component\ClassLoader\ApcClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = new ApcClassLoader();

$loader->registerNamespaces(array(
    'Acme'             => __DIR__.'/../src',
));

$loader->registerPrefixes(array(
    //'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => 'Twig',
));

//intl
if (!function_exists('intl_get_error_code')) {
    require_once
'Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->registerPrefixFallbacks(array
('Symfony/Component/Locale/Resources/stubs')); }


$loader->register();

AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});


// Swiftmailer needs a special autoloader to allow
// the lazy loading of the init file (which is expensive)
//require_once __DIR__.'/../vendor/swiftmailer/lib/classes/Swift.php';
//Swift::registerAutoload(__DIR__.'/../vendor/swiftmailer/lib/swift_init.php');


<?php
$root = dirname(__DIR__, 4);
require $root . '/vendor/autoload.php';
$loader = new Composer\Autoload\ClassLoader();
foreach (glob($root . '/platform/plugins/*/plugin.json') as $manifest) {
    $info = json_decode(file_get_contents($manifest), true);
    $loader->setPsr4($info['namespace'], dirname($manifest) . '/src');
}
$loader->register();

<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

foreach (glob(dirname(__DIR__) . '/resources/views/*.blade.php') as $view) {
    Blade::compileString(file_get_contents($view));
}

echo "Blade compile: PASS\n";

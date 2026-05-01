<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$shipmozo = app(SparroWave\Shipmozo\Shipmozo::class);
echo json_encode($shipmozo->getNdrAll(), JSON_PRETTY_PRINT);

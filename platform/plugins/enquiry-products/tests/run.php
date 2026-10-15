<?php
// Isolated integration checks: never connect to the application's real database.
foreach (['APP_ENV'=>'testing','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','CACHE_STORE'=>'array','SESSION_DRIVER'=>'array','MAIL_MAILER'=>'array'] as $key=>$value) {
    putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
require __DIR__.'/bootstrap.php';
require __DIR__.'/EnquiryProductsTest.php';
(new \SparroWave\EnquiryProducts\Tests\EnquiryProductsTest())->run();

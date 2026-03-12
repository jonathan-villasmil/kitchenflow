<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new \App\Livewire\Kitchen\KitchenDisplay();
$c->station = 'cold';
try {
    $ords = $c->getActiveOrdersProperty();
    echo 'Orders: ' . count($ords) . PHP_EOL;
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

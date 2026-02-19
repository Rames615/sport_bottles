<?php
require __DIR__ . '/../vendor/autoload.php';
$c = 'App\\Controller\\StripeController';
var_dump(class_exists($c));
if (class_exists($c)) {
    $r = new ReflectionClass($c);
    echo 'file: ' . $r->getFileName() . PHP_EOL;
    echo 'name: ' . $r->getName() . PHP_EOL;
}

#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Linkfactory\Tv2Ge\Console\Command\Tv2GeCommand;
use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new Tv2GeCommand());
$app->run();



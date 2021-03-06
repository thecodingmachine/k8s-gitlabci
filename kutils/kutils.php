#!/usr/bin/php
<?php

use Kutils\AddSecretCommand;
use Kutils\CreateSecretsCommand;
use Kutils\EditIngressHostCommand;
use Symfony\Component\Console\Application;

require_once 'vendor/autoload.php';

$app = new Application('kutils');
$app->add(new CreateSecretsCommand());
$app->add(new AddSecretCommand());
$app->add(new EditIngressHostCommand());

$app->run();

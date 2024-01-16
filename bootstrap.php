<?php
declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');

use Symfony\Component\Dotenv\Dotenv;

ini_set('display_errors', '1');

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

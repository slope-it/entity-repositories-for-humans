<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$dbParams = [
    'driver' => 'pdo_pgsql',
    'host' => $_ENV['DATABASE_HOST'],
    'user' => $_ENV['DATABASE_USER'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'dbname' => $_ENV['DATABASE_NAME'],
];

$config = ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/src/Entity'], true);
$connection = DriverManager::getConnection($dbParams, $config);
return new EntityManager($connection, $config);

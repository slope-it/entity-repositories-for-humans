<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'bootstrap.php';

$entityManager = require 'entity-manager-factory.php';

return ConsoleRunner::createHelperSet($entityManager);

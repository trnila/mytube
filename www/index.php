<?php
$container = include __DIR__ . '/../app/bootstrap.php';
$container->getByType('Nette\Application\Application')->run();
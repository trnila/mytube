<?php
$container = require __DIR__ . '/../app/bootstrap.php';

$container->database->query('TRUNCATE videoSearch');
$container->database->query('INSERT INTO videoSearch(id, title, description) SELECT id, title, description FROM videos');
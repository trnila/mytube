<?php
$container = require __DIR__ . '/../app/bootstrap.php';

$container->database->query('TRUNCATE videoSearch');
$container->database->query('INSERT INTO videoSearch(id, title, description, tags) SELECT id, title, description, (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = videos.id ORDER BY video_tags.position) FROM videos');
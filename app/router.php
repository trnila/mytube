<?php
use Nette\Application\Routers\Route;

$container->router[] = new Route('watch/<id>', 'Video:show');
$container->router[] = new Route('profile/<nickname>', 'Profile:show');

$container->router[] = new Route('<presenter>/<action>', 'Homepage:default');
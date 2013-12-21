<?php
use Nette\Application\Routers\Route;

$router = $container->getByType('Nette\Application\Routers\RouteList');

$router[] = new Route('watch/<id>', 'Video:show');
$router[] = new Route('profile/<nickname>', 'Profile:show');

$router[] = new Route('<presenter>/<action>', 'Homepage:default');
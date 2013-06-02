<?php
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

$container->router[] = $admin = new RouteList('Admin');
$admin[] = new Route('admin/<presenter>/<action>', 'Dashboard:default');



$container->router[] = new Nette\Application\Routers\Route('<presenter>/<action>', 'Homepage:default');
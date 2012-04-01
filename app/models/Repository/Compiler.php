<?php
namespace Model\Repository;
use Nette;

class Compiler extends Nette\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		foreach(Nette\Utils\Finder::find('*.php')->in(__DIR__) as $file) {
			$name = str_replace('.php', '', $file->getFileName());
			$class = 'Model\Repository\\' . $name;
			$reflection = Nette\Reflection\ClassType::from($class);

			if($reflection->parentClass->name == 'Model\Repository\Repository') {
				$builder->addDefinition($this->prefix(strtolower($name)))
					->setClass($class);
			}
		}
	}
}
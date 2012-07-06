<?php
use Nette\Utils\Html;
use Nette\Utils\PhpGenerator\Helpers;
use Nette\Utils\PhpGenerator\PhpLiteral;

class Assets extends Nette\Latte\Macros\MacroSet
{
	/** @var string */
	public $wwwDir;

	/** @var string */
	public $rootDir;

	/** @var boolean */
	public $productionMode;

	public static function installMacros(Nette\Latte\Compiler $compiler, $wwwDir, $rootDir, $productionMode, Nette\Http\Request $request)
	{
		$me = new static($compiler);
		$me->productionMode = $productionMode;
		$me->wwwDir = $wwwDir;
		$me->rootDir = $rootDir;

		$me->addMacro('stylesheet', callback($me, 'macroStylesheet'));
		$me->addMacro('javascript', callback($me, 'macroJavascript'));
	}

	public function macroStylesheet()
	{
		$path = $this->wwwDir . '/assets/stylesheets/application.css';
		$append = $this->productionMode ? '-' . md5_file($path) : '';

		return Helpers::format('echo "<link rel=\"stylesheet\" href=\"{$basePath}/assets/stylesheets/application' . $append . '.css\">";');

		/*
			//TODO: user-specific?
		 return 'echo "<link rel=\"stylesheet\" href=\"{$basePath}/assets/stylesheets/application"'
			. ' . (Nette\Diagnostics\Debugger::$productionMode ? "-' . $md5hash . '" : "")'
			. ' . ".css\">";';
		 */
	}

	public function macroJavascript()
	{
		$code = '';
		$compressed = $this->wwwDir . '/assets/javascripts/application.min.js';

		if($this->productionMode && file_exists($compressed)) {
			$code .= $this->formatScript($compressed);
		}
		else {
			foreach(file($this->wwwDir . '/assets/javascripts/resources') as $resource) {
				// If resource starts with / its appended by root path, else appended list's directory
				$path = $resource[0] === '/' ? $this->rootDir . $resource : $this->wwwDir . '/assets/javascripts/' . $resource;
				$code .= $this->formatScript(trim($path));
			}
		}

		return $code;
	}

	protected function formatScript($file)
	{
		$javascriptsDir = $this->wwwDir . '/assets/';
		$name = str_replace($javascriptsDir, '', $file);

		// If its not located in root
		if($name === $file) {
			$name = str_replace($this->rootDir, '', $name);
			$name = '@' . $name;
		}

		if($this->productionMode) {
			$parts = pathinfo($name);
			$hash = md5_file($file);

			$parts['dirname'] = $parts['dirname'] == '.' ? '' : $parts['dirname'] . '/';

			$name = "{$parts['dirname']}{$parts['filename']}-{$hash}.{$parts['extension']}";
		}

		return Helpers::format('echo "<script src=\"{$basePath}/assets/" . ? . "\"></script>";', $name) . "\n";
	}
}
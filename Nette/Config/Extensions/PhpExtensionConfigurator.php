<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config\Extensions;

use Nette,
	Nette\DI\ContainerBuilder;



/**
 * ...
 *
 * @author     David Grudl
 */
class PhpExtensionConfigurator extends Nette\Config\PackageConfigurator
{
	private $options = array();


	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		foreach ($config as $name => $value) {
			if (is_array($value)) { // back compatibility - flatten INI dots
				foreach ($value as $k => $v) {
					$this->options["$name.$k"] = $v;
				}
			} else {
				$this->options[$name] = $value;
			}
		}
	}



	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		foreach ($this->options as $name => $value) {
			if (!is_scalar($value)) {
				throw new Nette\InvalidStateException("Configuration value for directive '$name' is not scalar.");

			} elseif ($name === 'include_path') {
				$initialize->addBody('set_include_path(?);', array(str_replace(';', PATH_SEPARATOR, $value)));

			} elseif ($name === 'ignore_user_abort') {
				$initialize->addBody('ignore_user_abort(?);', array($value));

			} elseif ($name === 'max_execution_time') {
				$initialize->addBody('set_time_limit(?);', array($value));

			} elseif ($name === 'date.timezone') {
				$initialize->addBody('date_default_timezone_set(?);', array($value));

			} elseif (function_exists('ini_set')) {
				$initialize->addBody('ini_set(?, ?);', array($name, $value));

			} elseif (ini_get($name) != $value && !Nette\Framework::$iAmUsingBadHost) { // intentionally ==
				throw new Nette\NotSupportedException('Required function ini_set() is disabled.');
			}
		}

		// TODO
		/*
		foreach ($config as $name => $value) {
			$initialize->addBody('define(?, ?);', array($name, $value));
		}*/
	}

}

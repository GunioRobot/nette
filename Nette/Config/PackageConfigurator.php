<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config;

use Nette,
	Nette\DI\ContainerBuilder;



/**
 * ...
 *
 * @author     David Grudl
 */
interface IPackageConfigurator
{
	function loadConfiguration(ContainerBuilder $container, array $config);

	function beforeCompile(ContainerBuilder $container);

	function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class);
}



/**
 * ...
 *
 * @author     David Grudl
 */
abstract class PackageConfigurator extends Nette\Object implements IPackageConfigurator
{

	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
	}

	public function beforeCompile(ContainerBuilder $container)
	{
	}

	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
	}

}

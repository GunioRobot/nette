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
	Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;



/**
 * ...
 *
 * @author     David Grudl
 */
class FrameworkConfigurator extends Nette\Config\PackageConfigurator
{

	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		// cache
		$container->addDefinition('cacheJournal')
			->setClass('Nette\Caching\Storages\FileJournal', array('%tempDir%'));

		$container->addDefinition('cacheStorage')
			->setClass('Nette\Caching\Storages\FileStorage', array('%tempDir%/cache'));

		$container->addDefinition('templateCacheStorage')
			->setClass('Nette\Caching\Storages\PhpFileStorage', array('%tempDir%/cache'))
			->setAutowired(FALSE);

		// http
		$container->addDefinition('httpRequestFactory')
			->setClass('Nette\Http\RequestFactory')
			->addSetup('setEncoding', array('UTF-8'));

		$container->addDefinition('httpRequest')
			->setClass('Nette\Http\Request')
			->setFactory('@httpRequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse')
			->setClass('Nette\Http\Response');

		$container->addDefinition('httpContext')
			->setClass('Nette\Http\Context');

		$session = $container->addDefinition('session')
			->setClass('Nette\Http\Session');

		if (isset($config['session'])) {
			Validators::assertField($config, 'session', 'array');
			$session->addSetup('setOptions', array($config['session']));
		}
		if (isset($config['session']['expiration'])) {
			$session->addSetup('setExpiration', array($config['session']['expiration']));
		}

		$container->addDefinition('user')
			->setClass('Nette\Http\User');

		// application
		$application = $container->addDefinition('application')
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', '%productionMode%');

		if (empty($config['productionMode'])) {
			$application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initialize'); // enable routing debugger
		}

		$container->addDefinition('router')
			->setClass('Nette\Application\Routers\RouteList');

		$container->addDefinition('presenterFactory')
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));

		// mailer
		if (empty($config['mailer']['smtp'])) {
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SendmailMailer');
		} else {
			Validators::assertField($config, 'mailer', 'array');
			$container->addDefinition('mailer')
				->setClass('Nette\Mail\SmtpMailer', array($config['mailer']));
		}
	}



	private function checkTempDir($dir)
	{
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists

		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		umask(0000);
		if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// tests subdirectory mode
		$useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		@unlink("$dir/$uniq/_");
		@rmdir("$dir/$uniq"); // @ - directory may not already exist

		return 'Nette\Caching\Storages\FileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
	}



	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
		if (isset($container->parameters['tempDir'])) {
			$class->methods['initialize']->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));
		}
	}

}

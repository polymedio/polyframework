<?php

/**
 * PolyFramework(tm): Minimalist Lightweight PHP Framework (http://polyframework.org)
 * Copyright (c) 2009-2013, Polymedio Networks S.L. (http://www.polymedio.com)
 * All rights reserved.
 *
 * LICENSE
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/BSD-3-Clause
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@polymedio.com so we can send you a copy immediately.
 *
 */

/**
 * Inicialización del framework
 *
 * Define las constantes y carga las clases necesarias para inicializar el framework
 */

/**
 * Alias del separador de directorios
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Ruta del directorio lib
 */
define('LIB', dirname(__FILE__) . '/');

if (!defined('APP')) {
	/**
	 * Ruta del directorio app
	 */
	define('APP', dirname(dirname(__FILE__)).'/app/');
}

if (!defined('WEBROOT')) {
	/**
	 * Ruta del directorio webroot
	 */
	define('WEBROOT', dirname($_SERVER['SCRIPT_FILENAME']).'/');
}

if (!defined('VIEWS')) {
	/**
	 * Ruta del directorio views
	 */
	define('VIEWS', APP.'views/');
}

if (!defined('CONFIG')) {
	/**
	 * Ruta del directorio config
	 */
	define('CONFIG', APP.'config/');
}

if (!defined('HOST')) {
	/**
	 * Host donde esta alojado el script
	 */
	define('HOST', (empty($_SERVER['HTTPS'])?'http://':'https://') . $_SERVER['HTTP_HOST']);
}

if (!defined('BASE')) {
	/**
	 * Ruta del raiz de las URLs de la aplicacion
	 */
	define('BASE', rtrim(strpos($_SERVER['PHP_SELF'], 'webroot') === false?dirname($_SERVER['PHP_SELF']):dirname(dirname($_SERVER['PHP_SELF'])), './'));
}

/**
 * URL completa del raiz de la aplicacion
 */
define('FULL_BASE', HOST . BASE);

/**
 * Ruta del request actual
 */
define('HERE', $_SERVER['REQUEST_URI']);

/**
 * URL completa del request actual
 */
define('FULL_HERE', HOST . $_SERVER['REQUEST_URI']);

require LIB . 'basics.php';
if (php_sapi_name() != 'cli') {
	if(get_magic_quotes_gpc()) {
		$_POST    = fix_slashes($_POST);
		$_GET     = fix_slashes($_GET);
		$_REQUEST = fix_slashes($_REQUEST);
		$_COOKIE  = fix_slashes($_COOKIE);
	}
}

require LIB . 'Poly.php';
require LIB . 'Poly/Config.php';
require LIB . 'Poly/Cache.php';
require LIB . 'Poly/Router.php';
require LIB . 'Poly/Controller.php';

spl_autoload_register('Poly::autoload');
require APP.'config/bootstrap.php';
require APP.'config/routes.php';

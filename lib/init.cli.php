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

ini_set('error_reporting', E_ALL);

define('DS', DIRECTORY_SEPARATOR);
define('LIB', dirname(__FILE__) . '/');

if (!defined('APP')) {
	define('APP', dirname(dirname(__FILE__)).'/app/');
}

if (!defined('WEBROOT')) {
	define('WEBROOT', dirname(dirname(__FILE__)).'/webroot/');
}

if (!defined('VIEWS')) {
	define('VIEWS', APP.'views/');
}

if (!defined('CONFIG')) {
	define('CONFIG', APP.'config/');
}

if (!defined('BASE')) {
	define('BASE', null);
}

require_once ('Poly.php');
spl_autoload_register('Poly::autoload');
require LIB . 'basics.php';
require APP . 'config/bootstrap.php';

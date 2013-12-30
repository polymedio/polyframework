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

class Poly {

	/**
	 * Rutas en las que buscar una clase
	 */
	public static $paths = array(APP, LIB);

	/**
	 * Mapa de clases para cargar directamente
	 */
	public static $classMap = array();

	/**
	 * Realiza el dispatching de un request
	 *
	 * @param $url String ruta de la url
	 * @param $return Boolean si es true devuelve la salida
	 * @return String salida del request
	 */
	static function request($url = null, $return = false) {

		if (is_null($url)) {
			list($tmp_url) = explode('?', $_SERVER['REQUEST_URI'], 2);
			$url = substr(parse_url($tmp_url, PHP_URL_PATH), strlen(BASE));
		}

		$result = Poly_Router::match($url);
		$error = false;

		if ($result === false) {
			Poly_Router::handleError($url, 'no-route', $url, 'No matching route for '.htmlentities($url));
		}

		$result['url'] = $url;

		$action = $result['named']['action'];
		$controller = $result['named']['controller'];
		$package = $result['named']['package'];

		if (empty($action) || $action{0} == '_') {
			Poly_Router::handleError($url, 'invalid-action', $action, "Invalid action '$action'");
		}

		$class = 'Controller_'.ucfirst($controller);
		if (!empty($package)) {
			$class = ucfirst($package)."_$class";
		}

		$file = APP.str_replace('_', '/', $class) . '.php';
		if (!file_exists($file)) {
			Poly_Router::handleError($url, 'missing-file', $file, "Missing controller file '$file'");
		}

		require_once ($file);

		if (!class_exists($class)) {
			Poly_Router::handleError($url, 'missing-class', $class, "Missing controller class '$class'");
		}

		$controllerMethods = get_class_methods('Poly_Controller');
		$controllerActions = get_class_methods($class);
		$validActions = array_diff($controllerActions, $controllerMethods);

		if (!in_array($action, $validActions)) {
			Poly_Router::handleError($url, 'invalid-action', "$class::$action", "Invalid action '$action' in '$class'");
		}

		return self::dispatch($class, $action, $result, $return);
	}

	static function dispatch($class, $action, $params, $return = false) {
		$args = $params['params'];

		$Controller = new $class;
		$Controller->params = $params;
		$Controller->action = $action;
		$Controller->beforeFilter();
		$out = null;
		switch (count($args)) {
			case 0:
				$out = $Controller->$action();
				break;
			case 1:
				$out = $Controller->$action($args[0]);
				break;
			case 2:
				$out = $Controller->$action($args[0], $args[1]);
				break;
			case 3:
				$out = $Controller->$action($args[0], $args[1], $args[2]);
				break;
			case 4:
				$out = $Controller->$action($args[0], $args[1], $args[2], $args[3]);
				break;
			default:
				$out = call_user_func_array(array(&$Controller, $action), $args);
		}
		$Controller->afterFilter();

		if ($return) {
			return $out !== null ? $out : $Controller->output;
		}

		echo $Controller->output;
		echo $out;
	}

	/**
	 * Carga una clase
	 * @param String $class nombre de la clase
	 * @return Boolean true si encontró la clase
	 */
	static function autoload($class) {
		if (isset(self::$classMap[$class])) {
			require_once self::$classMap[$class];
			return true;
		}

		$file = str_replace('_', '/', $class) . '.php';

		foreach (self::$paths as $path) {
			$full = $path . $file;
			if (file_exists($full)) {
				require_once ($full);
				return true;
			}
		}
		return false;
	}

	/**
	 * Alias de Poly::autoload()
	 * @param String $class nombre de la clase
	 * @return Boolean true si encontró la clase
	 */
	static function loadClass($class) {
		if (!class_exists($class)) {
			return self::autoload($class);
		}
		return false;
	}

	/**
	 * Realiza una redirección HTTP
	 * @param String $url URL destino de la redirección
	 * @param Boolean $exit si es true termina ejecución de la aplicación
	 */
	static function redirect($url = null, $exit = true) {
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: ' . full_url($url));
		if ($exit) {
			exit();
		}
	}

	/**
	 * Envía un error HTTP
	 * @param Integer $error código del error HTTP
	 * @param String $data cuerpo del error HTTP
	 */
	static function error($error = null, $data = null) {
		if ($error && file_exists(APP."errors/$error.php")) {
			include(APP."errors/$error.php");
		}

		$code  = is_null($error)?404:$error;
		$codes = array(
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out'
		);

		if (isset($codes[$code])) {
			list($code, $msg) = array($code, $codes[$code]);
			header("HTTP/1.1 $code $msg");
			header("Status: $code $msg");
			die($data);
		}

		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		die();
	}
}

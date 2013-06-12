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

class Poly_Router {

	/**
	 * Extensiones
	 */
	public static $extensions = array('html');

	/**
	 * Rutas definidas
	 */
	public static $routes = array();

	/**
	 * Ruta por defecto ('/')
	 */
	public static $defaultRoute = array();

	/**
	 * Conecta una ruta
	 *
	 * @param String $rule         Regla para decidir aplicar la ruta
	 * @param Array  $defaults     Valores predeterminados de la ruta
	 * @param Array  $requeriments Restricciones para la ruta
	 */
	public static function connect($rule, $defaults = array(), $requeriments = array()) {
		$params = array();
		foreach ($defaults as $k => $v) {
			if (is_numeric($k)) {
				$params[$k] = $v;
			}
		}
		$defaults = array_merge(array('action' => 'index', 'package' => null), $defaults);
		$route = array('defaults' => $defaults, 'regexps' => array(), 'names' => array(), 'params' => $params, 'rule' => $rule);

		if ($rule == '/') {
			self::$defaultRoute = array('named' => $defaults, 'params' => array(), 'rule' => '/');
			return;
		}

		$segments = explode('/', trim($rule, '/'));
		foreach ($segments as $i => $segment) {
			if (empty($segment)) {
				continue;
			}
			if ($segment{0} != ':') {
				$route['regexps'][$i] = "/^$segment$/i";
				continue;
			}
			$name = substr($segment, 1);
			if (isset($requeriments[$name])) {
				$route['regexps'][$i] = $requeriments[$name];
			}
			$route['names'][$i] = $name;
		}

		self::$routes[] = $route;
	}

	/**
	 * Busca en las rutas una que corresponda a $url
	 *
	 * @param String $url la URL a rutear
	 * @return Array      la primer ruta que coincide, o false
	 */
	public static function match($url) {
		if ($url == '/') {
			if (!empty(self::$defaultRoute)) {
				return self::$defaultRoute;
			}
		}

		$url = trim(trim($url, '/'));
		$extension = pathinfo($url, PATHINFO_EXTENSION);
		if (!empty($extension) && in_array($extension, self::$extensions)) {
			$url = substr($url, 0, -(strlen($extension) + 1));
		}

		$segments = explode('/', $url);
		foreach (self::$routes as $route) {
			$result = self::check($segments, $route);
			if ($result !== false) {
				$result['extension'] = $extension;
				return $result;
			}
		}
		return false;
	}

	/**
	 * Comprueba si un array de segmentos coincide con la ruta
	 *
	 * @param Array $segments  los segmentos de la url
	 * @param Array $route     la ruta a comprobar
	 * @return Array           la ruta si coincide, o false
	 */
	public static function check($segments, $route) {
		$result = array('named' => $route['defaults'], 'params' => $route['params'], 'rule' => $route['rule']);

		foreach ($route['regexps'] as $i => $regexp) {
			if (!isset($segments[$i])) {
				return false;
			}
			if (!preg_match($regexp, $segments[$i])) {
				return false;
			}
		}

		foreach ($segments as $i => $segment) {
			$name = isset($route['names'][$i])?$route['names'][$i]:null;
			if (isset($route['regexps'][$i])) {
				if (!preg_match($route['regexps'][$i], $segment)) {
					return false;
				}
				if ($name) {
					$result['named'][$name] = $segment;
				}
				continue;
			}
			if ($name) {
				$result['named'][$name] = $segment;
			} else {
				$result['params'][] = $segment;
			}
		}
		return $result;
	}

}


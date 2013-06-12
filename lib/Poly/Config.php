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
 * Clase para el manejo de la configuracion
 */
class Poly_Config {

	/**
	 * Configuración en tiempo de ejecución
	 */
	static $config = array();

	/**
	 * Archivos de configuracion ya cargados
	 */
	static $loaded = array();

	/**
	 * Carga un archivo de configuracion desde APP/config/<name>.php
	 *
	 * @param String  $name   nombre
	 * @param Boolean $reload si es true, recarga el archivo de configuración
	 */
	static function load($name, $reload = false) {
		if (isset(self::$loaded[$name]) && !$reload) {
			return true;
		}

		$name = strtolower($name);
		$config = array();
		$file = CONFIG."$name.php";
		if (!file_exists($file)) {
			return false;
		}
		include $file;

		self::$config = array_merge(self::$config, $config);
		self::$loaded[$name] = true;
		return true;
	}

	/**
	 * Guarda un archivo de configuracion en APP/config/<name>.php
	 *
	 * @param String  $name   nombre
	 * @param Array   $data   data para almacenar
	 * @param Boolean $reload si es true, recarga el archivo de configuración
	 */
	static function store($name, $data, $reload = false) {
		$content = "<?php\n";
		foreach ($data as $key => $value) {
			$content .= "\$config['$name']['$key'] = ". var_export($value, true) .";\n";
		}
		$result = file_put_contents(CONFIG.strtolower($name).'.php', $content);
		if ($reload) {
			self::load($name, true);
		}
		return $result;
	}

	/**
	 * Hace permanente una configuracion en un archivo de configuracion en APP/config/<name>.php
	 *
	 * @param String  $name   nombre
	 */
	static function save($name) {
		return self::store($name, self::read($name));
	}

	/**
	 * Lee de la configuracion
	 *
	 * @param String  $path    camino para leer la configuracion
	 * @param Mixed   $default valor a retornar si no existe el $path
	 */
	static function read($path = null, $default = false) {
		if ($path === null) {
			return self::$config;
		}

		$keys = explode('.', $path);
		$ref =& self::$config;

		foreach ($keys as $key) {
			if (!isset($ref[$key])) {
				return $default;
			}
			$ref =& $ref[$key];
		}
		return $ref;
	}

	/**
	 * Escribe en la configuracion
	 *
	 * @param String  $path   camino para leer la configuracion
	 * @param Mixed   $value  valor para guardar
	 */
	static function write($path, $value) {
		$keys = explode('.', $path);
		$ref =& self::$config;

		foreach ($keys as &$key) {
			if (!isset($ref[$key])) {
				$ref[$key] = array();
			}
			$ref =& $ref[$key];
		}
		return $ref = $value;
	}
}

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
 * Maneja el acceso al cache y los drivers
 */
class Poly_Cache {

	/**
	 * Instancias de drivers de cache configurados
	 */
	protected static $instances = array();

	/**
	 * Instancia del driver actual
	 */
	protected static $current = null;

	/**
	 * Obtiene una instancia de un driver de cache
	 *
	 * @param  String  $key    clave para la instancia del driver de cache
	 * @param  Array   $config configuracion de instancia del driver
	 * @return Object          Instance del driver de cache
	 */
	static function getInstance($key, $config = array()) {
		if (isset(self::$instances[$key])) {
			return self::$instances[$key];
		}
		$driver = isset($config['driver']) ? $config['driver'] : 'File';
		$class = "Poly_Cache_$driver";
		return self::$instances[$key] = new $class($config);
	}

	/**
	 * Inicializa el sistema de cache
	 *
	 * @param  String  $config clave para la configuración del driver de cache
	 */
	static function config($config = 'default') {
		if (is_string($config)) {
			$key = $config;
			$config = Poly_Config::read("Cache.$key");
			if (is_string($config)) {
				$key = $config;
				$config = Poly_Config::read("Cache.$key");
			}
		} else {
			$key = md5(serialize($config));
		}
		self::$current = self::getInstance($key, $config);
		return self::$current;
	}

	/**
	 * Lee del cache
	 * @param  String  $key         clave para leer del cache
	 * @return Mixed                Valor obtenido del cache
	 */
	static function read($key) {
		return self::$current->read($key);
	}

	/**
	 * Escribe en cache
	 * @param  String  $key         clave para escribir en el cache
	 * @param  Mixed   $data        datos a escribir en el cache
	 * @param  Integer $duration    duración en segundos del dato en cache
	 * @return Bool                 true si se pudo guardar
	 */
	static function write($key, $data, $duration = null) {
		return self::$current->write($key, $data, $duration);
	}

	/**
	 * Agrega una clave al cache si no existe
	 * @param  String  $key         clave para escribir en el cache
	 * @param  Mixed   $data        datos a escribir en el cache
	 * @param  Integer $duration    duración en segundos del dato en cache
	 * @return Bool                 true si se pudo guardar, false si la clave existe
	 */
	static function add($key, $data, $duration = null) {
		return self::$current->add($key, $data, $duration);
	}

	/**
	 * Elimina del cache
	 * @param  String  $key         clave para eliminar del cache
	 * @return Bool                 true si se pudo eliminar
	 */
	static function delete($key) {
		return self::$current->delete($key);
	}

	/**
	 * Escribe en cache
	 * @param  String  $key         clave para escribir en el cache
	 * @param  Mixed   $increment   valor del incremento
	 * @param  Integer $duration    duración en segundos del dato en cache
	 * @return Bool                 true si se pudo guardar
	 */
	static function increment($key, $increment = 1, $duration = null) {
		return self::$current->increment($key, $increment, $duration);
	}

	/**
	 * Alias de Poly_Cache::delete(), @see Poly_Cache::delete()
	 */
	static function del($key) {
		return self::$current->delete($key);
	}

	/**
	 * Devuelve la instancia actual del driver de cache
	 * @return Object Engine de cache en uso
	 */
	static function getEngine() {
		return self::$current;
	}

}

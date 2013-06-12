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

class Poly_DB extends PDO {

	/**
	 * Instancias de Poly_DB
	 */
	protected static $instances = array();

	/**
	 * Mapeo de tipos de datos SQL a PDO
	 */
	protected static $map = array(
		'int' => PDO::PARAM_INT,
		'varchar' => PDO::PARAM_STR,
		'char' => PDO::PARAM_STR,
		'text' => PDO::PARAM_STR,
		'date' => PDO::PARAM_STR,
		'datetime' => PDO::PARAM_STR,
		'boolean' =>  PDO::PARAM_BOOL,
		'blob' => PDO::PARAM_LOB,
	);

	/**
	 * Si es true, hace cache de las querys
	 */
	public $cacheQueries = false;

	/**
	 * Cache de las querys
	 */
	protected $_cache = array();


	/**
	 * Constructor
	 *
	 * @param  String $config Llave para leer la confgiguracion
	 */
	function __construct($config='default') {

		if (is_string($config)) {
			$config = Poly_Config::read("DB.$config");
		}

		extract($config);
		$params = array();
		if (isset($config['encoding'])) {
			$params[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES '.$config['encoding'];
		}
		if (!empty($config['persistent'])) {
			$params[PDO::ATTR_PERSISTENT] = true;
		}

		parent::__construct("$driver:host=$host;dbname=$dbname", $username, $password, $params);
	}

	/**
	 * Devuelve una instancia configurada
	 *
	 * @param  String $config Llave para leer la confgiguracion
	 * @return Poly_DB        Una instancia de Poly_DB configurada
	 */
	static function getInstance($config='default') {
		if (isset(self::$instances[$config])) {
			return self::$instances[$config];
		}

		$key = $config;
		if (is_array($config)) {
			$key = md5(serialize($config));
		}

		self::$instances[$key] = new Poly_DB($config);
		return self::$instances[$key];
	}

	/**
	 * Obtiene un descripcion de los campos de una tabla
	 *
	 * @param String $table el nombre de la tabla
	 * @return Array Un hash de la forma: nombre  =>  tipo PDO
	 */
	function describe($table) {
		$schema = array();
		$result = $this->queryAndFetch("DESCRIBE $table");
		$primary = null;
		foreach ($result as $field) {
			$name = $field['Field'];
			$type = strtolower($field['Type']);
			if ($type == 'tinyint(1)') {
				$type = 'boolean';
			}
			if (strstr($type, '(')) {
				$parts = explode('(', $type);
				$type = $parts[0];
			}
			$schema[$name] = isset(self::$map[$type])?self::$map[$type]:PDO::PARAM_STR;
		}
		return $schema;
	}

	/**
	 * Vacia el cache
	 */
	protected function _clearCache() {
		$this->_cache = array();
	}

	/**
	 * Genera una llave para el cache
	 */
	protected function _cacheKey($sql, $params = array()) {
		return md5($sql . serialize($params));
	}

	/**
	 * Escribe en el cache
	 */
	protected function _toCache($sql, $params = array(), $result) {
		if (!$this->cacheQueries) {
			return false;
		}
		if (stripos(trim($sql), 'SELECT') !== 0) {
			return false;
		}
		$key = $this->_cacheKey($sql, $params);
		$this->_cache[$key] = $result;
	}

	/**
	 * Lee del cache
	 */
	protected function _fromCache($sql, $params = array()) {
		$key = $this->_cacheKey($sql, $params);
		if ($this->cacheQueries && isset($this->_cache[$key])) {
			return $this->_cache[$key];
		}
		return false;
	}

	/**
	 * Ejecuta una consula en la base de datos y devuelve todos los registros
	 *
	 * @param String  $sql     Consulta SQL
	 * @param Aray    $params  Hash con los valores a reemplazar en la query
	 * @param Array   $sql     Indica como escapar los campos, se utiliza PDO::PARAM_STR por defecto
	 * @param String  $class   Si no es null se utiliza como nombre de clase y se devuelven objetos
	 * @return Array           Array multidemencional o array de objetos, depende del valor de $class
	 */
	function queryAndFetch($sql, $params = array(), $schema = array(), $class = null) {
		$Statement = $this->prepare($sql);
		if ($class) {
			$Statement->setFetchMode(PDO::FETCH_CLASS, $class);
		} else {
			$Statement->setFetchMode(PDO::FETCH_ASSOC);
		}
		foreach ((array)$params as $key => $value) {
			$Statement->bindValue(":$key", $value, isset($schema[$key])?$schema[$key]:PDO::PARAM_STR);
		}
		if ($Statement->execute()) {
			$result = $Statement->fetchAll();
			$this->_toCache($sql, $params, $result);
			return $result;
		}
		return false;
	}

	/**
	 * Ejecuta una consula en la base de datos y un escalar
	 *
	 * @param String  $sql     Consulta SQL
	 * @param Aray    $params  Hash con los valores a reemplazar en la query
	 * @param Array   $sql     Indica como escapar los campos, se utiliza PDO::PARAM_STR por defecto
	 * @return Array           Array multidemencional o array de objetos, depende del valor de $class
	 */
	function queryScalar($sql, $params = array(), $schema = array()) {
		$Statement = $this->prepare($sql);
		$Statement->setFetchMode(PDO::FETCH_ASSOC);

		foreach ((array)$params as $key => $value) {
			$Statement->bindValue(":$key", $value, isset($schema[$key])?$schema[$key]:PDO::PARAM_STR);
		}
		if ($Statement->execute()) {
			$result = $Statement->fetchAll();
			$this->_toCache($sql, $params, $result);
			return $result ? current(current($result)) : $result;
		}
		return false;
	}

}


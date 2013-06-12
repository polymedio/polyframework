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

if (!defined('TMP')) { define('TMP', APP.'/tmp/'); }
if (!defined('CACHE')) { define('CACHE', TMP.'/cache/'); }

/**
 * Backend de cache en archivos
 */
class Poly_Cache_File {

	/**
	 * Serializar datos al guardar
	 * @var Boolean
	 */
	public $serialize = true;

	/**
	 * Ruta donde se guardan los archivos
	 * @var String
	 */
	protected $path = CACHE;

	/**
	 * Tiempo de expiracion
	 * @var Integer
	 */
	protected $expires = null;

	/**
	 * Crea una instancia del Backend
	 *
	 * Claves válidas en $params:
	 *  - serialize boolean si es true serializa los datos al guardar
	 *  - path string ruta donde se guardan los archivos
	 *
	 * @param Array $params configuración del backend
	 */
	function __construct($params) {
		if (isset($params['serialize'])) {
			$this->serialize = $params['serialize'];
		}
		if (isset($params['path'])) {
			$this->path = $params['path'];
			if (!is_dir($this->path)) {
				mkdir($this->path, 0777, true);
			}
		}
	}

	/**
	 * Lee una clave del cache
	 * @param String $key clave a leer
	 * @return mixed el valor de la clave o false si no existe
	 */
	function read($key) {
		$this->expires = null;
		if (!file_exists($this->path.$key)) {
			return false;
		}
		$data = @file_get_contents($this->path.$key);
		if (empty($data)) {
			return false;
		}
		$data = unpack('I1expires/a*data', $data);
		if ($data['expires'] < time()) {
			self::delete($key);
			return false;
		}
		$this->expires = $data['expires'];
		if ($this->serialize) {
			return unserialize($data['data']);
		}
		return $data['data'];
	}

	/**
	 * Agrega una clave al cache si no existe
	 * @param String $key clave a agregar
	 * @param Mixed $data valor de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return boolean true si se guardo la clave false si la clave existia
	 */
	function add($key, $data, $duration = null) {
		if ($this->read($key) === false) {
			return $this->write($key, $data, $duration);
		}
		return false;
	}

	/**
	 * Escribe una clave al cache
	 * @param String $key clave a guardar
	 * @param Mixed $data valor de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return boolean true si se guardo la clave
	 */
	function write($key, $data, $duration = null) {
		if ($this->serialize) {
			$data = serialize($data);
		}
		if (is_null($duration)) {
			$duration = 86400 * 3650;
		}
		$expires = time() + $duration;
		return @file_put_contents($this->path.$key, pack('Ia*', $expires, $data));
	}

	/**
	 * Elimina una clave del cache
	 * @param String $key clave a eliminar
	 */
	function delete($key) {
		@unlink($this->path.$key);
	}

	/**
	 * Incrementa el valor de una clave
	 * @param String $key clave a guardar
	 * @param Integer $value incremento de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return valor de la clave o false si hubo error al guardar
	 */
	function increment($key, $value = 1, $duration = null) {
		$result = $this->read($key);
		$data = $result + $value;
		if ($result !== false) {
			$duration = $this->expires - time();
		}
		if ($this->write($key, $data, $duration)) {
			return $data;
		}
		return false;
	}

}

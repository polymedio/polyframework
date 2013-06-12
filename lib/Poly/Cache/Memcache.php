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
* Backend de cache en memcached
*/
class Poly_Cache_Memcache {

	/**
	 * Nombre de la clase de memcache
	 * @var String
	 */
	public $className = 'Memcache';

	/**
	 * Instancia de Memcahce
	 * @var Object
	 */
	protected $Memcache = null;

	/**
	 * Prefijo de las claves guardadas
	 * @var String
	 */
	public $prefix = '';

	/**
	 * Crea una instancia del Backend
	 *
	 * Claves válidas en $params:
	 *  - host string dirección IP del servidor
	 *  - port integer puerto del servidor
	 *  - port boolean realizar conexion persistente
	 *  - class string nombre de la clase memcache
	 *  - prefix string prefijo de las claves
	 *
	 * @param Array $params configuración del backend
	 */
	function __construct($params) {
		$default = array('host' => 'localhost', 'port' => 11211, 'persistent' => true);

		if (isset($params['class'])) {
			$this->className = $params['class'];
		}

		if (isset($params['prefix'])) {
			$this->prefix = $params['prefix'];
		}

		$this->Memcache = new $this->className;

		if ($this->className == 'Memcache') {
			if (isset($params['host'])) {
				$server = array_merge($default, $params);
				if ($server['persistent']) {
					$this->Memcache->pconnect($server['host'], $server['port']);
				} else {
					$this->Memcache->connect($server['host'], $server['port']);
				}
			}

			if (!empty($params['servers'])) {
				foreach($params['servers'] as $server) {
					$server = array_merge($default, $server);
					$this->Memcache->addServer($server['host'], $server['port'], $server['persistent']);
				}
			}
		} else {

			$servers = array();
			if (isset($params['host'])) {
				$server = array_merge($default, $params);
				array_unshift($servers, array($server['host'], $server['port']));
			}

			if (!empty($params['servers'])) {
				foreach($params['servers'] as $server) {
					$server = array_merge($default, $params);
					array_unshift($servers, array($server['host'], $server['port']));
				}
			}
			$this->Memcache->addServers($servers);
		}
	}

	/**
	 * Lee una clave del cache
	 * @param String $key clave a leer
	 * @return mixed el valor de la clave o false si no existe
	 */
	function read($key) {
		return $this->Memcache->get($this->prefix . $key);
	}

	/**
	 * Agrega una clave al cache si no existe
	 * @param String $key clave a agregar
	 * @param Mixed $data valor de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return boolean true si se guardo la clave false si la clave existia
	 */
	function add($key, $data, $duration = null) {
		if ($this->className == 'Memcached') {
			return $this->Memcache->add($this->prefix . $key, $data, $duration);
		}
		return $this->Memcache->add($this->prefix . $key, $data, 0, $duration);
	}

	/**
	 * Escribe una clave al cache
	 * @param String $key clave a guardar
	 * @param Mixed $data valor de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return boolean true si se guardo la clave
	 */
	function write($key, $data, $duration = null) {
		if ($this->className == 'Memcached') {
			return $this->Memcache->set($this->prefix . $key, $data, $duration);
		}
		return $this->Memcache->set($this->prefix . $key, $data, 0, $duration);
	}

	/**
	 * Elimina una clave del cache
	 * @param String $key clave a eliminar
	 */
	function delete($key) {
		return $this->Memcache->delete($this->prefix . $key, 0);
	}

	/**
	 * Incrementa el valor de una clave
	 * @param String $key clave a guardar
	 * @param Integer $value incremento de la clave
	 * @param Integer $duration tiempo de expiración de la clave
	 * @return valor de la clave o false si hubo error al guardar
	 */
	function increment($key, $value = 1, $duration = null) {
		$result = $this->Memcache->increment($this->prefix . $key, $value);
		if ($result === false) {
			if ($this->write($key, $value, $duration)) {
				return $value;
			}
			return false;
		}
		return $result;
	}

	/**
	 * Devuelve la instancia de Memcache
	 * @return Object
	 */
	function getMemcache() {
		return $this->Memcache;
	}

	function __call($function, $args) {
		switch (count($args)) {
			case 0:
				return $this->Memcache->$function();
				break;
			case 1:
				return $this->Memcache->$function($args[0]);
				break;
			case 2:
				return $this->Memcache->$function($args[0], $args[1]);
				break;
			case 3:
				return $this->Memcache->$function($args[0], $args[1], $args[2]);
				break;
			case 4:
				return $this->Memcache->$function($args[0], $args[1], $args[2], $args[4]);
				break;
			default:
				return call_user_func_array(array(&$this->Memcache, $function), $args);
		}
	}

}

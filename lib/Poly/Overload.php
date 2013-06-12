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
 * Clase base para la sobrecarga propieadades a travez de getters y setters
 */
class Poly_Overload {

	function __get($name) {
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		return null;
	}

	function __set($name, $value) {
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}
		return $this->$name = $value;
	}

}

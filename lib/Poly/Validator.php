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
 * Clase base para validadores
 */
class Poly_Validator {

	/**
	 * Errores encontrados durante la validacion
	 */
	public $validationErrors = array();

	/**
	 * Objeto validado
	 * @var Mixed
	 */
	public $Subject;

	/**
	 * Crea una instancia del validador
	 * @param Mixed $Subject objeto a validar
	 */
	function __construct($Subject = null) {
		$this->Subject = $Subject;
	}

	/**
	 * Validar
	 * @return Boolean true si es válido
	 */
	function validate() {
		return empty($this->validationErrors);
	}

	/**
	 * Invalidar un campo
	 * @param String $field campo a invalidar
	 * @param String $message mensaje de error
	 */
	function invalidate($field, $message) {
		$this->validationErrors[$field][] = $message;
	}

	/**
	 * Borrar los errores de validación
	 */
	function reset() {
		$this->validationErrors = array();
	}

	/**
	 * Alias de Poly_Validator::required()
	 * @see Poly_Validator::required()
	 */
	function _require($field, $message = 'Este campo es necesario.') {
		return $this->required($field, $message);
	}

	/**
	 * Requerir un campo
	 * @param String $field nombre del campo requerido
	 * @param String $message mensaje de error
	 * @return boolean true si el campo no esta vacío
	 */
	function required($field, $message = 'Este campo es necesario.') {
		if (!$this->$field) {
			$this->invalidate($field, $message);
			return false;
		}
		return true;
	}

	function __get($field) {
		if (is_array($this->Subject)) {
			return isset($this->Subject[$field])?$this->Subject[$field]:null;
		}
		return isset($this->Subject->$field)?$this->Subject->$field:null;
	}

	function __call($rule, $params) {
		$message = array_pop($params);
		$field = $params[0];
		$params[0] = $this->$field;
		$result = call_user_func_array(array('Poly_Validate', $rule), $params);
		if (!$result) {
			$this->invalidate($field, $message);
		}
		return $result;
	}

	/**
	 * Devuelve true si el objeto ó campo es válido
	 * @param String $field campo a consultar, si es null considera el objeto
	 * @return boolean true si el objeto ó campo es válido
	 */
	function isValid($field = null) {
		if ($field) {
			return !isset($this->validationErrors[$field]);
		}
		return empty($this->validationErrors);
	}

}

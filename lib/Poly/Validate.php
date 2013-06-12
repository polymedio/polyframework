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
 * Implementa validaciones como metodos estáticos
 */
class Poly_Validate {

	const YEAR =  '/^[12][0-9]{3}$/';
	const NUMBER = '/^[-+]?\\b[0-9]*\\.?[0-9]+\\b$/';
	const DIGITS = '/^[0-9]+$/';

	const NOT_EMPTY = '/[^\s]+/m';
	const ALPHA_NUMERIC = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
	const USERNAME = '/^[a-z][a-z0-9\-\.]+[a-z0-9]$/i';

	const EMAIL = "/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,4}|museum|travel)$/i";
	const IP = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
	const HOSTNAME = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';

	static $minPassLength = 6;
	static $maxPassLength = 15;
	static $minUserLength = 6;
	static $maxUserLength = 15;

	/**
	 * Comprueba la igualdad de dos valores
	 *
	 * @param  Mixed    $val1   Primer valor
	 * @param  Mixed    $val1   Segundo valor
	 * @param  Boolean   $exact  Compobar identidad
	 * @return Boolean           True si la condición se cumple
	 */
	static function equal($val1, $val2, $exact = false) {
		if ($exact) {
			return $val1 === $val2;
		}
		return $val1 == $val2;
	}

	/**
	 * Comprueba la igualdad de dos cadenas sin tener en cuenta mayusculas y minusculas
	 *
	 * @param  String    $val1   Primer valor
	 * @param  String    $val1   Segundo valor
	 * @return Boolean           True si la condición se cumple
	 */
	static function iequal($val1, $val2) {
		return strtolower($val1) == strtolower($val2);
	}

	/**
	 * Comprueba la longitud mínima
	 *
	 * @param  String    $value  Valor a comprobar
	 * @param  Integer   $min    Longitud mínima
	 * @return Boolean           True si la condición se cumple
	 */
	static function minLength($value, $min) {
		if (function_exists('mb_strlen'))
			return mb_strlen($value) >= $min;

		return strlen($value) >= $min;
	}

	/**
	 * Comprueba la longitud maxima
	 *
	 * @param  String    $value   Valor a comprobar
	 * @param  Integer   $max     Longitud maxima
	 * @return Boolean            True si la condición se cumple

	 */
	static function maxLength($value, $max) {
		if (function_exists('mb_strlen'))
			return mb_strlen($value) <= $max;

		return strlen($value) <= $max;
	}

	/**
	 * Comprueba la longitud
	 *
	 * @param  String    $value   Valor a comprobar
	 * @param  Integer   $max     Longitud
	 * @return Boolean            True si la condición se cumple

	 */
	static function length($value, $length) {
		if (function_exists('mb_strlen'))
			return mb_strlen($value) == $length;

		return strlen($value) == $length;
	}

	/**
	 * Comprueba que sea un nombre de usuario válido
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function username($value) {
		return preg_match(self::USERNAME, $value);
	}

	/**
	 * Comprueba que sea un número
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function number($value) {
		return preg_match(self::NUMBER, $value);
	}

	/**
	 * Comprueba que este formado sólo por dígitos
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function digits($value) {
		return preg_match(self::DIGITS, $value);
	}

	/**
	 * Comprueba que sea alfanumerico
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function alphaNumeric($value) {
		return preg_match(self::ALPHA_NUMERIC, $value);
	}

	/**
	 * Comprueba que sea una dirección email válido
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function email($value) {
		return preg_match(self::EMAIL, $value);
	}

	/**
	 * Comprueba que sea una URL válida
	 *
	 * @param  String    $value   Valor a comprobar
	 * @param  Boolean   $strict  Si es true, comprueba que sea un protocolo válido
	 * @return Boolean            True si la condición se cumple
	 */
	static function url($value, $strict = false) {
		$validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=') . '\/0-9a-z]|(%[0-9a-f]{2}))';
		$regexp =  '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . ($strict?'':'?').
			'(?:' . self::IP . '|' . self::HOSTNAME . ')(?::[1-9][0-9]{0,3})?' .
			'(?:\/?|\/' . $validChars . '*)?' .
			'(?:\?' . $validChars . '*)?' .
			'(?:#' . $validChars . '*)?$/i';

		return preg_match($regexp, $value);
	}

	/**
	 * Comprueba que sea una dirección IPv4 válida
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function ip($value) {
		return preg_match('/'.self::IP.'/', $value);
	}

	/**
	 * Comprueba que sea una nombre de host válido
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function hostname($value) {
		return preg_match('/'.self::HOSTNAME.'/', $value);
	}

	/**
	 * Comprueba el valor este en la lista
	 *
	 * @param  String    $value   Valor a comprobar
	 * @param  Array     $list    Lista de valores
	 * @return Boolean            True si la condición se cumple
	 */
	static function inList($value, $list) {
		return in_array($value, $list);
	}

	/**
	 * Comprueba que sea una fecha valida
	 *
	 * @param  String    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function date($value) {
		if (!preg_match('/^[0-9]{1,4}-[0-9]{1,2}-[0-9]{1,2}$/', $value)) {
			return false;
		}
		list($y,$m,$d) = explode('-', $value);
		return checkdate($m, $d, $y);
	}

	/**
	 * Comprueba que no este vacio
	 *
	 * @param  Mixed    $value   Valor a comprobar
	 * @return Boolean            True si la condición se cumple
	 */
	static function notEmpty($value) {
		return !empty($value);
	}

}



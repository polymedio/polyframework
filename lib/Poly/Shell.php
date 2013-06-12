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
 * Clase para usar en scripts shell
 */
class Poly_Shell {

	const MISSING_ARGS = 1;
	const METHOD_NOT_FOUND = 2;

	const HR = "----------------------------------------";

	/**
	 * script args
	 */
	var $args = null;

	function __construct() {
		$this->args = array_slice($_SERVER['argv'], 2);
	}

	/**
	 * Escribe en STDOUT
	 * @param String $text texto a escribir
	 * @param Array $args argumentos de reemplazo en $text
	 * @param Boolean $newLine si es true escribe una nueva linea
	 */
	static function out($text, $args = array(), $newLine = true) {
		self::put('php://stdout', $text, $args, $newLine);
	}

	/**
	 * Escribe en STDERR
	 * @param String $text texto a escribir
	 * @param Array $args argumentos de reemplazo en $text
	 * @param Boolean $newLine si es true escribe una nueva linea
	 */
		static function err($text, $args = array(), $newLine = true) {
		self::put('php://stderr', $text, $args, $newLine);
	}

	/**
	 * Escribe en un log
	 * @param String $text texto a escribir
	 * @param Array $args argumentos de reemplazo en $text
	 * @param Boolean $newLine si es true escribe una nueva linea
	 */
	static function log($text, $args = array(), $log = 'log/log.txt') {
		self::put($log, date('Y-m-d H:i:s'), array(), true, FILE_APPEND);
		self::put($log, $text, $args, true, FILE_APPEND);
		self::put($log, "-----------------------------------------------------------------------", array(), true, FILE_APPEND);
	}

	/**
	 * Escribe en un archivo
	 * @param String $file nombre del archivo
	 * @param String $text texto a escribir
	 * @param Array $args argumentos de reemplazo en $text
	 * @param Boolean $newLine si es true escribe una nueva linea
	 * @param String $flags flags para abrir el archivo
	 */
	static function put($file, $text, $args = array(), $newLine = true, $flags = null) {
		if (is_array($text) || is_object($text)) {
			$text = print_r($text, true);
		}
		if ($newLine) {
			$text = $text."\n";
		}

		if (!empty($args)) {
			$text = vsprintf($text, $args);
		}

		if (!is_null($flags)) {
			file_put_contents($file, $text, $flags);
			return;
		}

		file_put_contents($file, $text);
	}

}

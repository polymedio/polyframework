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
 * Si no existe la función mime_content_type() provee un reemplazo
 */
if (!function_exists('mime_content_type')) {
	function mime_content_type($f) {
		return trim(exec('file -bi ' . escapeshellarg($f)));
	}
}

/**
 * Genera un hash criptograficamente seguro
 *
 * @param String $string cadena para generar el hash
 * @param Boolean $salt si es true se le agregara SALT al string
 */
function secure_hash($string, $salt = true) {
	return sha1($string . ($salt ? SALT : null));
}

/**
 * Genera una url completa
 * @param String $url URL a completar
 * @return String URL completa
 */
function full_url($url) {
	if (stripos($url, 'http:') === 0 || stripos($url, 'https:') === 0) {
		return $url;
	}
	return FULL_BASE . $url ;
}

/**
 * Alias de print_r()
 * @param Mixed $what objeto a imprimir
 * @param Boolean $return si true devuelve el resultado
 */
function pr($what, $return = false) {

	if (php_sapi_name() == 'cli') {
		$out = print_r($what, true) . "\n";
	} else {
		$out = '<pre>'.h(print_r($what, true)).'</pre>';
	}
	
	if ($return) {
		return $out;
	}
	echo $out;
}

/**
 * Convierte camelCase en under_score
 * @param Stromg $camelCasedWord texto para convertir
 */
function underscore($camelCasedWord) {
	return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
}

/**
 * Convierte under_score en CamelCase
 * @param String $lowerCaseAndUnderscoredWord texto para convertir
 */
function camelize($lowerCaseAndUnderscoredWord) {
	return str_replace(" ", "", ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord)));
}

/**
 * Reemplaza los caracteres noalfanumericos en $string por $replacement
 * @param String $string texto a convertir
 * @param String $replacement reemplazo
 */
function slug($string, $replacement = '-') {
	$map = array(
		'/à|á|å|â/' => 'a',
		'/è|é|ê|ẽ|ë/' => 'e',
		'/ì|í|î/' => 'i',
		'/ò|ó|ô|ø/' => 'o',
		'/ù|ú|ů|û/' => 'u',
		'/ç/' => 'c',
		'/ñ/' => 'n',
		'/ä|æ/' => 'ae',
		'/ö/' => 'oe',
		'/ü/' => 'ue',
		'/Ä/' => 'Ae',
		'/Ü/' => 'Ue',
		'/Ö/' => 'Oe',
		'/ß/' => 'ss',
		'/[^\w\s]/' => ' ',
		'/\\s+/' => $replacement,
		sprintf('/^[%s]+|[%s]+$/', preg_quote($replacement, '/'), preg_quote($replacement, '/')) => '',
	);
	$string = preg_replace(array_keys($map), array_values($map), $string);
	return $string;
}

/**
 * Corrige el escapado automatico de magic_quotes
 * @param Mixed $values texto o array de textos a eliminar el escapado
 */
function fix_slashes($values) {
	if (is_array($values)) {
		$values = array_map('fix_slashes', $values);
	} else {
		$values = stripslashes($values);
	}
	return $values;
}

/**
 * Escapa los caracteres especiales en HTML
 * @param Mixed $text texto o array de textos a escapar
 */
function h($text) {
	if (is_array($text)) {
		return array_map('h', $text);
	}
	return htmlspecialchars($text, ENT_QUOTES, ENCODING);
}

/**
 * Escapa los caracteres especiales en Javascript
 * @param Mixed $text texto o array de textos a escapar
 */
function j($text) {
	if (is_array($text)) {
		return array_map('j', $text);
	}
	return substr(json_encode($text), 1, -1);
}






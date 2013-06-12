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
 * Helper de HTML
 */
class Poly_Helper_Html extends Poly_Helper {

	public static $tags = array(
		'script' => '<script%s>%s</script>',
		'link' => '<a%s>%s</a>',
		'image' => '<img%s />',
		'label' => '<label%s>%s</label>',
	);

	public static $scripts = array();

	/**
	 * Escapa los caracteres especiales en HTML
	 */
	static function escape($text) {
		if (is_array($text)) {
			return array_map(array(__CLASS__, 'escape'), $text);
		}
		return htmlspecialchars($text, ENT_QUOTES, ENCODING);
	}

	/**
	 * Formatea atributos HTML
	 */
	static function attributes($attributes) {
		$out = '';
		foreach ($attributes as $name => $value) {
			$out .= ' '. $name . '="' . self::escape($value) .'"';
		}
		return $out;
	}

	/**
	 * Crea un enlace HTML
	 */
	static function link($title, $url, $attributes = array(), $return = false) {
		$attributes['href'] = $url;
		$out = sprintf(self::$tags['link'], self::attributes($attributes), $title);
		if ($return) {
			return $out;
		}
		echo $out;
	}

	/**
	 * Crea una imagen en HTML
	 */
	static function image($url, $attributes = array(), $return = false) {
		if (!isset($attributes['alt'])) {
			$attributes['alt'] = '';
		}
		$attributes['src'] = stripos('http://', $url) === 0 || stripos('https://', $url) === 0 ? $url : BASE . '/' . ltrim($url, '/');
		$out = sprintf(self::$tags['image'], self::attributes($attributes));
		if ($return) return $out;
		echo $out;
	}

	static function img($url, $attributes = array()) {
		return self::image($url, $attributes, true);
	}

	/**
	 * Agrega un script al helper
	 */
	static function addScript() {
		$scripts = func_get_args();
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	/**
	 * Escribe los scripts del helper
	 */
	static function includeScripts($return = false) {
		$out = '';
		foreach (self::$scripts as $script) {
			$out .= self::script($script, true);
		}
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Formate un tag de script
	 */
	static function script($script, $return = false) {
		$url = stripos('http://', $script) === 0 || stripos('https://', $script) === 0 ? $script : BASE . '/js/' . $script;
		$attributes = array('src' => $url, 'type' => 'text/javascript');
		$out = sprintf(self::$tags['script'], self::attributes($attributes), '');
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Agrega scripts inline
	 */
	static function inlineScript() {
		$scripts = func_get_args();
		foreach ($scripts as $script) {
			self::script($script);
		}
	}

}

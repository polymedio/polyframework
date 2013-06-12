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
 * Clase para manejo de templates
 */
class Poly_Template {

	static public $noEscape = ':';
	static public $escape = 'h';

	/**
	 * Complia un template
	 * @param String $data template a compilar
	 */
	static function compile($data) {
		$lines = explode("\n", $data);
		$out = array();
		foreach($lines as $i => $line) {
			$newLine = $line;
			$match = array();
		
			$indent = '';
			$mIndent = array();
			if (preg_match('#^([\s]+)#', $line, $mIndent)) {
				$indent = $mIndent[1];
			}
		
			/* if */
			if (preg_match('#<!-- if (.*) -->#', $line, $match)) {
				$newLine =  $indent . sprintf('<? if (%s): ?>', self::make($match[1]));
	
			/* else */
			} elseif (preg_match('#<!-- else -->#', $line, $match)) {
				$newLine =  $indent . '<? else: ?>';

			/* switch */
			} elseif (preg_match('#<!-- switch (.*) -->#', $line, $match)) {
				$newLine =  $indent . sprintf("<? switch (%s): default: ?>", self::make($match[1]));

			/* case */
			} elseif (preg_match('#<!-- case (.*) -->#', $line, $match)) {
				$newLine =  $indent . sprintf('<? break; case %s: ?>', $match[1]);

			/* terminaciones de bloques */
			} elseif (preg_match('#<!-- (end.*) -->#', $line, $match)) {
				$newLine =  $indent . sprintf('<? %s ?>', $match[1]);
	
			/* foreach */
			} elseif (preg_match('#<!-- foreach (.*) as (.*) -->#', $line, $match)) {
				$sub = array();
				if (preg_match('#(.*) => (.*)#', $match[2], $sub)) {
					$match[2] = self::make($sub[1]) . ' => ' . self::make($sub[2]);
				} else {
					$match[2] = self::make($match[2]);
				}
	
				$newLine =  $indent . sprintf('<? foreach(%s as %s): ?>', self::make($match[1]), $match[2]);
	
			/* vars */
			} elseif (preg_match_all('#\{([^}]+)\}#', $line, $match, PREG_SET_ORDER)) {
				$search = array();
				$replace = array();

				$nlen = strlen(self::$noEscape);
				foreach($match as $set) {
					$search[] = $set[0];

					$escape = !empty(self::$escape);
					if (substr($set[1], 0, $nlen) == self::$noEscape) {
						$set[1] = substr($set[1], $nlen);
						$escape = false;
					}

					if ($escape) {
						$replace[] = sprintf('<?= %s(%s) ?>', self::$escape, self::make($set[1]));
					} else {
						$replace[] = sprintf('<?= %s ?>', self::make($set[1]));
					}
				}
	
				$newLine = str_replace($search, $replace, $line);
	
			}
	
			$out[] = $newLine;
		}
		return join("\n", $out);
	}

	/**
	 * Compila una referencia del template
	 * @param String $line referencia de template
	 */
	static function make($line) {
		$line = trim($line);

		$match = array();
		if (preg_match('#^(\!?[\w]+)\((.*)\)#', $line, $match)) {
			$args = array_map(array('self', 'make'), explode(',', $match[2]));
			return sprintf('%s(%s)', $match[1], join(', ', $args));
		}

		$line = preg_replace('#([\w]+)\.([\w]+)#', '$1->$2', $line);
		$line = preg_replace('#([\w]+)\[([\w]+)\]#', '$1[\'$2\']', $line);
		$line = preg_replace('#^([\w]+)#', '\$$1', $line);
		return $line;
	}

}

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
 * Clase base para los controladores
 */
class Poly_Controller {

	/**
	 * Título de pagina por defecto
	 */
	public $pageTitle;

	/**
	 * Path de las vistas
	 */
	public $viewPath;

	/**
	 * Variables pasadas a las vistas
	 */
	public $viewVars = array();

	/**
	 * Vista renderizada
	 */
	public $renderedView = null;

	/**
	 * Buffer de salida al navegador
	 */
	public $output = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$parts = explode('_', preg_replace('#Controller_#', '', get_class($this)));
		$this->viewPath = VIEWS . implode('/', $parts);
	}

	/**
	 * Callback ejecutado antes de cada acción
	 */
	public function beforeFilter() {
	}

	/**
	 * Callback ejecutado despues de cada acción
	 */
	public function afterFilter() {
	}

	/**
	 * Envia valoes a la vista
	 *
	 * @param Mixed  $key    Nombre de la variable en la vista o un array asociativo
	 * @param String $value  Valor de la variable
	 */
	public function set($key, $value = null) {
		if (is_array($key)) {
			$this->viewVars = array_merge($this->viewVars, $key);
		} else {
			$this->viewVars[$key] = $value;
		}
	}

	/**
	 * Renderiza una vista y coloca el resultado en $this->output
	 *
	 * @param  String $template Nombre de la vista a renderizar
	 * @param  String $layout   Nombre del layout a renderizar
	 * @return String           La vista renderizada
	 */
	public function render($template, $layout = 'default') {
		$__template__ = $this->viewPath .'/' . $template . '.php';
		if ($template[0] == '/') {
			$__template__ = VIEWS . ltrim($template, '/') . '.php';
		}

		$__layout__ = false;
		if ($layout) {
			$__layout__ = VIEWS . '/layouts/'. $layout . '.php';
		}

		extract($this->viewVars, EXTR_OVERWRITE);

		ob_start();
		include($__template__);
		$content_for_layout = ob_get_clean();
		$this->renderedView = $content_for_layout;

		if ($__layout__) {
			if (!isset($page_title)) {
				$page_title = $this->pageTitle;
			}
			ob_start();
			include($__layout__);
			$this->output .= ob_get_clean();
		} else {
			$this->output .= $content_for_layout;
		}
		return $this->output;
	}

	/**
	 * Redirige el request a otra URL
	 *
	 * @param String   $url     URL de destino
	 * @param Integer  $status  Código HTTP de respuesta
	 * @param Boolean  exit     Si es true termina la ejecución del script
	 */
	public function redirect($url, $status = null, $exit = true) {
		session_write_close();

		if (!empty($status)) {
			$codes = array(
				100 => 'Continue',
				101 => 'Switching Protocols',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Time-out',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Large',
				415 => 'Unsupported Media Type',
				416 => 'Requested range not satisfiable',
				417 => 'Expectation Failed',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Time-out'
			);

			list($code, $msg) = array($status, $codes[$status]);
			header("HTTP/1.1 $code $msg");
		}

		if ($url !== null) {
			header('Location: ' . full_url($url));
		}

		if ($exit) {
			exit();
		}
	}
}

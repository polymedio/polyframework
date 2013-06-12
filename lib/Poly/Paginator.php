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

class Poly_Paginator {

	protected $Model = null;
	public $total = 0;
	public $params = array();
	public $filterDuplicated = true;
	public $maxPage = 0;

	public $pageTitleFormat       = 'Página %d';
	public $linkAttributes        = array();
	public $attributes = array(
		'first' => array('class' => 'first'),
		'previous' => array('class' => 'previous'),
		'next' => array('class' => 'next'),
		'last' => array('class' => 'last'),
		'current' => array('class' => 'current'),
	);

	/**
	 * Crea una instancia del paginador
	 * @param Object $Model objeto a paginar
	 */
	function __construct($Model) {
		$this->Model = $Model;
		$url = substr(HERE, strlen(BASE));
		$here = @parse_url($url);

		$this->here = $here['path'];
		if (!empty($here['query'])) {
			parse_str($here['query'], $this->params);
		}
	}

	/**
	 * Obtiene una página de objetos
	 * @param   Integer  $page        Número de pagina a leer
	 * @param   Integer  $limit       Cantidad de registros por pagina
	 * @param   String   $order       Ordenamiento de los registros
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Array                 La página de registros encontrados
	 */
	function paginate($page = 1, $limit = 20, $order = null, $conditions = '1', $params = array(), $extra = array()) {
		$countExtra = $extra;
		unset($countExtra['fields']);
		unset($countExtra['order']);
		if (isset($extra['count'])) {
			$countExtra['fields'] = $extra['count'];
		}

		$this->total = $this->Model->findCount($conditions, $params, $countExtra);

		$this->page = $page > 0 ? $page : 1;
		$this->limit = $limit;
		$this->order = $order;
		$this->lastPage = ceil($this->total / $this->limit);
		if ($this->maxPage) {
			$this->lastPage = min($this->lastPage, $this->maxPage);
		}


		if ($order) {
			$extra['order'] = $order;
		}

		return $this->Model->paginate($page, $limit, $conditions, $params, $extra);
	}

	/**
	 * Genera link a la primer página
	 * @param String $title Texto para el enlace
	 * @param String $disabled Texto para enlace desactivado
	 * @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	 * @return String enlace HTML
	 */
	function first($title = 'Primero', $disabled = null, $attributes = null) {
		if (!$this->hasPrev()) {
			return is_null($disabled)?"<span class=\"first disabled\">$title</span>":$disabled;
		}

		if (is_null($attributes)) {
			$attributes = isset($this->attributes['first']) ? $this->attributes['first'] : $this->linkAttributes;
		}
		$url = $this->url(array('page' => 1));
		return Poly_Helper_Html::link($title, $url, $attributes, true);
	}

	/**
	 * Genera link a la página previa
	 * @param String $title Texto para el enlace
	 * @param String $disabled Texto para enlace desactivado
	 * @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	 * @return String enlace HTML
	 */
	function prev($title = 'Anterior', $disabled = null, $attributes = null) {
		if (!$this->hasPrev()) {
			return is_null($disabled)?"<span class=\"previous disabled\">$title</span>":$disabled;
		}

		if (is_null($attributes)) {
			$attributes = isset($this->attributes['previous']) ? $this->attributes['previous'] : $this->linkAttributes;
		}
		$url = $this->url(array('page' => $this->page - 1));
		return Poly_Helper_Html::link($title, $url, $attributes, true);
	}

	/**
	* Genera link a la página siguiente
	* @param String $title Texto para el enlace
	* @param String $disabled Texto para enlace desactivado
	* @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	* @return String enlace HTML
	*/
	function next($title = 'Siguiente', $disabled = null, $attributes = null) {
		if (!$this->hasNext()) {
			return is_null($disabled)?"<span class=\"next disabled\">$title</span>":$disabled;
		}

		if (is_null($attributes)) {
			$attributes = isset($this->attributes['next']) ? $this->attributes['next'] : $this->linkAttributes;
		}
		$url = $this->url(array('page' => $this->page + 1));
		return Poly_Helper_Html::link($title, $url, $attributes, true);
	}

	/**
	* Genera link a la última página
	* @param String $title Texto para el enlace
	* @param String $disabled Texto para enlace desactivado
	* @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	* @return String enlace HTML
	*/
	function last($title = 'Último', $disabled = null, $attributes = null) {
		if (!$this->hasNext()) {
			return is_null($disabled)?"<span class=\"last disabled\">$title</span>":$disabled;
		}
		if (is_null($attributes)) {
			$attributes = isset($this->attributes['last']) ? $this->attributes['last'] : $this->linkAttributes;
		}
		$url = $this->url(array('page' => $this->lastPage));
		return Poly_Helper_Html::link($title, $url, $attributes, true);
	}

	/**
	 * Devuelve true si existe la página anterior
	 * @return boolean true si existe
	 */
	function hasPrev() {
		return $this->page > 1;
	}

	/**
	 * Devuelve true si existe la página siguiente
	 * @return boolean true si existe
	 */
	function hasNext() {
		return $this->page < $this->lastPage;
	}

	/**
	 * Genera una URL con los parámetros especificados
	 * @param Array $params parámetros de la url
	 * @return String URL generada
	 */
	function url($params = array()) {
		if (!isset($params['page'])) {
			$params['page'] = $this->page;
		}

		$url = $this->here;
		$params = array_merge($this->params, $params);
		if ($params['page'] < 2) {
			unset($params['page']);
		}

		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		return $url;
	}

	/**
	 * Genera una lista de páginas
	 * @param Integer $numbers cantidad de páginas
	 * @param String $separator separador de los enlaces
	 * @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	 * @return string lista de enlaces HTML
	 */
	function numbers($numbers = 10, $separator = ' | ', $attributes = null) {
		$start = max($this->page - ceil($numbers / 2), 1);
		$end = min($start + $numbers, $this->lastPage);
		$start = max($end - $numbers, 1);

		$out = array();
		for ($i = $start ; $i <= $end; $i++) {
			$_attributes = is_null($attributes) ? $this->linkAttributes : $attributes;
			if ($this->page == $i) {
				$_attributes = isset($this->attributes['current']) ? $this->attributes['current'] : $_attributes;
			}

			if ($this->pageTitleFormat) {
				$_attributes['title'] = sprintf($this->pageTitleFormat, $i);
			}
			$url = $this->url(array('page' => $i));
			$out[] = Poly_Helper_Html::link($i, $url, $_attributes, true);
		}
		return implode($separator, $out);
	}

	/**
	 * Genera un link a ordenar por el campo especificado
	 * @param String $field campo para ordenar
	 * @param String $title texto del enlace
	 * @param Array $attributes atributos del enlace. @see Poly_Helper_Html::link()
	 * @return string enlace HTML
	 */
	function sort($field, $title = null, $attributes = null) {
		if (is_null($attributes)) {
			$attributes = isset($this->attributes['order']) ? $this->attributes['order'] : $this->linkAttributes;
		}
		if (is_null($title)) {
			$title = ucfirst($field);
		}
		$attributes['title'] = $title;

		$url = $this->url(array('order' => $field));
		return Poly_Helper_Html::link($title, $url, $attributes, true);
	}


}

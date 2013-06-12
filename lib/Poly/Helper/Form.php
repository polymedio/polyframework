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

class Poly_Helper_Form extends Poly_Helper {

	/**
	 * Tags html
	 */
	public static $tags = array(
		'input' => '<input%s />',
		'textarea' => '<textarea%s>%s</textarea>',
		'select' => '<select %s>%s</select>',
		'option' => '<option%s>%s</option>',
		'optgroup' => '<optgroup%s>%s</optgroup>',
		'button' => '<button%s>%s</button>',
		'formStart' => '<form%s>',
		'formEnd' => '</form>',
		'error' => '<div class="error-message">%s</div>',
	);

	/**
	 * Abre un formulario
	 *
	 * @param String  $action      URL para la acción del formulario
	 * @param String  $type        método del formulario: 'post', 'get', 'file'
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function create($action = null, $type = 'post', $attributes = array(), $return = false) {
		if ($type == 'file') {
			$type = 'post';
			$attributes['enctype'] = 'multipart/form-data';
		}
		$attributes['action'] = is_null($action) ? HERE : $action;
		$attributes['method'] = $type;

		$out = sprintf(self::$tags['formStart'], Poly_Helper_Html::attributes($attributes));
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Cierra un formulario
	 *
	 * @param Bool    $return      si es false, realiza un echo del resultado
	 */
	static function end($return = false) {
		$out = self::$tags['formEnd'];
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un boton
	 *
	 * @param String  $title       texto del boton
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function button($title, $name = null, $value = null, $attributes = array(), $return = false) {
		if (!is_null($name)) {
			$attributes['name'] = $name;
		}
		if (!is_null($value)) {
			$attributes['value'] = $value;
		}
		if (!isset($attributes['type'])) {
			$attributes['type'] = 'submit';
		}
		$out = sprintf(self::$tags['button'], Poly_Helper_Html::attributes($attributes), $title);
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un input
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function input($name, $value = null, $attributes = array(), $return = false) {
		$attributes['name'] = $name;
		if (!is_null($value)) {
			$attributes['value'] = $value;
		}
		if (!isset($attributes['type'])) {
			$attributes['type'] = 'text';
		}
		$out = sprintf(self::$tags['input'], Poly_Helper_Html::attributes($attributes));
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un input de tipo password
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function password($name, $value = null, $attributes = array(), $return = false) {
		$attributes['type'] = 'password';
		return self::input($name, $value, $attributes, $return);
	}

	/**
	 * Crea un input de tipo oculto
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function hidden($name, $value = null, $attributes = array(), $return = false) {
		$attributes['type'] = 'hidden';
		return self::input($name, $value, $attributes, $return);
	}

	/**
	 * Crea un checkbox
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Bool    $checked     estado del checkbox
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function checkbox($name, $value = 1, $checked = false, $attributes = array(), $return = false) {
		$attributes['type'] = 'checkbox';
		if ($checked) {
			$attributes['checked'] = 'checked';
		}
		return self::input($name, $value, $attributes, $return);
	}

	/**
	 * Crea un radio
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Bool    $checked     estado del checkbox
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function radio($name, $value = null, $checked = false, $attributes = array(), $return = false) {
		$attributes['type'] = 'radio';
		if ($checked) {
			$attributes['checked'] = 'checked';
		}
		return self::input($name, $value, $attributes, $return);
	}

	/**
	 * Crea un input para upload de archivos
	 *
	 * @param String  $name        nombre de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function file($name, $attributes = array(), $return = false) {
		$attributes['type'] = 'file';
		return self::input($name, null, $attributes, $return);
	}

	/**
	 * Crea un textarea
	 *
	 * @param String  $name        nombre de la variable
	 * @param String  $value       valor de la variable
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function textarea($name, $value = null, $attributes = array(), $return = false) {
		$attributes = array_merge(array('cols' => 40, 'rows' => 10), $attributes);
		$attributes['name'] = $name;

		$out = sprintf(self::$tags['textarea'], Poly_Helper_Html::attributes($attributes), Poly_Helper_Html::escape($value));
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un select
	 *
	 * @param String  $name        nombre de la variable
	 * @param Array   $options     opciones del select
	 * @param Mixed   $default     valor de la opcion preseleccionada
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function select($name, $options, $default = null, $attributes = array(), $return = false) {
		if (is_array($options)) {
			$empty = isset($attributes['empty'])?$attributes['empty']:null;
			$options = self::options($options, $default, $empty);
		}
		unset($attributes['empty']);
		$attributes['name'] = $name;

		$out = sprintf(self::$tags['select'], Poly_Helper_Html::attributes($attributes), $options);
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Genera options para los selects
	 *
	 * @param Array   $options     opciones del select
	 * @param Mixed   $default     valor de la opcion preseleccionada
	 * @param String  $empty       si no es null, se utiliza como opción nula
	 */
	static function options($options, $default = array(), $empty = null) {
		$out = '';
		if (!is_null($empty)) {
			$attributes = array('value' => null);
			if ($default === null) {
				$attributes['selected'] = 'selected';
			}
			$out .= sprintf(self::$tags['option'], Poly_Helper_Html::attributes($attributes), Poly_Helper_Html::escape($empty));
		}
		$default = (array) $default;
		foreach ($options as $value => $title) {

			if (is_array($title)) {
				$attributes = array('label' => $value);
				$out .= sprintf(self::$tags['optgroup'], Poly_Helper_Html::attributes($attributes), self::options($title, $default));
				continue;
			}

			$attributes = array('value' => $value);
			if (in_array($value, $default)) {
				$attributes['selected'] = 'selected';
			}
			$out .= sprintf(self::$tags['option'], Poly_Helper_Html::attributes($attributes), Poly_Helper_Html::escape($title));
		}
		return $out;
	}

	/**
	 * Crea selects para fechas
	 *
	 * @param String  $name        nombre de la variable
	 * @param Mixed   $default     valor de la opcion preseleccionada
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function date($name, $default = null, $attributes = array(), $return = false) {
		$out = '';

		$empty = false;
		if (isset($attributes['empty'])) {
			$empty = $attributes['empty'];
			unset($attributes['empty']);
		}

		$separator = ' ';
		if (isset($attributes['separator'])) {
			$separator = $attributes['separator'];
			unset($attributes['separator']);
		}

		list($year,$month,$day) = array(null, null, null);
		if (!empty($default)) {
			if (is_string($default)) {
				list($year,$month,$day) = explode('-', $default);
			} elseif (is_array($default)) {
				list($year,$month,$day) = array($default['y'], $default['m'], $default['d']);
			}
		}

		$minYear = 1900;
		if (isset($attributes['minYear'])) {
			$minYear = $attributes['minYear'];
			unset($attributes['minYear']);
		}
		$maxYear = date('Y');
		if (isset($attributes['maxYear'])) {
			$maxYear = $attributes['maxYear'];
			unset($attributes['maxYear']);
		}

		$days = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10,
		11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20,
		21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31);

		$months = array(1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
		7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre');

		$years = range($minYear, $maxYear);
		$years = array_combine($years, $years);

		if ($empty !== false) {
			$days = array(null => $empty) + $days;
			$months = array(null => $empty) + $months;
			$years = array(null => $empty) + $years; //ksort($years);
		}

		$attributes_d = $attributes;
		$attributes_m = $attributes;
		$attributes_y = $attributes;

		if (isset($attributes['id'])) {
			$attributes_d['id'] .= '_d';
			$attributes_m['id'] .= '_m';
			$attributes_y['id'] .= '_y';
		}

		$out .= self::select($name.'[d]', $days, $day, $attributes_d, true) . $separator;
		$out .= self::select($name.'[m]', $months, $month, $attributes_m, true) . $separator;
		$out .= self::select($name.'[y]', $years, $year, $attributes_y, true);

		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un grupo de checkboxes
	 *
	 * @param String  $name        nombre de la variable
	 * @param Array   $options     opciones
	 * @param Mixed   $default     valor de la opcion preseleccionada
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function checkboxes($name, $options, $default = array(), $attributes = array(), $return = false) {
		$name .= '[]';
		$out = '';

		$invert = false;
		if (isset($attributes['invert'])) {
			$invert = $attributes['invert'];
			unset($attributes['invert']);
		}

		foreach ($options as $value => $title) {
			$optionAttributes = $attributes;
			if (in_array($value, $default)) {
				$optionAttributes['checked'] = 'checked';
			}
			$optionAttributes['name'] = $name;
			$optionAttributes['value'] = $value;
			$optionAttributes['type'] = 'checkbox';

			$input = self::input($name, $value, $optionAttributes, true);

			if ($invert) {
				$out .= sprintf('<label>%s %s</label>', $input, $title);
			} else {
				$out .= sprintf('<label>%s %s</label>', $title, $input);
			}
		}
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Crea un grupo de radios
	 *
	 * @param String  $name        nombre de la variable
	 * @param Array   $options     opciones
	 * @param Mixed   $default     valor de la opcion preseleccionada
	 * @param Array   $attributes  attributos html extra
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function radios($name, $options, $default = null, $attributes = array(), $return = false) {

		$invert = false;
		if (isset($attributes['invert'])) {
			$invert = $attributes['invert'];
			unset($attributes['invert']);
		}

		$out = sprintf('<input type="hidden" name="%s">', $name);
		foreach ($options as $value => $title) {
			$optionAttributes = $attributes;
			if ($default == $value) {
				$optionAttributes['checked'] = 'checked';
			}
			$optionAttributes['name'] = $name;
			$optionAttributes['value'] = $value;
			$optionAttributes['type'] = 'radio';
			$input = self::input($name, $value, $optionAttributes, true);

			if ($invert) {
				$out .= sprintf('<label>%s %s</label>', $input, $title);
			} else {
				$out .= sprintf('<label>%s %s</label>', $title, $input);
			}


		}
		if ($return) return $out;
		echo $out;
	}

	/**
	 * Escribe los errores de un campo
	 *
	 * @param Array   $errors      errores
	 * @param String  $key         clave para buscar el error en el array de errorer
	 * @param Bool    $return      si es true retorna en lugar de hacer echo
	 */
	static function errors($errors, $key = null, $return = false) {
		$out = '';
		if (!is_null($key)) {
			if (empty($errors[$key])) {
				return;
			}
			$errors = $errors[$key];
		}

		foreach ($errors as $error) {
			$out .= sprintf(self::$tags['error'], Poly_Helper_Html::escape($error));
		}

		if ($return) return $out;
		echo $out;
	}

	/**
	 * Serializa un array como campos de un formulario
	 */
	static function serialize($data, $name = '') {
		$out = '';
		foreach ($data as $key => $value) {
			$current = $name ? h($name . '[' . $key . ']') : h($key);

			if (is_array($value)) {
				$out .= self::serialize($value, $current) . "\n";
			} else {
				$out .= self::hidden($current, $value, array(), true) . "\n";
			}
		}
		return $out;
	}

}

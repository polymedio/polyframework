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

class Poly_Upload_Image extends Poly_Upload {

	public $allowedMimeTypes = array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png');
	public $allowedSize = 2097152;

	public $minWidth = 0;
	public $minHeight = 0;

	public $maxWidth = 0;
	public $maxHeight = 0;

	/**
	 * Validar upload
	 *
	 * @return Boolean True si pasa la validación, false en otro caso
	 */
	function validate() {
		if (!parent::validate()) {
			return false;
		}

		if ($this->isEmpty()) {
			return true;
		}

		if (!$this->validateDimensions()) {
			return false;
		}

		return true;
	}

	/**
	 * Validar dimensiones de la imagen
	 * @return boolean True si pasa la validación, false en otro caso
	 */
	function validateDimensions() {
		list($width, $height) = $this->getDimensions();

		if ($this->minWidth && $width < $this->minWidth) {
			$this->validationError = sprintf('La imagen debe tener al menos %dpx de ancho.', $this->minWidth);
			return false;
		}

		if ($this->minHeight && $height < $this->minHeight) {
			$this->validationError = sprintf('La imagen debe tener al menos %dpx de alto.', $this->minHeight);
			return false;
		}

		if ($this->maxWidth && $width > $this->maxWidth) {
			$this->validationError = sprintf('La imagen debe tener menos de %dpx de ancho.', $this->maxWidth);
			return false;
		}

		if ($this->maxHeight && $height > $this->maxHeight) {
			$this->validationError = sprintf('La imagen debe tener menos de %dpx de alto.', $this->maxHeight);
			return false;
		}

		return true;
	}

	/**
	 * Obtiene las dimensiones de la imagen
	 * @return Array
	 */
	function getDimensions() {
		return getimagesize($this->data['tmp_name']);
	}

}


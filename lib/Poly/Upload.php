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
 * Clase para validar uploads
 */
class Poly_Upload {

	/**
	 * Tipos mimes permitidos
	 */
	public $allowedMimeTypes = array();

	/**
	 * Extensiones permitidas
	 */
	public $allowedExtensions = array();

	/**
	 * Permitir uploads vacios
	 */
	public $allowEmpty = true;

	/**
	 * Extensiones permitidas
	 */
	public $allowedSize = 0;

	/**
	 * Permisos del archivo
	 */
	public $fileMode = 0644;

	/**
	 * Permisos del directorio
	 */
	public $dirMode = 0755;

	/**
	 * Error de validación
	 */
	public $validationError = false;

	/**
	 * Constantes de tamaño de archivos
	 */
	const Kib = 1024;
	const MiB = 1048576;
	const GiB = 1073741824;

	/**
	 * Mensajes de error
	 */
	public $errorMessages = array(
		'uploadError' => 'Ningún archivo enviado.',
		'invalidSize' => 'Tamaño máximo de archivo superado.',
		'invalidMimeType' => 'Tipo de archivo no permitido.',
		'invalidExtension' => 'Extensión no permitida.',
	);

	/**
	 * Constructor
	 *
	 * @param Array $file El upload de un archivo
	 */
	function __construct($file) {
		$this->data = $file;
	}

	/**
	 * Validar upload
	 *
	 * @return Boolean True si pasa la validación, false en otro caso
	 */
	function validate() {
		$this->validationError = false;

		if ($this->allowEmpty && $this->isEmpty()) {
			return true;
		}
		if (!$this->validateUpload()) {
			$this->validationError = $this->errorMessages['uploadError'];
			return false;
		}
		if (!$this->validateSize()) {
			$this->validationError = $this->errorMessages['invalidSize'];
			return false;
		}
		if (!$this->validateMimeType()) {
			$this->validationError = $this->errorMessages['invalidMimeType'];
			return false;
		}
		if (!$this->validateExtension()) {
			$this->validationError = $this->errorMessages['invalidExtension'];
			return false;
		}
		return true;
	}

	/**
	 * Comprueba si el upload esta vacío (no se envio ningún archivo)
	 *
	 * @return Boolean True si esta vacío, false en otro caso
	 */
	function isEmpty() {
		return $this->data['error'] == UPLOAD_ERR_NO_FILE;
	}

	/**
	 * Comprueba que no hayan ocurrido errores en el upload
	 *
	 * @return Boolean True si es válido, false en otro caso
	 */
	function validateUpload() {
		if ($this->data['error'] == UPLOAD_ERR_OK) {
			return is_uploaded_file($this->data['tmp_name']);
		}
		return false;
	}

	/**
	 * Comprueba que no se exceda el tamaño de archivo permitido
	 *
	 * @return Boolean True si es válido, false en otro caso
	 */
	function validateSize() {
		if ($this->allowedSize) {
			return $this->data['size'] <= $this->allowedSize;
		}
		return true;
	}

	/**
	 * Comprueba que el tipo mime este en la lista de tipos mimes permitidos
	 *
	 * @return Boolean True si es válido, false en otro caso
	 */
	function validateMimeType() {
		if (empty($this->allowedMimeTypes)) {
			return true;
		}
		return in_array($this->getMimeType(), $this->allowedMimeTypes);
	}

	/**
	 * Comprueba que la extensión este en la lista de extensiones permitidas
	 *
	 * @return Boolean True si es válido, false en otro caso
	 */
	function validateExtension() {
		if (empty($this->allowedExtensions)) {
			return true;
		}
		return in_array($this->getExtension(), $this->allowedExtensions);
	}

	/**
	 * Devuelve el tipo mime del upload
	 *
	 * @return String El tipo mime
	 */
	function getMimeType() {
		if (function_exists('finfo_file')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mime = finfo_file($finfo, $this->data['tmp_name']);
			finfo_close($finfo);
			return $mime;
		}
		return mime_content_type($this->data['tmp_name']);
	}

	/**
	 * Devuelve la extensión del upload
	 *
	 * @return String La extensión
	 */
	function getExtension() {
		$info = pathinfo($this->data['name']);
		return $info['extension'];
	}

	/**
	 * Devuelve el nombre de archivo del upload
	 *
	 * @return String El nombre de archivo
	 */
	function getFileName() {
		return basename($this->data['name']);
	}

	/**
	 * Genera un nombre de archivo con un prefijo único al azar
	 *
	 * @return String El nombre de archivo
	 */
	function getRandomFileName() {
		$info = pathinfo($this->data['name']);
		return uniqid($info['filename'].'_') . '.' .$info['extension'];
	}

	/**
	 * Mueve el upload al directorio path especificado
	 *
	 * @param  String   $destination Path destino del archivo
	 * @param  Boolean  $mkdir Crear el directorio si no existe
	 * @return Boolean  True si se pudo mover, false en otro caso
	 */
	function moveTo($destination, $mkdir = true) {
		if ($mkdir) {
			$dir = dirname($destination);
			if (!is_dir($dir)) {
				mkdir($dir, $this->dirMode, true);
			}
		}
		if (@move_uploaded_file($this->data['tmp_name'], $destination)) {
			@chmod($destination, $this->fileMode);
			return $destination;
		}
		return false;
	}
}

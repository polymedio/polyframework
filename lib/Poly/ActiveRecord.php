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
 * Clase base para los modelos
 */
class Poly_ActiveRecord extends Poly_Validatable implements ArrayAccess {

	/**
	 * Relaciones 1 <- n
	 */
	public $hasMany = array();

	/**
	 * Relaciones 1 <- 1
	 */
	public $hasOne = array();

	/**
	 * Relaciones 1 -> 1
	 */
	public $belongsTo = array();

	/**
	 * Relaciones n <-> m
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * Nombre de tabla
	 */
	public $tableName = null;

	/**
	 * Clave primaria
	 */
	protected $primaryKey = array('id');

	/**
	 * Si es true, hace cache de las querys
	 */
	public $cacheQueries = false;

	/**
	 * Configuracion del datasource
	 */
	public $dbConfig = 'default';

	/**
	 * Campo utilizado para los listados
	 */
	public $displayField = 'id';

	/**
	 * Cache de los schemas de tablas
	 */
	protected static $_schemas = array();

	/**
	 * Si el registro existe
	 */
	protected $_exists = false;

	/**
	 * Contructor
	 * @param Mixed $id Valor de la clave primaria
	 * @param String $table nombre de la tabla
	 */
	function __construct($id = null, $table = null) {
		if (!is_null($table)) {
			$this->tableName = $table;
		}
		if (is_null($this->tableName)) {
			$this->tableName = strtolower(get_class($this)).'s';
		}
		if (!is_null($id)) {
			$this->read($id);
		}
		$this->__CLASS__ = get_class($this);

		foreach ($this->belongsTo as $key => $value) {
			if (!is_numeric($key)) {
				continue;
			}
			$this->belongsTo[$value] = array('class' => $value);
			unset($this->belongsTo[$key]);
		}

		foreach ($this->hasOne as $key => $value) {
			if (!is_numeric($key)) {
				continue;
			}
			$this->hasOne[$value] = array('class' => $value);
			unset($this->hasOne[$key]);
		}
	}

	/**
	 * Devuelve el valor de la clave primaria
	 * @return Mixed
	 */
	function id() {
		$primaryKey = array();
		foreach ((array)$this->primaryKey as $key) {
			$primaryKey[] = $this->{$key};
		}
		return $primaryKey;
	}

	/**
	 * Establece el valor de la clave primaria
	 * @param Mixed $id El valor de la clave primaria
	 */
	function setId($id) {
		$id = (array) $id;
		foreach ((array)$this->primaryKey as $key) {
			$this->{$key} = array_shift($id);
		}
	}

	/**
	 * Devuelve el nombre de la tabla
	 * @return String
	 */
	function getTableName() {
		return $this->tableName;
	}

	/**
	 * Devuelve una instancia del datasource
	 * @see Poly_ActiveRecord::$dbConfig
	 * @return Poly_DB
	 */
	function getDataSource() {
		$DB = Poly_DB::getInstance($this->dbConfig);
		$DB->cacheQueries = $this->cacheQueries;
		return $DB;
	}

	/**
	 * Devuelve la lista de campos de la tabla
	 * @return Array
	 */
	function schema() {
		$table = $this->getTableName();
		$key = $this->dbConfig . '.' . $table;
		if (isset(Poly_ActiveRecord::$_schemas[$key])) {
			return Poly_ActiveRecord::$_schemas[$key];
		}

		Poly_Cache::config('default');
		$fromCache = Poly_Cache::read("schema_$key");
		if ($fromCache !== false) {
			Poly_ActiveRecord::$_schemas[$key] = $fromCache;
			return $fromCache;
		}

		$DB = $this->getDataSource();
		$schema = $DB->describe($table);

		Poly_Cache::write("schema_$key", $schema);
		return Poly_ActiveRecord::$_schemas[$key] = $schema;
	}

	/**
	 * Construye un SELECT a partir de fragmentos SQL
	 *
	 * Claves válidas en $parts:
	 *	- fields 	string o array con los campos a seleccionar. Ej: "title, body"
	 *	- conditions 	fragmento de condiciones SQL. Ej: "group_id = :group_id AND rate > 3.5"
	 *	- limit: 	limite. Ej: "1,10"
	 *	- order: 	orden. Ej: "fecha DESC"
	 *	- group: 	orden. Ej: "fecha DESC"
	 *	- joins: 	tablas relacionadas. Ej: "categorias ON (categoria_id = categorias.id)"
	 *
	 * @param  Array  $parts Fragmentos SQL para construir el select
	 * @return String        Sentencia SQL SELECT
	 */
	function buildSelect($parts) {
		$defaults = array('fields' => '*', 'conditions' => '1', 'limit' => null, 'order' => null, 'group' => null, 'joins' => null);
		$parts = array_merge($defaults, $parts);
		extract($parts, EXTR_OVERWRITE);

		if (empty($conditions)) {
			$conditions = '1';
		}
		if (is_array($fields)) {
			$fields = join(', ', $this->quoteField($fields));
		}
		if (!empty($limit)) {
			$limit = "LIMIT $limit";
		}
		if (!empty($order)) {
			$order = "ORDER BY $order";
		}
		if (!empty($group)) {
			$group = "GROUP BY $group";
		}

		$table = $this->quoteField($this->getTableName());
		$sql = "SELECT $fields FROM $table $joins WHERE $conditions $group $order $limit";
		return $sql;
	}

	/**
	 * Obtiene array objetos con una query SQL
	 *
	 * @param String  $sql     Consulta sql a ejecutar
	 * @param Array   $params  Reemplazo de las variables en $sql
	 * @return Array           Array de objetos seleccionados por la query
	 */
	function query($sql, $params = array()) {
		$result = $this->getDataSource()->queryAndFetch($sql, $params, $this->schema(), get_class($this));
		foreach ($result as $object) {
			$object->_exists = true;
		}
		return $result;
	}

	/**
	 * Obtiene array multidimencional con una query SQL
	 *
	 * @param String  $sql     Consulta sql a ejecutar
	 * @param Array   $params  Reemplazo de las variables en $sql
	 * @return Array          Array multidimencional de filas seleccionadas por la query
	 */
	function queryAssoc($sql, $params = array()) {
		return $this->getDataSource()->queryAndFetch($sql, $params, $this->schema());
	}

	/**
	 * Encuentra los registros que cumplen las condiciones
	 *
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Array                 Los registros encontrados
	 */
	function findAll($conditions = '1', $params = array(), $extra = array()) {
		$extra['conditions'] = $conditions;
		$sql = $this->buildSelect($extra);
		return $this->query($sql, $params);
	}

	/**
	 * Encuentra un registro que cumple con las condiciones
	 *
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Object                El objeto encontrado
	 */
	function find($conditions = '1', $params = array(), $extra = array()) {
		$extra['limit'] = 1;
		$result = $this->findAll($conditions, $params, $extra);
		if ($result === false) {
			return false;
		}
		if (!empty($result)) {
			return $result[0];
		}
		return $result;
	}

	/**
	 * Genera un array con $primaryKey como clave y $displayField como valor
	 *
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Array                 Un array con $primaryKey como clave y $displayField como valor
	 */
	function findList($conditions = '1', $params = array(), $extra = array()) {
		$key = $this->primaryKey[0];
		$value = $this->displayField;

		if (!empty($extra['fields'])) {
			if (is_string($extra['fields'])) {
				$fields = explode(',', $extra['fields']);
				$key = trim($fields[0]);
				$value = trim($fields[1]);
			} else {
				list($key, $value) = $extra['fields'];
			}
		} else {
			$extra['fields'] = "{$key}, {$value}";
		}

		if (empty($extra['order'])) {
			$extra['order'] = "$value ASC";
		}

		$result = $this->findAll($conditions, $params, $extra);
		if ($result === false) {
			return $result;
		}
		$list = array();
		foreach($result as $item) {
			$list[$item->{$key}] = $item->{$value};
		}
		return $list;
	}

	/**
	 * Permite acceder a Poly_ActiveRecord::findList() como $Object->list
	 * @deprecated
	 */
	function getList() {
		$this->list = $this->findList();
		return $this->list;
	}

	/**
	 * Encuentra un registro por su clave primaria
	 *
	 * @param   Mixed   $id     Valor de la clave primaria
	 * @param   Array   $extra  Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Object          El objeto encontrado
	 */
	function findById($id, $extra = array()) {
		$id = (array)$id;
		$conditions = $params = array();

		foreach ((array)$this->primaryKey as $key) {
			$conditions[] = $this->quoteField($key) . " = :{$key}";
			$params[$key] = array_shift($id);
		}
		$conditions = implode(' AND ', $conditions);

		return $this->find($conditions, $params, $extra);
	}

	/**
	 * Encuentra un registro por su clave primaria y lo asigna al objeto
	 *
	 * @param   Mixed   $id     Valor de la clave primaria
	 * @param   Array   $extra  Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Boolean         True si encontro el registro
	 */
	function read($id, $extra = array()) {
		$extra['limit'] = 1;

		$id = (array)$id;
		$conditions = $params = array();
		foreach ((array)$this->primaryKey as $key) {
			$conditions[] = $this->quoteField($key) . " = :{$key}";
			$params[$key] = array_shift($id);
		}
		$extra['conditions'] = implode(' AND ', $conditions);

		$sql = $this->buildSelect($extra);
		$result = $this->queryAssoc($sql, $params);

		if (empty($result)) {
			return false;
		}
		foreach ($result[0] as $field => $value) {
			$this->$field = $value;
		}
		$this->_exists = true;
		return true;
	}

	/**
	 * Reasigna todos los campos leyendolos de la base de datos
	 * @return  Boolean         True si encontro el registro
	 */
	function reload() {
		return $this->read($this->id());
	}

	/**
	 * Vacia todos los campos
	 */
	function reset() {
		$schema = $this->schema();
		foreach ($schema as $field => $value) {
			unset($this->$field);
		}
		$this->_exists = false;
	}

	/**
	 * Cuenta la cantidad de registros que cumplen con una condición
	 *
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Integer               La cantidad de registros encontrados
	 */
	function findCount($conditions = '1', $params = array(), $extra = array()) {
		if (isset($extra['fields'])) {
			$fields = sprintf('COUNT(%s)', join(', ', (array)$extra['fields']));
		} else {
			$extra['fields'] = 'COUNT(*)';
		}
		$extra['conditions'] = $conditions;

		$sql = $this->buildSelect($extra);
		$result = $this->queryAssoc($sql, $params);
		if ($result === false) {
			return false;
		}

		if (empty($result)) {
			return 0;
		}

		$result = current(current($result));
		return $result;
	}

	/**
	 * Determina si el valor de un campo no se repite en la tabla
	 *
	 * Si el campo de la clave primaria tiene un valor, se excluira
	 * ese registro en la condición.
	 *
	 * @param String $field Nombre del campo a comprobar si es único
	 */
	function isUnique($field) {
		$conditions = "{$field} = :{$field}";
		$params = array($field => $this->$field);

		if ($id = $this->id()) {
			foreach ((array)$this->primaryKey as $key) {
				$conditions .= " AND " . $this->quoteField($key) . " != :{$key}";
				$params[$key] = array_shift($id);
			}
		}
		$count = $this->findCount($conditions, $params);
		return $count == 0;
	}

	/**
	 * Pagina el resultado de los registros que cumplen con una condición
	 *
	 * @param   Integer  $page        Página del resultado
	 * @param   Integer  $limit       Resultados por página
	 * @param   Mixed    $conditions  Fragmento SQL con las condiciones, si es un array anula a $extra
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @param   Array    $extra       Otros parametros para la consulta. @see Poly_ActiveRecord::buildSelect()
	 * @return  Array                 Los registros encontrados
	 */
	function paginate($page = 1, $limit = 20, $conditions = '1', $params = array(), $extra = array()) {
		if (is_array($conditions)) {
			$extra = $conditions;
			unset($conditions);
			if (isset($extra['params'])) {
				$params = $extra['params'];
				unset($extra['params']);
			}
		} else {
			$extra['conditions'] = $conditions;
		}

		if ($page < 1) {
			$page = 1;
		}
		$offset = ($page - 1) * $limit;
		$extra['limit'] = "$offset, $limit";

		$sql = $this->buildSelect($extra);
		return $this->query($sql, $params);
	}

	/**
	 * Callback llamado antes de guardar un registro
	 *
	 * @return  Boolean  retornar false para impedir que se guarde
	 */
	protected function beforeSave() {
		return true;
	}

	/**
	 * Callback llamado después de guardar un registro
	 *
	 * @param Boolean $created True si se creo un nuevo registro
	 */
	protected function afterSave($created) {
	}

	/**
	 * Callback llamado antes de guardar un campo
	 *
	 * @return  Boolean  retornar false para impedir que se guarde
	 */
	protected function beforeSaveField() {
		return true;
	}

	/**
	 * Callback llamado después de guardar un campo
	 *
	 */
	protected function afterSaveField() {
	}

	/**
	 * Callback llamado antes de actualizar varios registros
	 *
	 * @return  Boolean  retornar false para impedir que se actualize
	 */
	protected function beforeUpdateAll() {
		return true;
	}

	/**
	 * Callback llamado después de actualizar varios registros
	 *
	 */
	protected function afterUpdateAll() {
	}

	/**
	 * Callback llamado antes de eliminar un registro
	 *
	 * @return  Boolean  retornar false para impedir que se elimine
	 */
	protected function beforeDelete() {
		return true;
	}

	/**
	 * Callback llamado después de eliminar un registro
	 */
	protected function afterDelete($id) {
		$this->cleanHasAndBelongsToManyLinks();
	}

	/**
	 * Callback llamado antes de eliminar varios registros
	 *
	 * @return  Boolean  retornar false para impedir que se elimine
	 */
	protected function beforeDeleteAll() {
		return true;
	}

	/**
	 * Callback llamado después de eliminar varios registro
	 */
	protected function afterDeleteAll() {
	}

	/**
	 * Callback llamado antes de vaciar la tabla
	 *
	 * @return  Boolean  retornar false para impedir que se elimine
	 */
	protected function beforeTruncate() {
		return true;
	}

	/**
	 * Callback llamado después de vaciar la tabla
	 */
	protected function afterTruncate() {
	}

	/**
	 * Guarda un registro en la base de datos
	 *
	 * Llama a Poly_ActiveRecord::insert() o Poly_ActiveRecord::update() según corresponda
	 *
	 * @param   Boolean  $validate    Si es false se omite la validación
	 * @param   Array    $fields      Campos que deberán ser guardados
	 *
	 * @return  Boolean  true si fue guardado
	 */
	function save($validate = true, $fields = array()) {
		if ($this->_exists) {
			return $this->update($validate, $fields);
		}
		return $this->insert($validate, $fields);
	}

	/**
	 * Inserta un registro en la base de datos
	 *
	 * @param   Boolean  $validate    Si es false se omite la validación
	 * @param   Array    $fields      Campos que deberán ser guardados
	 *
	 * @return  Boolean  true si fue guardado
	 */
	function insert($validate = true, $fields = array()) {
		if ($validate && !$this->validate()) {
			return false;
		}

		if (!$this->beforeSave()) {
			return false;
		}

		$vars = array_keys(get_object_vars($this));
		$schema = $this->schema();
		if (empty($fields)) {
			$fields = array_intersect(array_keys($schema), $vars);
		}

		$columns = join($this->quoteField($fields), ', ');
		$values = join($fields, ', :');

		$table = $this->quoteField($this->getTableName());
		$sql = "INSERT INTO $table ($columns) VALUES (:$values)";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);

		foreach ($fields as $field) {
			$value = $this->$field;
			if (is_array($value)) {
				$value = json_encode($value);
			}
			$stmt->bindValue(":$field", $value, $schema[$field]);
		}

		$result = $stmt->execute();
		if ($result) {
			$this->setId($db->lastInsertId($table));
			$this->_exists = true;
			$this->afterSave(true);
		}
		return $result;
	}

	/**
	 * Reemplaza un registro en la base de datos
	 *
	 * @param   Boolean  $validate    Si es false se omite la validación
	 * @param   Array    $fields      Campos que deberán ser guardados
	 *
	 * @return  Boolean  true si fue guardado
	 */
	function replace($validate = true, $fields = array()) {
		if ($validate && !$this->validate()) {
			return false;
		}

		if (!$this->beforeSave()) {
			return false;
		}

		$vars = array_keys(get_object_vars($this));
		$schema = $this->schema();
		if (empty($fields)) {
			$fields = array_intersect(array_keys($schema), $vars);
		}

		$columns = join($this->quoteField($fields), ', ');
		$values = join($fields, ', :');

		$table = $this->quoteField($this->getTableName());
		$sql = "REPLACE INTO $table ($columns) VALUES (:$values)";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);

		foreach ($fields as $field) {
			$value = $this->$field;
			if (is_array($value)) {
				$value = json_encode($value);
			}
			$stmt->bindValue(":$field", $value, $schema[$field]);
		}

		$result = $stmt->execute();
		if ($result) {
			$this->_exists = true;
			$this->afterSave(true);
		}
		return $result;
	}

	/**
	 * Actualiza un registro en la base de datos
	 *
	 * @param   Boolean  $validate    Si es false se omite la validación
	 * @param   Array    $fields      Campos que deberán ser guardados
	 *
	 * @return  Boolean  true si fue guardado
	 */
	function update($validate = true, $fields = array()) {
		if ($validate && !$this->validate()) {
			return false;
		}

		if (!$this->beforeSave()) {
			return false;
		}

		$vars = array_keys(get_object_vars($this));
		$schema = $this->schema();
		if (empty($fields)) {
			$fields = array_intersect(array_keys($schema), $vars);
		}

		$updates = array();
		foreach ($fields as $field) {
			if (!in_array($field, $this->primaryKey)) {
				$updates[] = $this->quoteField($field) . " = :$field";
			}
		}
		$updates = implode(', ', $updates);

		$table = $this->quoteField($this->getTableName());
		$conditions = array();
		foreach ((array)$this->primaryKey as $i => $key) {
			$conditions[] = "$key = :$key";
		}
		$conditions = implode(' AND ', $conditions);

		$sql = "UPDATE $table SET $updates WHERE $conditions";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);

		foreach ($fields as $field) {
			$value = $this->$field;
			if (is_array($value)) {
				$value = json_encode($value);
			}
			$stmt->bindValue(":$field", $value, $schema[$field]);
		}
		$id = $this->id();
		foreach ((array)$this->primaryKey as $key) {
			$stmt->bindValue(":{$key}", array_shift($id), $schema[$key]);
		}

		$result = $stmt->execute();
		if ($result) {
			$this->_exists = true;
			$this->afterSave(false);
		}
		return $result;
	}

	/**
	 * Actualiza el valor de un campo en la base de datos
	 *
	 * @param   String   $field   Nombre del campo a actualizar
	 * @param   Mixed    $value   Nuevo valor del campo
	 *
	 * @return  Boolean  true si fue guardado
	 */
	function saveField($field, $value) {
		if (!$this->_exists) {
			return false;
		}
		$schema = $this->schema();
		if (!isset($schema[$field])) {
			return false;
		}

		if (!$this->beforeSaveField()) {
			return false;
		}

		$conditions = array();
		foreach ((array)$this->primaryKey as $i => $key) {
			$conditions[] = $this->quoteField($key) . " = :$key";
		}
		$conditions = implode(' AND ', $conditions);

		$table = $this->quoteField($this->getTableName());
		$sql = "UPDATE $table SET $field = :$field WHERE $conditions";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);
		$id = $this->id();
		foreach ((array)$this->primaryKey as $key) {
			$stmt->bindValue(":{$key}", array_shift($id), $schema[$key]);
		}
		$stmt->bindValue(":$field", $value, $schema[$field]);

		$result = $stmt->execute();
		if ($result) {
			$this->$field = $value;
			$this->afterSaveField();
		}
		return $result;
	}

	/**
	 * Elimina un registro
	 *
	 * @return   Boolean   True si se pudo eliminar
	 */
	function delete() {
		if (!$this->_exists) {
			return false;
		}
		if (!$this->beforeDelete()) {
			return false;
		}

		$schema = $this->schema();
		$table = $this->quoteField($this->getTableName());
		$conditions = array();
		foreach ((array)$this->primaryKey as $i => $key) {
			$conditions[] = $this->quoteField($key) . " = :$key";
		}
		$conditions = implode(' AND ', $conditions);
		$sql = "DELETE FROM $table WHERE $conditions";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);
		$id = $this->id();
		foreach ((array)$this->primaryKey as $key) {
			$stmt->bindValue(":{$key}", array_shift($id), $schema[$key]);
		}

		$result = $stmt->execute();
		if ($result) {
			$this->_exists = false;
			$this->afterDelete($id);
		}
		return $result;
	}

	/**
	 * Elimina registros que cumplen con las condiciones
	 *
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 */
	function deleteAll($conditions = null, $params = array()) {
		if (empty($conditions)) {
			return false;
		}
		if (!$this->beforeDeleteAll()) {
			return false;
		}

		$schema = $this->schema();
		$table = $this->quoteField($this->getTableName());
		$sql = "DELETE FROM $table WHERE $conditions";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);

		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$stmt->bindValue(":$key", $value, isset($schema[$key])?$schema[$key]:null);
			}
		}
		$result = $stmt->execute();
		if ($result) {
			$this->afterDeleteAll();
			return $stmt->rowCount();
		}
		return $result;
	}

	/**
	 * Vacia la tabla
	 * @return   Boolean   True si se pudo vaciar
	 */
	function truncate() {
		if (!$this->beforeTruncate()) {
			return false;
		}

		$table = $this->quoteField($this->getTableName());
		$sql = "TRUNCATE $table";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		if ($result) {
			$this->afterTruncate();
		}
		return $result;
	}

	/**
	 * Actualiza registros que cumplen con las condiciones
	 *
	 * @param   String   $updates     Fragmento SQL para actualizar los registros
	 * @param   String   $conditions  Fragmento SQL con las condiciones
	 * @param   Array    $params      Reemplazo de las variables de $conditions
	 * @return  Integer               cantidad de registros actualizados
	 */
	function updateAll($updates, $conditions = '1', $params = array()) {
		if (!$this->beforeUpdateAll()) {
			return false;
		}

		$schema = $this->schema();
		$table = $this->getTableName();
		$sql = "UPDATE $table SET $updates WHERE $conditions ";

		$db = $this->getDataSource();
		$stmt = $db->prepare($sql);

		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$stmt->bindValue(":$key", $value, isset($schema[$key])?$schema[$key]:null);
			}
		}

		$result = $stmt->execute();
		if ($result) {
			$this->afterUpdateAll();
			return $stmt->rowCount();
		}
		return $result;
	}

	/**
	 * Convierte el nombre de una funcion en un fragmento de condiciones SQL
	 *
	 * @param  String  $function  Función a convertir en condiciones
	 * @return Array              Fragmento de condiciones SQL y un array con los nombres de las variables
	 */
	protected function _parseFunction($function) {
		$operators = array('AND', 'OR', 'NOT');

		$function = strtolower(underscore($function));
		$fields = str_replace(array('_and_', '_or_', '_not_'), ' ', $function);
		$keys = explode(' ', $fields);

		$search = array('_and_', '_or_', '_not_');
		$replace = array(' AND ', ' OR ', ' NOT ');
		foreach ($keys as $key) {
			$search[] = $key;
			$replace[] = "$key = :$key";
		}
		$conditions = str_replace($search, $replace, $function);
		return array($conditions, $keys);
	}

	/**
	 * Crea una instancia de la clase especificada
	 *
	 * @param  String  $class     Nombre de la clase a instanciar
	 * @return Object             Instancia creada
	 */
	protected function create($class) {
		return new $class;
	}

	/**
	 * Obtiene los objetos relacionados a traves de un hasMany
	 *
	 * @param   Array    $options      Definicion de la relacion
	 * @param   String   $name         Nombre del miembro al que será asignado el resultado si $options['persist'] es true
	 * @return  Array                  Los objetos relacionados
	 */
	function hasMany($options, $name = null) {
		$options = $this->configureRelation('hasMany', $options);
		extract($options, EXTR_OVERWRITE);

		if (empty($conditions)) {
			$conditions = $this->quoteField($foreignKey) . " = :$foreignKey";
		}
		$params[$foreignKey] = current($this->id());

		$Object = $this->create($class);
		$result = $Object->findAll($conditions, $params, $extra);

		if ($result) {
			$myClass = get_class($this);
			foreach ($Object->belongsTo as $key => $value) {
				if ($value['class'] == $myClass) {
					foreach($result as &$item) {
						$item->$key = $this;
					}
				}
			}
		}

		if ($persist && $name) {
			$this->$name = $result;
		}
		return $result;
	}

	/**
	 * Obtiene el objeto relacionado a traves de un hasOne
	 *
	 * @param   Array    $options      Definicion de la relacion
	 * @param   String   $name         Nombre del miembro al que será asignado el resultado si $options['persist'] es true
	 * @return  Object                 El objeto encontrado
	 */
	function hasOne($options, $name = null) {
		$options = $this->configureRelation('hasOne', $options);
		extract($options, EXTR_OVERWRITE);

		if (empty($conditions)) {
			$conditions = $this->quoteField($foreignKey) . " = :$foreignKey";
		}
		$params[$foreignKey] = current($this->id());
		$extra['limit'] = 1;

		$Object = $this->create($class);
		$result = $Object->find($conditions, $params, $extra);
		if ($result) {
			$myClass = get_class($this);
			foreach ($Object->belongsTo as $key => $value) {
				if ($value['class'] == $myClass) {
					$result->$key = $this;
				}
			}
		}

		if ($persist && $name) {
			$this->$name = $result;
		}
		return $result;
	}

	/**
	 * Obtiene el objeto relacionado a traves de un belongsTo
	 *
	 * @param   Array    $options      Definicion de la relacion
	 * @param   String   $name         Nombre del miembro al que será asignado el resultado si $options['persist'] es true
	 * @return  Object                 El objeto encontrado
	 */
	function belongsTo($options, $name = null) {
		$options = $this->configureRelation('belongsTo', $options);
		extract($options, EXTR_OVERWRITE);

		$Object = $this->create($class);
		$result = $Object->findById($this->{$foreignKey});

		if ($persist && $name) {
			$this->$name = $result;
		}
		return $result;
	}

	/**
	 * Obtiene los objetos relacionados a traves de un hasAndBelongsToMany
	 *
	 * @param   Array    $options      Definicion de la relacion
	 * @param   String   $name         Nombre del miembro al que será asignado el resultado si $options['persist'] es true
	 * @return  Array                  Los objetos relacionados
	 */
	function hasAndBelongsToMany($options, $name = null) {
		$options = $this->configureRelation('hasAndBelongsToMany', $options);
		extract($options, EXTR_OVERWRITE);

		$Object = $this->create($class);

		if (empty($conditions)) {
			$conditions = sprintf($this->quoteField($selfKey) . " = :$selfKey");
		}
		$params[$selfKey] = current($this->id());

		$table = $Object->getTableName();
		if (!isset($extra['joins'])) {
			$extra['joins'] = sprintf('LEFT JOIN %s ON (%s.%s = %s.%s)', $this->quoteField($joinTable), $this->quoteField($joinTable), $this->quoteField($foreignKey), $this->quoteField($table), $this->quoteField($Object->primaryKey[0]));
		}

		if (!isset($extra['fields'])) {
			$extra['fields'] = $this->quoteField($table) . ".*";
		}

		$result = $Object->findAll($conditions, $params, $extra);
		if ($persist && $name) {
			$this->$name = $result;
		}
		return $result;
	}

	/**
	 * Configura las opciones predeterminadas de una relación
	 *
	 * @param  String  $type     Tipo de relación: hasOne, hasMany, belongsTo, hasAndBelongsToMany
	 * @param  String  $options  Configuración de la relación
	 * @return Array             Configuracion completa con las opciones predeterminadas
	 */
	protected function configureRelation($type, $options) {
		$class   = $options['class'];
		$myClass = get_class($this);

		switch ($type) {
			case 'hasAndBelongsToMany':
				$defaults = array(
					'foreignKey' => strtolower($options['class']) . '_id',
					'selfKey' => strtolower($myClass) . '_id',
					'joinTable' => strtolower(($class < $myClass)?"{$class}_{$myClass}":"{$myClass}_{$class}"),
					'params' => array(),
					'extra' => array(),
					'dependent' => true,
					'persist' => true
				);
				return array_merge($defaults, $options);

			case 'belongsTo':
				$defaults = array(
					'foreignKey' => strtolower($class) . '_id',
					'persist' => true
				);
				return array_merge($defaults, $options);

			case 'hasMany':
			case 'hasOne':
				$defaults = array(
					'foreignKey' => strtolower($myClass) . '_id',
					'params' => array(),
					'extra' => array(),
					'persist' => true
				);
				return array_merge($defaults, $options);
			default:
				return $options;
		}
	}

	/**
	 * Obtiene la configuracion de una relacion hasAndBelongsToMany
	 */
	protected function _getHasAndBelongsToManyOptions($Object, $name = null) {
		if ($name == null) {
			$class = get_class($Object);
			foreach ($this->hasAndBelongsToMany as $key => $options) {
				if ($class == $options['class']) {
					$name = $key;
					break;
				}
			}
		}

		if (!isset($this->hasAndBelongsToMany[$name])) {
			return false;
		}
		return $this->configureRelation('hasAndBelongsToMany', $this->hasAndBelongsToMany[$name]);
	}

	/**
	 * Detecta si existe un enlace hasAndBelongsToMany con un objeto
	 *
	 * @param  Object  $Object El objeto para comprobar la relación
	 * @param  String  $name   El nombre de la relación (opcional)
	 * @return Boolean         Si el enlace existe
	 */
	function hasLink($Object, $name = null) {
		if (!$options = $this->_getHasAndBelongsToManyOptions($Object, $name)) {
			return false;
		}
		$Model = new Poly_ActiveRecord(null, $options['joinTable']);
		$conditions = $this->quoteField($options['selfKey']) . " = :self AND " . $this->quoteField($options['foreignKey']) ." = :foreign";
		$params = array('self' => current($this->id()), 'foreign' => current($Object->id()));
		return $Model->findCount($conditions, $params);
	}

	/**
	 * Crea un enlace hasAndBelongsToMany con un objeto
	 *
	 * @param  Object  $Object El objeto al que se enlazará
	 * @param  String  $name   El nombre de la relación (opcional)
	 * @return Boolean         Si se creó el enlace
	 */
	function addLink($Object, $name = null) {
		if (!$options = $this->_getHasAndBelongsToManyOptions($Object, $name)) {
			return false;
		}
		$Model = new Poly_ActiveRecord(null, $options['joinTable']);
		$conditions = $this->quoteField($options['selfKey']) . " = :self AND " . $this->quoteField($options['foreignKey']) . " = :foreign";
		$params = array('self' => current($this->id()), 'foreign' => current($Object->id()));
		if ($Model->findAll($conditions, $params)) {
			return false;
		}
		$Model->{$options['selfKey']} = current($this->id());
		$Model->{$options['foreignKey']} = current($Object->id());
		return $Model->save();
	}

	/**
	 * Elimina el enlace hasAndBelongsToMany con un objeto
	 *
	 * @param  Object  $Object El objeto del que se elimina la relación
	 * @param  String  $name   El nombre de la relación (opcional)
	 * @return Boolean         Si se eliminó el enlace
	 */
	function removeLink($Object, $name = null) {
		if (!$options = $this->_getHasAndBelongsToManyOptions($Object, $name)) {
			return false;
		}
		$Model = new Poly_ActiveRecord(null, $options['joinTable']);
		$conditions = $this->quoteField($options['selfKey']) . " = :self AND ". $this->quoteField($options['foreignKey']) . " = :foreign";
		$params = array('self' => current($this->id()), 'foreign' => current($Object->id()));
		return $Model->deleteAll($conditions, $params);
	}

	/**
	 * Elimina los enlaces de las relaciones hasAndBelongsToMany
	 *
	 * @param  Object  $Object El objeto del que se elimina la relación
	 * @param  String  $name   El nombre de la relación (opcional)
	 * @return Boolean         Si se eliminó el enlace
	 */
	function cleanHasAndBelongsToManyLinks() {
		foreach ($this->hasAndBelongsToMany as $name => $options) {
			$options = $this->configureRelation('hasAndBelongsToMany', $options);
			if (!$options['dependent']) {
				continue;
			}
			$Model = new Poly_ActiveRecord(null, $options['joinTable']);
			$conditions = $this->quoteField($options['selfKey']) . " = :self";
			$params = array('self' => current($this->id()));
			$Model->deleteAll($conditions, $params);
		}
	}

	/**
	 * Sobrecarga para obtener metódos magicos
	 *
	 * Ej: $Objeto->findByNombreAndApellido('pedro', 'gonzales');
	 * Ej: $Objeto->findAllByEmail('tim@hotmail.com');
	 */
	function __call($method, $args) {
		if (strpos($method, 'findBy') === 0) {
			list($conditions, $keys) = $this->_parseFunction(substr($method, 6));
			$params = array_combine($keys, $args);
			return $this->find($conditions, $params);
		}
		if (strpos($method, 'findAllBy') === 0) {
			list($conditions, $keys) = $this->_parseFunction(substr($method, 9));
			$params = array_combine($keys, $args);
			return $this->findAll($conditions, $params);
		}
		if (strpos($method, 'create') === 0) {
			$class = substr($method, 6);
			$Object = new $class;
			$myClass = get_class($this);

			$Object->$myClass = $this;
			if (!empty($args)) {
				foreach ($args as $arg) {
					if ($arg instanceof Poly_ActiveRecord) {
						$argClass = get_class($arg);
						$Object->$argClass = $arg;
					}
				}
			}

			return $Object;
		}

		trigger_error(sprintf('Undefined method %s:%s', __CLASS__, $method));
	}

	/**
	 * Sobrecarga el objeto para obtener los objetos relacionados
	 */
	function __get($name) {
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}

		$relations = array('hasMany', 'belongsTo', 'hasOne', 'hasAndBelongsToMany');
		foreach ($relations as $relation) {
			$data = $this->$relation;
			if (isset($data[$name])) {
				return $this->$relation($data[$name], $name);
			}
		}
	}

	/**
	 * Sobrecarga el objeto para establecer los objetos relacionados
	 */
	function __set($name, $value) {
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}

		foreach ($this->belongsTo as $key => $config) {
			if ($config['class'] == $name) {
				$foreignKey = isset($config['foreignKey'])?$config['foreignKey']:strtolower($name) . '_id';
				$this->$foreignKey = current($value->id());
				return $this->$name = $value;
			}
		}

		return $this->$name = $value;
	}

	/**
	 * Convierte el objeto en un Array
	 *
	 * @return Array Un array conteniendo los miembro del objeto
	 */
	function toArray() {
		$arMembers = get_class_vars('Poly_ActiveRecord');
		$vars = get_object_vars($this);
		foreach ($vars as $key => &$var) {
			if (isset($arMembers[$key]) || in_array($key, array('tableName', 'validator'))) {
				unset($vars[$key]);
			}
			if ($var instanceof Poly_ActiveRecord) {
				$var = $var->toArray();
			}
			if (is_array($var)) {
				foreach ($var as &$v) {
					if ($v instanceof Poly_ActiveRecord) {
						$v = $v->toArray();
					}
				}
			}
		}
		return $vars;
	}

	/**
	 * Establece varias propiedades del objeto
	 *
	 * @param  Array  $data    Array asociativo con los datos
	 * @param  Array  $fields  Campos a inicializar
	 */
	function set($data, $fields = array()) {
		if (empty($fields)) {
			$fields = array_keys($data);
		}

		foreach ($fields as $field) {
			if (isset($data[$field])) {
				$this->$field = $data[$field];
			}
		}
	}

	public function offsetExists($offset) {
		return isset($this->$offset);
	}

	public function offsetSet($offset, $value) {
		$this->$offset = $value;
	}

	public function offsetGet($offset) {
		return $this->$offset;
	}

	public function offsetUnset($offset) {
		unset($this->$offset);
	}

	public function quoteField($fields) {
		if (is_array($fields)) {
			foreach($fields as $i => $field) {
				$fields[$i] = $this->quoteField($field);
			}
			return $fields;
		}

		$parts = explode('.', $fields);
		foreach($parts as $i => $name) {
			$parts[$i] = '`' . $name . '`';
		}
		return join('.', $parts);
	}
}

<?php

class dbAbstraction{

	/**
	 * sanitizes data input before sql queries
	 *
	 * @return void
	 * @author Alex Currie-Clark
	 **/
 	function check_input($value)
	 	{
			$db = db::getInstance();
		 	$value = $db->quote(stripslashes($value));
			return substr($value, 1, -1);
	 	}

	/*
	 * @the errors array
	 */
	public $errors = array();

	/*
	 * @the flags array
	 */
	private $flags = array();

	/*
	 * @The sql query
	 */
	private $sql;

	/**
	 * @The name=>value pairs
	 */
	private $values = array();

	/**
	 *
	 * @add a value to the values array
	 *
	 * @access public
	 *
	 * @param string $key the array key
	 *
	 * @param string $value The value
	 *
	 */
	public function addValue($value, $key = "")
	{
		$this->values[$key] = $value;
	}


	/**
	 *
	 * @set the values
	 *
	 * @access public
	 *
	 * @param array
	 *
	 */
	public function setValues($array)
	{
		$this->values = $array;
	}

	/**
	 *
	 * @delete a recored from a table
	 *
	 * @access public
	 *
	 * @param string $table The table name
	 *
	 * @param int ID
	 *
	 */
	public function delete($table, $id)
	{
		try
		{
			// get the primary key name
			$pk = $this->getPrimaryKey($table);
			$sql = "DELETE FROM $table WHERE $pk=:$pk";
			$db = db::getInstance();
			$stmt = $db->prepare($sql);
			$stmt->bindParam(":$pk", $id);
			$stmt->execute();
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
			return false;
		}
		return true;
	}


	/**
	 *
	 * @insert a record into a table
	 *
	 * @access public
	 *
	 * @param string $table The table name
	 *
	 * @param array $values An array of fieldnames and values
	 *
	 * @return int The last insert ID
	 *
	 */
	public function insert($table, $values=null)
	{
		$values = is_null($values) ? $this->values : $values;
		$sql = "INSERT INTO $table SET ";

		$obj = new CachingIterator(new ArrayIterator($values));

		try
		{
			$db = db::getInstance();
			foreach( $obj as $field=>$val)
			{
				$sql .= "`$field` = :$field";
				$sql .=  $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			$stmt = $db->prepare($sql);

			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			$stmt->execute($values);

			logger::Database(json_encode($values)." added to $table with id: ".$db->lastInsertId());

			// return the last insert id
			return $db->lastInsertId();
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * @update a table
	 *
	 * @access public
	 *
	 * @param string $table The table name
	 *
	 * @param int $id
	 *
	 */
	public function update($table, $id, $values=null)
	{
		$values = is_null($values) ? $this->values : $values;
		try
		{
			// get the primary key/
			$pk = $this->getPrimaryKey($table);

			// set the primary key in the values array
			$values[$pk] = $id;

			$obj = new CachingIterator(new ArrayIterator($values));

			$db = db::getInstance();
			$sql = "UPDATE $table SET \n";
			foreach( $obj as $field=>$val)
			{
				$sql .= "`$field` = :$field";
				$sql .= $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			$sql .= " WHERE $pk=$id";
			$stmt = $db->prepare($sql);

			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			// bind the primary key and the id
			$stmt->bindParam($pk, $id);
			$stmt->execute($values);

			logger::Database(json_encode($values)." updated in $table");

			// return the affected rows
			return $stmt->rowCount();
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * @get the name of the field that is the primary key
	 *
	 * @access private
	 *
	 * @param string $table The name of the table
	 *
	 * @return string
	 *
	 */
	private function getPrimaryKey($table)
	{
		try
		{
			// get the db name from the config.ini file
			$db_name = DB_NAME;

			$db = db::getInstance();
			$sql = "SELECT
				k.column_name
				FROM
				information_schema.table_constraints t
				JOIN
				information_schema.key_column_usage k
				USING(constraint_name,table_schema,table_name)
				WHERE
				t.constraint_type='PRIMARY KEY'
				AND
				t.table_schema='{$db_name}'
				AND
				t.table_name=:table";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':table', $table, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchColumn(0);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	public function set_query($query) {
		$this->sql = $query;
	}

	/**
	 *
	 * Fetch all records from table
	 *
	 * @access public
	 *
	 * @param $table The table name
	 *
	 * @return array
	 *
	 */
	public function query($query = null)
	{
		global $db_cache;
		$query = is_null($query) ? $this->sql : $query;
		if ($query) {
			
		}
		if (isset($db_cache[$query])) {
			logger::Database('Cached SQL = '.$query);
			return $db_cache[$query];
		}
		else {
			logger::Database('Prepared SQL = '.$query);
			$res = db::getInstance()->query($query);
			unset($this->flags);
			$db_cache[$query] = $res;
			return $res;
		}
	}

	/**
	 *
	 * @select statement
	 *
	 * @access public
	 *
	 * @param string $table
	 *
	 */
	public function select($table)
	{
		if (is_array($table)) {
			$first_pass = true;
			$this->sql = "SELECT * FROM (";
			foreach ($table as $key => $table_name) {
				$table[$key] = $this->check_input($table_name);
				$this->sql .= ($first_pass) ? '' : ' UNION';
				$this->sql .= " ( SELECT * FROM ".$table[$key].")";
				$first_pass = false;
			}
			$this->sql .= ') AS user';
			echo $this->sql;
		}
		else {
			$table = $this->check_input($table);
			$this->sql = "SELECT * FROM $table";
		}
	}

	public function count($table) {
		$table = $this->check_input($table);
		$this->sql = "SELECT count(*) FROM $table";
	}

	/**
	 * @where clause
	 *
	 * @access public
	 *
	 * @param string $field
	 *
	 * @param string $value
	 *
	 */
	public function where($field, $value)
	{
		if ($field == 'query') {
			$operator = '';
			$field = '';
		}
		else {
			$operator = '=';

			if (strpos($field, ' ') !== false) {
				$field = '`'.str_replace(' ', '` ', $field);
				$operator = ' ';
			}

			if (strpos($field, ',') !== false) {
				$operator = ' LIKE ';
				$field_values = explode(',', $field);
				$field = "CONCAT_WS(' '";
				foreach ($field_values as $field_value) {
					$field .= ', `'.$this->check_input(trim($field_value)).'`';
				}
				$field .= ')';
			}
			else {
				$field = ($operator == ' ') ? $this->check_input($field) : '`'.$this->check_input($field).'`';
			}

			if ($value === null) {
				$value = 'NULL';
			}
			else {
				$value = '\''.$this->check_input($value).'\'';
			}
		}
		if (!isset($this->flags['where']))
			$this->sql .= " WHERE $field$operator$value";
		else $this->sql .= " AND $field$operator$value";

		$this->flags['where'] = true;
	}

	/**
	 *
	 * @set limit
	 *
	 * @access public
	 *
	 * @param int $offset
	 *
	 * @param int $limit
	 *
	 * @return string
	 *
	 */
	public function limit($offset, $limit)
	{
		$offset = $this->check_input($offset);
		$limit = $this->check_input($limit);
		$this->sql .= " LIMIT $offset, $limit";
	}

	/**
	 *
	 * @add an AND clause
	 *
	 * @access public
	 *
	 * @param string $field
	 *
	 * @param string $value
	 *
	 */
	public function andClause($field, $value)
	{

		$value = $this->check_input($value);
		$field = $this->check_input($field);
		$this->sql .= " AND `$field`='$value'";
	}


	/**
	 *
	 * Add and order by
	 *
	 * @param string $fieldname
	 *
	 * @param string $order
	 *
	 */
	public function orderBy($fieldname, $order='ASC')
	{
		$fieldname = $this->check_input($fieldname);
		$order = $this->check_input($order);
		if (!isset($this->flags['order']))
			$this->sql .= " ORDER BY `$fieldname` $order";
		else $this->sql .= ", `$fieldname` $order";
		$this->flags['order'] = true;
	}
} // end of class

?>

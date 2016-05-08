<?php
/** The MYSQL Adapter class

	This class is the sql adapter for MySQL.
*/
class SQLAdapter_MySQL extends SQLAdapter {
	
	//! Defaults for selecting
	protected static $selectDefaults = array(
			'what'			=> '',//table.* => All fields
			'join'			=> '',// No join
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'groupby'		=> '',//Ex: Field
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::ARR_ASSOC,//Associative Array
	);
	
	//! Defaults for updating
	protected static $updateDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'ignore'		=> false,//false => Not ignore errors
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::NUMBER,//Number of updated lines
	);
	
	//! Defaults for deleting
	protected static $deleteDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'quick'			=> false,//false => Not merge index leaves
			'ignore'		=> false,//false => Not ignore errors
			'where'			=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> SQLAdapter::NUMBER,//Number of deleted lines
	);
	
	//! Defaults for inserting
	protected static $insertDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'delayed'		=> false,//false => Not delayed
			'ignore'		=> false,//false => Not ignore errors
			'into'			=> true,//true => INSERT INTO
			'output'		=> SQLAdapter::NUMBER,//Number of inserted lines
	);
	
	protected function connect(array $config) {
		$this->instance = new PDO(
			"mysql:dbname={$config['dbname']};host={$config['host']}".(!empty($config['port']) ? ';port='.$config['port'] : ''),
			$config['user'], $config['passwd'],
			array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::MYSQL_ATTR_DIRECT_QUERY=>true)
		);
		$this->instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	/** The function to use for SELECT queries
	 * @param $options The options used to build the query.
	 * @return Mixed return, depending on the 'output' option.
	 * @sa http://dev.mysql.com/doc/refman/5.0/en/select.html
	 * @sa SQLAdapter::select()
	 * 
	 * Using pdo_query(), It parses the query from an array to a SELECT query.
    */
	public function select(array $options=array()) {
		$options += self::$selectDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( !$options['number'] && $options['output'] == static::ARR_FIRST ) {
			$options['number']	= 1;
		}
		$TABLE		= static::escapeIdentifier($options['table']);
		// Auto-satisfy join queries
		if( empty($options['what']) ) {
			$options['what'] = $TABLE.'.*';
		}
		$WHAT		= is_array($options['what']) ? implode(', ', $options['what']) : $options['what'];
		$WC			= $options['where'] ? 'WHERE '.(is_array($options['where']) ? implode(' AND ', $options['where']) : $options['where']) : '';
		$GROUPBY	= !empty($options['groupby']) ? 'GROUP BY '.$options['groupby'] : '';
		$ORDERBY	= !empty($options['orderby']) ? 'ORDER BY '.$options['orderby'] : '';
		$HAVING		= !empty($options['having']) ? 'HAVING '.(is_array($options['having']) ? implode(' AND ', $options['having']) : $options['where']) : '';
		$LIMIT		= $options['number'] > 0 ? 'LIMIT '.
				( $options['offset'] > 0 ? $options['offset'].', ' : '' ).$options['number'] : '';
		$JOIN		= is_array($options['join']) ? implode(' ', $options['join']) : $options['join'];
		
		$QUERY		= "SELECT {$WHAT} FROM {$TABLE} {$JOIN} {$WC} {$GROUPBY} {$HAVING} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = $this->query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return (!empty($results) && $options['output'] == static::ARR_FIRST) ?  $results[0] : $results;
		
// 		return (!empty($results) && $options['output'] == static::ARR_ASSOC && $options['number'] == 1) ? $results[0] : $results;
	}
	
	/** The function to use for UPDATE queries
	 * @param $options The options used to build the query.
	 * @return The number of affected rows.
	 * @sa http://dev.mysql.com/doc/refman/5.0/en/update.html
	 * Using pdo_query(), It parses the query from an array to a UPDATE query.
	*/
	public function update(array $options=array()) {
		$options += self::$updateDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No field');
		}
		$OPTIONS	= '';
		$OPTIONS	.= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS	.= (!empty($options['ignore'])) ? ' IGNORE' : '';
		
// 		if( is_array($options['what']) ) {
// 			$string	= '';
// 			foreach( $options['what'] as $key => $value ) {
// 				$string .= ($string ? ', ' : '').static::escapeIdentifier($key).'='.static::formatValue($value);
// 			}
// 			$options['what'] = $string;
// 		}
		
		$WHAT		= $this->formatFieldList($options['what']);
		$WC			= ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY	= ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT		= ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		$TABLE		= static::escapeIdentifier($options['table']);
	
		$QUERY		= "UPDATE {$OPTIONS} {$TABLE} SET {$WHAT} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to use for INSERT queries
	 * @param $options The options used to build the query.
	 * @return The number of inserted rows.
	 * 
	 * It parses the query from an array to a INSERT query.
	 * Accept only the String syntax for what option.
	*/
	public function insert(array $options=array()) {
		$options += self::$insertDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		if( empty($options['what']) ) {
			throw new Exception('No field');
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : (!empty($options['delayed'])) ? ' DELAYED' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$OPTIONS .= (!empty($options['into'])) ? ' INTO' : '';
		
		$COLS = $WHAT = '';
		//Is an array
		if( is_array($options['what']) ) {
			//Is an indexed array of fields Arrays
			if( !empty($options['what'][0]) ) {
				// Quoted as escapeIdentifier()
				$COLS = '(`'.implode('`, `', array_keys($options['what'][0])).'`)';
				foreach($options['what'] as $row) {
					$WHAT .= (!empty($WHAT) ? ', ' : '').'('.implode(', ', $row).')';
				}
				$WHAT	= 'VALUES '.$WHAT;
			//Is associative fields Arrays
			} else {
// 				$WHAT	= 'SET '.parseFields($options['what'], '`');
				$WHAT	= 'SET '.$this->formatFieldList($options['what']);
			}
			
		//Is a string
		} else {
			$WHAT	= $options['what'];
		}
		$TABLE		= static::escapeIdentifier($options['table']);
		
		$QUERY = "INSERT {$OPTIONS} {$TABLE} {$COLS} {$WHAT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to use for DELETE queries
	 * @param $options The options used to build the query.
	 * @return The number of deleted rows.
	 * It parses the query from an array to a DELETE query.
	*/
	public function delete(array $options=array()) {
		$options += self::$deleteDefaults;
		if( empty($options['table']) ) {
			throw new Exception('Empty table option');
		}
		$OPTIONS	= '';
		$OPTIONS	.= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS	.= (!empty($options['quick'])) ? ' QUICK' : '';
		$OPTIONS	.= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WC			= ( !empty($options['where']) ) ? 'WHERE '.$options['where'] : '';
		$ORDERBY	= ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT		= ( $options['number'] > 0 ) ? 'LIMIT '.
			( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		$TABLE		= static::escapeIdentifier($options['table']);
		
		$QUERY		= "DELETE {$OPTIONS} FROM {$TABLE} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		return $this->query($QUERY, PDOEXEC);
	}
	
	/** The function to get the last inserted ID

	 * @param $table The table to get the last inserted id.
	 * @param $idfield The field id name.
	 * @return The last inserted id value.
	 * 
	 * It requires a successful call of insert() !
	*/
	public function lastID($table) {
// 	public function lastID($table, $idfield='id') {
		return $this->query("SELECT LAST_INSERT_ID();", PDOFETCHFIRSTCOL);
	}

	/** Escape SQL identifiers
	 * @param $Identifier The identifier to escape.
	 * @return The escaped identifier.
	 * 
	 * Escape the given string as an SQL identifier.
	*/
	public function escapeIdentifier($key) {
		return '`'.$key.'`';
	}
	
	public static function getDriver() {
		return 'mysql';
	}
}
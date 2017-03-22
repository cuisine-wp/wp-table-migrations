<?php

	namespace TableMigrations\Database\Grammars;

	use TableMigrations\Utilities\Fluent;
	use TableMigrations\Utilities\Sort;
	use TableMigrations\Database\BaseInterface;

	class BaseGrammar{


		/**
		 * The database-interface instance of this object
		 * 
		 * @var TableMigrations\Database\Contracts\QueryProducer;
		 */
		protected $interface;


		/**
		 * Database connection
		 * 
		 * @var WPDB instance
		 */
		protected $connection;


		/**
	     * The possible column modifiers.
	     *
	     * @var array
	     */
	    protected $modifiers = [
	        'unsigned', 'charset', 'collate', 'nullable',
	        'default', 'increment', 'comment'
	    ];

 		/**
	     * The possible column serials.
	     *
	     * @var array
	     */
	    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];
		

		/**
		 * Constructor for the grammar class
		 * 
		 * @param QueryProducer $interface
		 * @param WPDB $connection
		 */
		public function __construct( BaseInterface $interface, $connection )
		{
			$this->interface = $interface;	
			$this->connection = $connection;
		}


		/**
		 * Returns the table name
		 * 
		 * @return string
		 */
		public function getTable( $wrap = true )
		{
			$table = $this->connection->prefix . $this->interface->getTable();

			if( $wrap )
				return $this->wrap( $table );

			return $table;
		}

		/**
		 * Wrap this string in sepcial quotes
		 * 
		 * @param  String $string 
		 * 
		 * @return String
		 */
		public function wrap( $string, $wrapper = '`' )
		{
			return "{$wrapper}{$string}{$wrapper}";	
		}


		/**
		 * Wrap all values in an array
		 *
		 * @param $input
		 * 
		 * @return array
		 */
		public function wrapValues( $input, $wrapper = '`' )
		{
			$input = Sort::prependValues( $input, $wrapper );
			$output = Sort::appendValues( $input, $wrapper );
			return $output;
		}
		

		/*************************************************************/
		/*******    TABLES
		/*************************************************************/


		/**
		 * Compile a create table command 
		 * 
		 * @param  Fluent $command
		 * 
		 * @return Array
		 */
		public function compileCreate( Fluent $command )
		{
			$columns = implode( ', ', $this->getColumns() );
			$table = $this->getTable();
			
			return "CREATE TABLE $table ( $columns )";
		}


		/**
		 * Compile an add column
		 * 
		 * @param  Fluent $command
		 * 
		 * @return Array
		 */
		public function compileAdd( Fluent $command )
		{
			$table = $this->getTable();
			$columns = Sort::prependValues( $this->getColumns(), 'ADD ' );
			$columns = implode( ', ', $columns );

			return "ALTER TABLE $table $columns";
		}


		/**
		 * Compile a drop table command
		 *
		 * @param  Fluent $command
		 * 
		 * @return Array
		 */
		public function compileDrop( Fluent $command )
		{
			$table = $this->getTable();
			return "DROP TABLE $table";
		}


		/**
		 * Compile a rename table command
		 *
		 * @param  Fluent $command
		 * 
		 * @return Array
		 */
		public function compileRename( Fluent $command )
		{
			 $from = $this->tableName();

        	return "RENAME TABLE {$from} TO ".$this->wrap( $command->to );	
		}

		/**
		 * Compile a drop column command
		 *
		 * @param  Fluent $command
		 * 
		 * @return Array
		 */
		public function compileDropColumn( Fluent $command )
		{
			$columns = Sort::prependValues( $command->columns, 'DROP `' );
			$columns = Sort::appendValues( $columns, '`' );
        	$table = $this->getTable();

        	return "ALTER TABLE $table ".implode( ', ', $columns );	
		}


		/*************************************************************/
		/*******    RECORDS
		/*************************************************************/

		/**
		 * Compile an instert command
		 * 
		 * @param  Fluent $command
		 * 
		 * @return string (sql)
		 */
		public function compileInsert( Fluent $command )
		{	
			$table = $this->getTable(); 
			$columns = $command->data;

			$keys = $this->wrapValues( array_keys( $columns ) );
			$values = $this->wrapValues( array_values( $columns ), "'" );

			$keys = implode( ', ', $keys );
			$values = implode( ', ', $values );

			return "INSERT INTO $table ( $keys ) VALUES ( $values )";
		}


		/**
		 * Update a record
		 * 
		 * @param  Fluent $command
		 * 
		 * @return string (sql)
		 */
		public function compileUpdate( Fluent $command )
		{
			$table = $this->getTable();
			$data = $command->data;
			$where = $this->getClauses();
			
			$set = 'SET ';
			$columns = [];

			foreach( $data as $key => $value ){
				//$key = $this->Wrap( $key );
				$value = ( is_null( $value ) ? 'NULL' : $this->wrap( $value, "'" ) );
				$columns[] = "{$key}={$value}";
			}

			$set = $set . implode( ', ', $columns );

			return "UPDATE $table $set $where";
		}


		/**
		 * Compile a delete row action
		 * 
		 * @param  Fluent $command
		 * 
		 * @return string (sql)
		 */
		public function compileDelete( Fluent $command )
		{
			$table = $this->getTable();
			$where = $this->getClauses();

			return "DELETE FROM $table $where";
		}


		/**
		 * Compile a find action
		 * 
		 * @param  Fluent $command
		 * 
		 * @return string (Sql)
		 */
		public function compileFind( Fluent $command )
		{
			$table = $this->getTable();
			$where = $this->getClauses();
			$limit = $this->getLimit( false );
	
			return "SELECT * FROM $table $where $limit";
		}


		/**
		 * Compile loose columns
		 * 
		 * @return array
		 */
		public function getColumns()
		{
			$columns = [];

			foreach( $this->interface->getColumns() as $column ){

				$name = $this->wrap( $column->name );
				$sql = "{$name} {$this->getType( $column )}";

				$columns[] = $this->addModifiers( $sql, $column );
			}
			
			return $columns;
		}


		/**
		 * Return the clauses of a query
		 * 
		 * @return sql
		 */
		public function getClauses( $operator = 'AND' )
		{
			$clauses = Sort::flatten( $this->interface->clauses );
			$operator = apply_filters( 'table_migrations_database_where_operator', $operator, $this->interface );

			$where = array();

			foreach( $clauses as $key => $value ){

				if( $key != 'limit' )
					$where[] = $this->wrap( $key ) .' = '.$this->wrap( $value, "'" );
			}

			//return an empty string if no clauses where found
			switch( sizeof( $where ) ){

				case 0:
					return '';
					break;

				case 1:
					return ' WHERE '.$where[0];
					break;

				default:

					$whereString = implode( " {$operator} ", $where );
					return ' WHERE '.$whereString;
					break;
			}
		}

		/**
		 * Returns a limit
		 * 
		 * @return sql
		 */
		public function getLimit( $force = true )
		{
			$clauses = Sort::flatten( $this->interface->clauses );
			$limit = '';
			$amount = apply_filters( 'table_migrations_database_limit', 10, $this->interface );

			foreach( $clauses as $key => $value ){
				if( $key == 'limit' )
					return " LIMIT {$value}";
			}

			if( $force )
				return " LIMIT {$amount}";

			return '';
		}


		/**
		 * Returns the type of column in correct MySQL syntax
		 * 
		 * @return string
		 */
		public function getType( Fluent $column )
		{
			switch( strtolower( $column->type ) ){

				case 'char':
					return "char({$column->length})";
					break;

				case 'string':
					return "varchar({$column->length})";	
					break;

				case 'biginteger':
				case 'integer':
				case 'mediuminteger':
				case 'tinyinteger':
				case 'smallinteger':
					return str_replace( 'integer', 'int', $column->type );
					break;

				case 'float':
				case 'double':

					if( $column->total && $column->place )
						return "double( {$column->total}, {$column->places})";

					return 'double';
					break;

				case 'decimal':
					return "decimal({$column->total}, {$column->places})";
					break;

				case 'boolean':
				case 'bool':
					return 'tinyint(1)';
					break;

				case 'timestamp':

					if( $column->useCurrent )
						return 'timestamp default CURRENT_TIMESTAMP';

					return 'timestamp';
					break;

				case 'binary':
					return 'blob';
					break;


				default:
					return strtolower( $column->type );
					break;
			}
		}


		/**
		 * Add modifiers to the sql rules
		 * 
		 * @param string $sql
		 * @param Fluent $column
		 */
		public function addModifiers( $sql, Fluent $column )
		{
			foreach ($this->modifiers as $modifier) {

				switch( $modifier ){

					case 'unsigned':
						
						if( $column->unsigned )
							$sql .= ' UNSIGNED';

						break;

					case 'charset':

						if( !is_null( $column->charset ) ){
							$set = $this->wrap( $column->charset );
							$sql .= " CHARACTER SET $set";
						}

						break;

					case 'collate':

						if( !is_null( $column->collation ) ){
							$collation = $this->wrap( $column->collation );
							$sql .= " COLLATE $collation";
						}
						
						break;

					case 'nullable':

						$sql .= ( $column->nullable ? ' NULL' : ' NOT NULL' );
						break;

					case 'default':

						if( !is_null( $column->default ) )
							$sql .= ' DEFAULT '.$this->getDefaultValue( $column->default );
						
						break;

					case 'increment':
						
						if( in_array( $column->type, $this->serials ) && $column->autoIncrement )
							$sql .= ' AUTO_INCREMENT PRIMARY KEY';

						break;
				}
        	}

        	return $sql;
		}


		/**
		 * Returns the default value to a column
		 * 
		 * @param  string $value
		 * 
		 * @return string|null
		 */
		public function getDefaultValue( $value )
		{
			if( is_bool( $value ) ) {
            	return "'".(int) $value."'";
			}

        	return "'".strval($value)."'";
		}

	}
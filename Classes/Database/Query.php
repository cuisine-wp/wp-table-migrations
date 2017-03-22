<?php

	namespace TableMigrations\Database;

	use TableMigrations\Utilities\Fluent;
	use TableMigrations\Database\Grammars\MySql;
	use TableMigrations\Contracts\QueryProducer;

	class Query extends BaseInterface implements QueryProducer{


		/**
		 * WHERE Clauses 
		 * 
		 * @var Array
		 */
		public $clauses = [];


		/**
		 * Create a new schema record
		 * 
		 * @param string  $table
		 *
		 * @return void
		 */
		public function __construct( $table )
		{
			$this->table = $table;

		}


		/**
		 * Insert a record into the database
		 * 
		 * @param  String $table
		 * @param  Array $data
		 * 
		 * @return TableMigrations\Database\Record
		 */
		public function insert( $data )
		{
			$this->addCommand( 'insert', [ 'data' => $data ] );
			return $this;
		}


		/**
		 * Upsert a record into the database
		 * 
		 * @param  String $table
		 * @param  Array $data
		 * 
		 * @return TableMigrations\Database\Record
		 */
		public function update( $id, $data )
		{
			$this->where([ 'id' => $id ]);
			$this->addCommand( 'update', [ 'data' => $data ] );
			return $this;
		}


		/**
		 * Delete a record
		 * 
		 * @param  Int $id
		 * 
		 * @return void
		 */
		public function delete( $id )
		{
			$this->where([ 'id' => $id ]);
			$this->addCommand( 'delete' );
			return $this;
		}


		/**
		 * Set where parameters
		 * 
		 * @return TableMigrations\Database\Record
		 */
		public function where( $attributes )
		{
			$this->clauses[] = $attributes;
			return $this;
		}


		/**
		 * Return a find command
		 * 
		 * @return TableMigrations\Utilities\Fluent;
		 */
		public function find()
		{
			$command = $this->addCommand( 'find' );	
			return $this;
		}

		
		/**
		 * Add a limit to the eventual query
		 * 
		 * @return 
		 */
		public function limit( $limit )
		{
			$this->clauses[] = [ 'limit' => $limit ];	
			return $this;
		}


		/**
		 * Execute the query against the database
		 * 
		 * @param  WPDB $connection
		 * @return void
		 */
		public function run( $connection )
		{
			//set the grammar
			$this->grammar = new MySql( $this, $connection );

			foreach( $this->toSql() as $statement ) {
				$connection->query( $statement );	
			}
		}


		/**
		 * Execute a select query against the databse 
		 * 
		 * @param  WPDB $connection
		 * 
		 * @return array
		 */
		public function results( $connection )
		{
			$this->grammar = new MySql( $this, $connection );
			$sql = $this->toSql();

			$results = $connection->get_results( $sql[0] );
			return $results;
		}


		/**
		 * Get the prepared SQL statements for the blueprint
		 * 
		 * @return array
		 */
		public function toSql()
		{

			$statements = [];

			foreach( $this->commands as $command ) {

				$method = 'compile'.ucfirst( $command->name );

				if( method_exists( $this->grammar, $method ) ){
					$sql = $this->grammar->$method( $command );
					if( $sql != null ){
						$statements = array_merge( $statements, ( array ) $sql );
					}

				}
			}

			return $statements;
		}

		
	}
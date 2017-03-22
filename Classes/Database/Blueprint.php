<?php

	namespace TableMigrations\Database;

	use Closure;
	use TableMigrations\Utilities\Fluent;
	use TableMigrations\Database\Grammars\MySql;
	use TableMigrations\Contracts\QueryProducer;

	class Blueprint extends BaseInterface implements QueryProducer{


		/**
		 * Create a new schema blueprint
		 * 
		 * @param string  $table
		 * @param Closure|null $callback
		 *
		 * @return void
		 */
		public function __construct( $table, Closure $callback = null )
		{
			$this->table = $table;

			if( ! is_null( $callback ) )
				$callback( $this );
		}


		/**
		 * Execute the blueprint against the database
		 * 
		 * @param  WPDB $connection
		 * @return void
		 */
		public function build( $connection )
		{
			//set the grammar
			$this->grammar = new MySql( $this, $connection );

			foreach( $this->toSql( $connection ) as $statement ) {
				$connection->query( $statement );
			}
		}


		/**
		 * Get the prepared SQL statements for the blueprint
		 * 
		 * @param  WPDB $connection
		 * 
		 * @return array
		 */
		public function toSql( $connection )
		{

			$this->addImpliedCommands();

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


		/**
	     * Add the commands that are implied by the blueprint.
	     *
	     * @return void
	     */
	    protected function addImpliedCommands()
	    {
	        if( count( $this->getAddedColumns() ) > 0 && ! $this->creating() ) {
	            array_unshift( $this->commands, $this->createCommand( 'add' ) );
	        }

	        if( count( $this->getChangedColumns() ) > 0 && ! $this->creating() ) {
	            array_unshift( $this->commands, $this->createCommand( 'change' ) );
	        }

	    }


	    /********************************************************/
	    /****** 	Command Types
	    /*******************************************************/


		/**
		 * Create the table for this blueprint
		 * 
		 * @return \TableMigrations\Utilities\Fluent
		 */
		public function create()
		{
			return $this->addCommand( 'create' );
		}

		
		/**
	     * Determine
	     *
	     * @return bool
	     */
	    protected function creating()
	    {
	        foreach( $this->commands as $command ){
	            if( $command->name == 'create' ){
	                return true;
	            }
	        }

	        return false;
	    }



		/**
		 * Drop this table
		 * 
		 * @return \TableMigrations\Utilities\Fluent
		 */
		public function drop()
		{
			return $this->addCommand( 'drop' );	
		}


		/**
		 * Drop this table if it exists
		 * 
		 * @return \TableMigrations\Utilities\Fluent
		 */
		public function dropIfExists()
		{
			return $this->addCommand( 'dropIfExists' );	
		}


		/**
		 * Drop a column from the database
		 * 
		 * @param  Array $columns
		 * 
		 * @return \TableMigrations\Utilities\Fluent
		 */
		public function dropColumn( $columns )
		{
			$columns = is_array( $columns ) ? $columns : (array) func_get_args();
			return $this->addCommand( 'dropColumn', compact( 'columns' ) );
		}

		 /**
	     * Indicate that the given columns should be renamed.
	     *
	     * @param  string  $from
	     * @param  string  $to
	     * @return \TableMigrations\Utilities\Fluent
	     */
	    public function renameColumn( $from, $to )
	    {
	        return $this->addCommand( 'renameColumn', compact( 'from', 'to' ) );
	    }


	    /********************************************************/
	    /****** 	Column Types
	    /*******************************************************/


		/**
	     * Specify the primary key(s) for the table.
	     *
	     * @param  string|array  $columns
	     * @param  string  $name
	     * @param  string|null  $algorithm
	     * @return array
	     */
	    public function primary( $columns, $name = null, $algorithm = null )
	    {
	        return $this->indexCommand( 'primary', $columns, $name, $algorithm );
	    }

	    /**
	     * Specify a unique index for the table.
	     *
	     * @param  string|array  $columns
	     * @param  string  $name
	     * @param  string|null  $algorithm
	     * @return array
	     */
	    public function unique( $columns, $name = null, $algorithm = null )
	    {
	        return $this->indexCommand( 'unique', $columns, $name, $algorithm );
	    }

	    /**
	     * Specify an index for the table.
	     *
	     * @param  string|array  $columns
	     * @param  string  $name
	     * @param  string|null  $algorithm
	     * @return array
	     */
	    public function index( $columns, $name = null, $algorithm = null )
	    {
	        return $this->indexCommand( 'index', $columns, $name, $algorithm );
	    }

	    /**
	     * Specify a foreign key for the table.
	     *
	     * @param  string|array  $columns
	     * @param  string  $name
	     * @return array
	     */
	    public function foreign( $columns, $name = null )
	    {
	        return $this->indexCommand( 'foreign', $columns, $name );
	    }

	    /**
	     * Create a new auto-incrementing integer (4-byte) column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function increments( $column )
	    {
	        return $this->unsignedInteger( $column, true );
	    }

	    /**
	     * Create a new auto-incrementing small integer (2-byte) column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function smallIncrements( $column )
	    {
	        return $this->unsignedSmallInteger( $column, true );
	    }

	    /**
	     * Create a new auto-incrementing medium integer (3-byte) column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function mediumIncrements( $column )
	    {
	        return $this->unsignedMediumInteger( $column, true );
	    }

	    /**
	     * Create a new auto-incrementing big integer (8-byte) column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function bigIncrements( $column )
	    {
	        return $this->unsignedBigInteger( $column, true );
	    }

		/**
	     * Create a new char column on the table.
	     *
	     * @param  string  $column
	     * @param  int  $length
	     * @return array
	     */
	    public function char( $column, $length = 255 )
	    {
	        return $this->addColumn( 'char', $column, compact( 'length' ) );
	    }

	    /**
	     * Create a new string column on the table.
	     *
	     * @param  string  $column
	     * @param  int  $length
	     * @return array
	     */
	    public function string( $column, $length = 255 )
	    {
	        return $this->addColumn( 'string', $column, compact( 'length' ) );
	    }

	    /**
	     * Create a new text column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function text( $column )
	    {
	        return $this->addColumn( 'text', $column );
	    }

	    /**
	     * Create a new medium text column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function mediumText( $column )
	    {
	        return $this->addColumn( 'mediumText', $column );
	    }

	    /**
	     * Create a new long text column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function longText( $column )
	    {
	        return $this->addColumn( 'longText', $column );
	    }

	    /**
	     * Create a new integer (4-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @param  bool  $unsigned
	     * @return array
	     */
	    public function integer( $column, $autoIncrement = false, $unsigned = false )
	    {
	        return $this->addColumn( 'integer', $column, compact( 'autoIncrement', 'unsigned' ) );
	    }

	    /**
	     * Create a new tiny integer (1-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @param  bool  $unsigned
	     * @return array
	     */
	    public function tinyInteger( $column, $autoIncrement = false, $unsigned = false )
	    {
	        return $this->addColumn( 'tinyInteger', $column, compact( 'autoIncrement', 'unsigned' ) );
	    }

	    /**
	     * Create a new small integer (2-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @param  bool  $unsigned
	     * @return array
	     */
	    public function smallInteger( $column, $autoIncrement = false, $unsigned = false )
	    {
	        return $this->addColumn( 'smallInteger', $column, compact( 'autoIncrement', 'unsigned' ) );
	    }

	    /**
	     * Create a new medium integer (3-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @param  bool  $unsigned
	     * @return array
	     */
	    public function mediumInteger( $column, $autoIncrement = false, $unsigned = false )
	    {
	        return $this->addColumn( 'mediumInteger', $column, compact( 'autoIncrement', 'unsigned' ) );
	    }

	    /**
	     * Create a new big integer (8-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @param  bool  $unsigned
	     * @return array
	     */
	    public function bigInteger( $column, $autoIncrement = false, $unsigned = false )
	    {
	        return $this->addColumn( 'bigInteger', $column, compact( 'autoIncrement', 'unsigned' ) );
	    }

	    /**
	     * Create a new unsigned tiny integer (1-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @return array
	     */
	    public function unsignedTinyInteger( $column, $autoIncrement = false )
	    {
	        return $this->tinyInteger( $column, $autoIncrement, true );
	    }

	    /**
	     * Create a new unsigned small integer (2-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @return array
	     */
	    public function unsignedSmallInteger( $column, $autoIncrement = false )
	    {
	        return $this->smallInteger( $column, $autoIncrement, true );
	    }

	    /**
	     * Create a new unsigned medium integer (3-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @return array
	     */
	    public function unsignedMediumInteger( $column, $autoIncrement = false )
	    {
	        return $this->mediumInteger( $column, $autoIncrement, true );
	    }

	    /**
	     * Create a new unsigned integer (4-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @return array
	     */
	    public function unsignedInteger( $column, $autoIncrement = false )
	    {
	        return $this->integer( $column, $autoIncrement, true );
	    }

	    /**
	     * Create a new unsigned big integer (8-byte) column on the table.
	     *
	     * @param  string  $column
	     * @param  bool  $autoIncrement
	     * @return array
	     */
	    public function unsignedBigInteger( $column, $autoIncrement = false )
	    {
	        return $this->bigInteger( $column, $autoIncrement, true );
	    }

	    /**
	     * Create a new float column on the table.
	     *
	     * @param  string  $column
	     * @param  int     $total
	     * @param  int     $places
	     * @return array
	     */
	    public function float( $column, $total = 8, $places = 2 )
	    {
	        return $this->addColumn( 'float', $column, compact('total', 'places') );
	    }

	    /**
	     * Create a new double column on the table.
	     *
	     * @param  string   $column
	     * @param  int|null    $total
	     * @param  int|null $places
	     * @return array
	     */
	    public function double( $column, $total = null, $places = null )
	    {
	        return $this->addColumn( 'double', $column, compact( 'total', 'places' ) );
	    }

	    /**
	     * Create a new decimal column on the table.
	     *
	     * @param  string  $column
	     * @param  int     $total
	     * @param  int     $places
	     * @return array
	     */
	    public function decimal( $column, $total = 8, $places = 2 )
	    {
	        return $this->addColumn( 'decimal', $column, compact( 'total', 'places' ) );
	    }

	    /**
	     * Create a new boolean column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function boolean( $column )
	    {
	        return $this->addColumn( 'boolean', $column );
	    }

	    /**
	     * Create a new date column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function date( $column )
	    {
	        return $this->addColumn( 'date', $column );
	    }

	    /**
	     * Create a new date-time column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function dateTime( $column )
	    {
	        return $this->addColumn( 'dateTime', $column );
	    }

	    /**
	     * Create a new time column on the table.
	     *
	     * @param  string  $column
	     * @return array
	     */
	    public function time( $column )
	    {
	        return $this->addColumn( 'time', $column );
	    }


	 	/**
	     * Create a new timestamp column on the table.
	     *
	     * @param  string  $column
	     * @return \Illuminate\Support\Fluent
	     */
	    public function timestamp( $column )
	    {
	        return $this->addColumn( 'timestamp', $column );
	    }

	}
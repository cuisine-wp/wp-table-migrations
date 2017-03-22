<?php

	namespace TableMigrations\Database;

	use Closure;

	class Schema{


		/**
		 * Database connection 
		 * 
		 * @var WPDB instance
		 */
		protected $connection;


		/**
		 * Create a new database schema manager
		 *
		 * @return  void
		 */
		public function __construct()
		{
			global $wpdb;
			$this->connection = $wpdb;
		}


		/**
		 * Check if a table exists
		 *
		 * @param string $table
		 * 
		 * @return boolean
		 */
		public function hasTable( $table )
		{
				
		}


		/**
		 * Check if a table has a column
		 *
		 * @param string $table
		 * @param string $column
		 * 
		 * @return boolean
		 */
		public function hasColumn( $table, $column )
		{
				
		}


		/**
		 * Modify a table on the schema
		 * 
		 * @param  string $table
		 * @param  Closure $callback
		 * 
		 * @return void
		 */
		public function table( $table, Closure $callback )
		{
			$blueprint =  $this->createBlueprint( $table, $callback );			
			$this->build( $blueprint );
			
		}


		/**
		 * Create a new table on the schema
		 *
		 * @param string $table
		 * @param  \Closure $callback
		 * 
		 * @return void
		 */
		public function create( $table, Closure $callback )
		{
			$blueprint = $this->createBlueprint( $table );

			$blueprint->create();

			$callback( $blueprint );

			$this->build( $blueprint );
		}


		/**
		 * Drop a table from the schema
		 * 
		 * @param  string $table
		 * 
		 * @return void
		 */
		public function drop( $table )
		{
			$blueprint = $this->createBlueprint( $table );

			$blueprint->drop();

			$this->build( $blueprint );
		}


    	/**
     	* Drop a table from the schema if it exists.
     	*
     	* @param  string  $table
     	* @return void
     	*/
   		public function dropIfExists( $table )
    	{
        	$blueprint = $this->createBlueprint( $table );

        	$blueprint->dropIfExists();

        	$this->build( $blueprint );
    	}


    	/**
	     * Rename a table on the schema.
	     *
	     * @param  string  $from
	     * @param  string  $to
	     * @return void
	     */
	    public function rename( $from, $to )
	    {
	        $blueprint = $this->createBlueprint( $from );

	        $blueprint->rename( $to );

	        $this->build( $blueprint );
	    }


	    /**
	     * Execute the blueprint to build / modify the table.
	     *
	     * @param  \TableMigrations\Database\Blueprint  $blueprint
	     * 
	     * @return void
	     */
	    protected function build( Blueprint $blueprint )
	    {
	        $blueprint->build( $this->connection );
	    }

	    /**
	     * Create a new command set with a Closure
	     *
	     * @param string $table
	     * @param \Closure|null $callback
	     *
	     * @return \TableMigrations\Database\Blueprint
	     */
	    protected function createBlueprint( $table, Closure $callback = null )
	    {
	    	return new Blueprint( $table, $callback );
	    }


	}
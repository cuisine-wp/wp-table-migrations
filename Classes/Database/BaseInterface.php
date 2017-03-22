<?php

	namespace TableMigrations\Database;

	use TableMigrations\Utilities\Fluent;

	class BaseInterface{

		/**
		 * The table this blueprint describes
		 * 
		 * @var string
		 */
		protected $table;


		/**
		 * The columns that should be added to the table
		 * 
		 * @var array
		 */
		protected $columns = [];


		/**
		 * The commands that should be run for the table
		 * 
		 * @var array
		 */
		protected $commands = [];

		/**
		 * The grammar with which to talk to the Database
		 * 
		 * @var TableMigrations\Database\Contracts\Grammar
		 */
		protected $grammar;

		

		/********************************************************/
	    /****** 	Getters
	    /*******************************************************/

	    /**
	     * Returns this interface's table
	     * 
	     * @return string
	     */
	    public function getTable()
	    {
	    	return $this->table;
	    }

	    /**
	     * Returns all this interface's columns
	     * 
	     * @return array
	     */
	    public function getColumns()
	    {
	    	return $this->columns;
	    }


	    /**
	     * Returns all this interface's commands
	     * 
	     * @return array
	     */
	    public function getCommands()
	    {
	    	return $this->commands;
	    }


		/********************************************************/
	    /****** 	Columns
	    /*******************************************************/

	    /**
	     * Add a new column to the blueprint.
	     *
	     * @param  string  $type
	     * @param  string  $name
	     * @param  array   $parameters
	     * @return Column
	     */
	    public function addColumn( $type, $name, array $parameters = [] )
	    {
	        $attributes = array_merge( compact('type', 'name'), $parameters );

	        $this->columns[] = $column = new Fluent( $attributes );

	        return $column;
	    }

	    /**
	     * Remove a column from the schema blueprint.
	     *
	     * @param  string  $name
	     * @return $this
	     */
	    public function removeColumn($name)
	    {
	        $this->columns = array_values( array_filter( $this->columns, function( $c ) use ( $name ) {
	            return $c['attributes']['name'] != $name;
	        }));

	        return $this;
	    }

	   
		/**
	     * Get the columns on the blueprint that should be added.
	     *
	     * @return array
	     */
	    public function getAddedColumns()
	    {
	        return array_filter( $this->columns, function( $column ) {
	            return ! $column->change;
	        });
	    }

	    /**
	     * Get the columns on the blueprint that should be changed.
	     *
	     * @return array
	     */
	    public function getChangedColumns()
	    {
	        return array_filter( $this->columns, function( $column ) {
	            return (bool) $column->change;
	        });
	    }


	    /********************************************************/
	    /****** 	Commands
	    /*******************************************************/

 		/**
	     * Add a new index command to the blueprint.
	     *
	     * @param  string        $type
	     * @param  string|array  $columns
	     * @param  string        $index
	     * @param  string|null   $algorithm
	     * @return array
	     */
	    protected function indexCommand( $type, $columns, $index, $algorithm = null )
	    {
	        $columns = (array) $columns;

	        // If no name was specified for this index, we will create one using a basic
	        // convention of the table name, followed by the columns, followed by an
	        // index type, such as primary or index, which makes the index unique.
	        if (is_null($index)) {
	            $index = $this->createIndexName($type, $columns);
	        }

	        return $this->addCommand( $type, compact('index', 'columns', 'algorithm') );
	    }

		/**
		 * Add a command
		 * 
		 * @param string $string
		 * @param array  $parameters
		 *
		 * @return void
		 */
		public function addCommand( $string, $parameters = [] )
		{

			$this->commands[] = $command = $this->createCommand( $string, $parameters );
			return $command;
		}


		/**
	     * Create a new Fluent command.
	     *
	     * @param  string  $name
	     * @param  array   $parameters
	     * 
	     * @return \TableMigrations\Utilities\Fluent
	     */
		public function createCommand( $name, $parameters = [] )
		{
			return new Fluent( array_merge( compact( 'name' ), $parameters ) );
		}

	}
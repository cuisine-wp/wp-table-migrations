<?php

	namespace TableMigrations\Migrations;

	class Migrator{

		/**
		 * Direction of the migrator
		 * 
		 * @var string
		 */
		public $direction;


		/**
		 * Current timestamp
		 * 
		 * @var string
		 */
		public $timestamp;


		/**
		 * Constructor
		 */
		public function __construct()
		{
			return $this;
		}


		/**
		 * Handle new migrations
		 * 
		 * @return void
		 */
		public function up()
		{
			$this->direction = 'up';
			$this->run();
		}

		/**
		 * Set direction to down and run
		 * 
		 * @return void
		 */
		public function down()
		{
			$this->direction = 'down';
			$this->run();
		}


		/**
		 * Run migrations
		 * 
		 * @return void
		 */
		public function run()
		{
			$this->timestamp = time();
			do_action( 'run_table_migrations', $this );	
		}
	}
<?php 

	namespace TableMigrations\Contracts;

	interface Migration{

		public function up();
		public function down();
		
	}
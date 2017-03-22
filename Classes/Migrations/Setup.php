<?php

	namespace TableMigrations\Migrations;

	use TableMigrations\Wrappers\Schema;
	use TableMigrations\Database\Blueprint;
	use TableMigrations\Contracts\Migration as MigrationContract;

	class Setup extends Migration implements MigrationContract{

		/**
		 * Setup the migrations table
		 * 
		 * @return void
		 */
		public function up()
		{
			Schema::create( 'migrations', function( Blueprint $table ){
				$table->increments( 'id' )->unique();
				$table->string( 'name' );
				$table->timestamp( 'created' )->useCurrent();
			});
		}


		public function down()
		{
			Schema::drop( 'migrations' );
		}

	}

	\TableMigrations\Migrations\Setup::getInstance();
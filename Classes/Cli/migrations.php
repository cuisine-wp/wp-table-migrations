<?php
	namespace TableMigrations\Cli;

	use WP_CLI;
	use WP_CLI_Command;
	use TableMigrations\Migrations\Migrator;


	class MigrateCommands extends WP_CLI_Command{
	
		/**
		 * Run migrations
		 * 
		 * @param  array $args
		 * @param  array $assoc_args
		 * 
		 * @return WP_CLI::success message
		 */
    	function migrate( $args, $assoc_args ){

    		$migrator = new Migrator( );

    		if( isset( $assoc_args['rollback'] ) ){
    			$migrator->down();
    		}else{
    			$migrator->up();
    		}

    		// Print a success message
    		WP_CLI::success( "All migrations ran" );

    	}
		
	
	
	}


	WP_CLI::add_command( 'tables', 'TableMigrations\Cli\MigrateCommands' );


WP Table Migrations
===========================

Gives you the possibility to create Laravel-like database migrations in WordPress.

---

## Requirements

| Prerequisite    | How to check | How to install
| --------------- | ------------ | ------------- |
| PHP >= 5.4.x    | `php -v`     | [php.net](http://php.net/manual/en/install.php) |


---

## Installing

Clone the git repo - `git clone https://github.com/cuisine-wp/wp-table-migrations.git` or install with composer:

`composer require chefduweb/wp-table-migrations`

After you have all the files you need to install cuisine like a regular WordPress plugin:

1. move the files to wp-content/plugins
2. get into the WordPress admin and go to plugins
3. activate WP Table Migrations.

---

## Getting Started

Creating database migrations with the power of WP Table Migrations is quite easy; you just create a class that extends

```php
\TableMigrations\Migrations\Migration;
``` 

And use [WP Cli](https://wp-cli.org/) to run the command:
```
wp tables migrate
```

That's it! See our examples for more:

---

## Quick examples

Here are a few quick examples on how Cuisine will be making your life easier:

---

### Creating and dropping custom tables
This actually looks exactly the same as [Laravel's database migrations](https://laravel.com/docs/5.4/migrations), with the execption of having to extend a different class and create an instance of that class at the bottom.

```php

    namespace MyPlugin\Database;

    use TableMigrations\Wrappers\Schema;
    use TableMigrations\Database\Blueprint;
    use TableMigrations\Migrations\Migration;

    class ProductMetaMigration extends Migration{


        /**
         * Put the table up
         * 
         * @return void
         */
        public function up()
        {
            Schema::create( 'product_meta', function( Blueprint $table ){
                $table->increments( 'id' )->unique();
                $table->integer( 'product_id' )->unsigned();
                $table->string( 'image_url' )->nullable();
                $table->integer( 'position' );
                $table->float( 'price' );
                $table->integer( 'stock' );
            });         
        }

        /**
         * Default down function
         * 
         * @return void
         */
        public function down()
        {
            Schema::drop( 'product_meta' ); 
        }

    }


    \MyPlugin\Database\ProductMetaMigration::getInstance();

```
In this example a table named `product_meta` is being created or destroyed, dependent on which direction the migration is running. 


### Creating columns

You can create a lot of different column types with Table Migrations. Here's a list:

| Command | Description |
|---------|-------------|
| $table->bigInteger('votes'); | BIGINT equivalent for the database. |
| $table->binary('data');  | BLOB equivalent for the database. |
| $table->boolean('confirmed'); | BOOLEAN equivalent for the database. |
| $table->char('name', 4); | CHAR equivalent with a length. |
| $table->date('created_at'); | DATE equivalent for the database. |
| $table->dateTime('created_at'); | DATETIME equivalent for the database. |
| $table->decimal('amount', 5, 2); | DECIMAL equivalent with a precision and scale. |
| $table->double('column', 15, 8); | DOUBLE equivalent with precision, 15 digits in total and 8 after the decimal point. |
| $table->float('amount', 8, 2); | FLOAT equivalent for the database, 8 digits in total and 2 after the decimal point. |
| $table->increments('id'); } Incrementing ID (primary key) using a "UNSIGNED INTEGER" equivalent. |
| $table->integer('votes'); | INTEGER equivalent for the database. |
| $table->longText('description'); | LONGTEXT equivalent for the database. |
| $table->mediumInteger('numbers'); | MEDIUMINT equivalent for the database. |
| $table->mediumText('description'); | MEDIUMTEXT equivalent for the database. |
| $table->smallInteger('votes'); | SMALLINT equivalent for the database. |
| $table->string('email'); | VARCHAR equivalent column. |
| $table->string('name', 100); | VARCHAR equivalent with a length. |
| $table->text('description'); | TEXT equivalent for the database. |
| $table->time('sunrise'); | TIME equivalent for the database. |
| $table->tinyInteger('numbers'); | TINYINT equivalent for the database. |
| $table->timestamp('added_on'); | TIMESTAMP equivalent for the database. |
```php


### Saving & Fetching data
WordPress has all sorts of functions for saving and retrieving your data from the 12 database tables it already knows. We wouldn't want to leave you hanging, so we created a simpel wrapper for your custom tables as well.

```php

use TableMigrations\Wrappers\Record;

//inserting:
Record::insert( 'product_meta', $data );

//updating:
Record::update( 'product_meta', $id, $data );

//removing:
Record::delete( 'product_meta', $id );

```

The record class can handle table inserts, updates and upserts, but also fetches:
```php

//find all product meta associated with this product:
Record::find( 'product_meta' )
        ->where(['product_id' => $product_id ])
        ->results();

//find the first product meta where the price is 0
Record::find( 'product_meta' )
        ->where([
        
            'product_id' => $product_id,
            'price' => 0
        
        ])->first();
```

Adding where clauses results in "AND" queries being made on standard. Currently we're still working on "OR" support.
`Record::find()` queries will return you either and `Array` or `null` if they turn out to be empty.




---

## Documentation

Documentation is still being worked on at the moment.


---

## Contributing

Everyone is welcome to help [contribute](CONTRIBUTING.md) and improve this project. There are several ways you can contribute:

* Reporting issues
* Suggesting new features
* Writing or refactoring code
* Fixing [issues](https://github.com/cuisine-wp/wp-table-migrations/issues)



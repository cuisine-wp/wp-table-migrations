<?php

    namespace TableMigrations\Wrappers;
    
    class StaticInstance {
    
        /**
         * Static bootstrapped instance.
         *
         * @var \Cuisine\Wrappers\StaticInstance
         */
        public static $instance = null;
    
    
    
        /**
         * Init this Class
         *
         * @return \Cuisine\Wrappers\StaticInstance
         */
        public static function getInstance(){
            
            return static::$instance = new static();

        }
    
    
    } 
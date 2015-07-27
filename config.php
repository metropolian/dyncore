<?php

    switch('localhost')
    {    
        case 'localhost':
            define('DB_TYPE', 'mysql');

            /** MySQL hostname */
            define('DB_HOST', '127.0.0.1');

            /** MySQL database username */
            define('DB_USER', 'root');

            /** MySQL database password */
            define('DB_PASSWORD', '');

            /** The name of the database */
            define('DB_NAME', '');

            /** Database Charset to use in creating database tables. */
            define('DB_CHARSET', 'utf8');

            /** The Database Collate type. Don't change this if in doubt. */
            define('DB_COLLATE', '');

            break;
    }


    $Configs = array(
        'error_reporting' => 1,
        'base_path' => dirname(__FILE__),
        'db' => array(
            'main' => array(
                'type' => DB_TYPE,
                'host' => DB_HOST,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'name' => DB_NAME,
                'charset' => DB_CHARSET,
                'collate' => DB_COLLATE
            ),
            'user' => array(
                'type' => DB_TYPE,
                'host' => DB_HOST,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'name' => DB_NAME,
                'charset' => DB_CHARSET,
                'collate' => DB_COLLATE
            ),            
        ),
        'smtp' => array(
            'main' => array(
                'host' => 'smtp.gmail.com',
                'secure_auth' => true,
                'secure_mode' => 'tls',
                'port' => 587,
                'username' => 'imp.metropolian@gmail.com',
                ''
            )
        ),
    );



?>
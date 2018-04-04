<?php

use Illuminate\Database\Capsule\Manager as Capsule;


abstract class Db
{
    public static function setConnection()
    {
        $cfg = require(APP . '/config/config.php');
        $db = $cfg['db'];

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $db['host'],
            'database'  => $db['dbname'],
            'username'  => $db['username'],
            'password'  => $db['password'],
            'charset'   => $db['charset'],
            'collation' => $db['collation'],
            'prefix'    => '',
        ]);
        // Setup the Eloquent ORM
        $capsule->bootEloquent();
    }
}

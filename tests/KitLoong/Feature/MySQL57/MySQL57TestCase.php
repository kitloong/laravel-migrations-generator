<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2021/01/01
 */

namespace Tests\KitLoong\Feature\MySQL57;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\KitLoong\Feature\FeatureTestCase;

abstract class MySQL57TestCase extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'         => 'mysql',
            'url'            => null,
            'host'           => env('MYSQL57_HOST'),
            'port'           => env('MYSQL57_PORT'),
            'database'       => env('MYSQL57_DATABASE'),
            'username'       => env('MYSQL57_USERNAME'),
            'password'       => env('MYSQL57_PASSWORD'),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_general_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
    }

    protected function dumpSchemaAs(string $destination): void
    {
        $password = (!empty(config('database.connections.mysql.password')) ?
            '-p\''.config('database.connections.mysql.password').'\'' :
            '');
        $command  = sprintf(
            'mysqldump -h %s -u %s '.$password.' %s --compact --no-data > %s',
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.database'),
            $destination
        );
        exec($command);
    }

    protected function dropAllTables(): void
    {
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            Schema::dropIfExists(
                substr(
                    $table->{'Tables_in_'.config('database.connections.mysql.database')},
                    strlen(config('database.connections.mysql.prefix'))
                )
            );
        }
    }
}

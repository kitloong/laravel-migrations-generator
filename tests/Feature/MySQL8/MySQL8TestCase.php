<?php

namespace Tests\Feature\MySQL8;

use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\Feature\FeatureTestCase;

abstract class MySQL8TestCase extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'mysql8');
        $app['config']->set('database.connections.mysql8', [
            'driver'         => 'mysql',
            'url'            => null,
            'host'           => env('MYSQL8_HOST'),
            'port'           => env('MYSQL8_PORT'),
            'database'       => env('MYSQL8_DATABASE'),
            'username'       => env('MYSQL8_USERNAME'),
            'password'       => env('MYSQL8_PASSWORD'),
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
        $password = (!empty(config('database.connections.mysql8.password')) ?
            '-p\''.config('database.connections.mysql8.password').'\'' :
            '');

        if (!$this->isMaria()) {
            $skipColumnStatistics = '--skip-column-statistics';
        } else {
            $skipColumnStatistics = '';
        }

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s '.$password.' %s --compact --no-data '.$skipColumnStatistics.' > %s',
            config('database.connections.mysql8.host'),
            config('database.connections.mysql8.port'),
            config('database.connections.mysql8.username'),
            config('database.connections.mysql8.database'),
            $destination
        );
        exec($command);
    }

    protected function dropAllTables(): void
    {
        Schema::connection('mysql8')->dropAllViews();
        Schema::connection('mysql8')->dropAllTables();
    }
}

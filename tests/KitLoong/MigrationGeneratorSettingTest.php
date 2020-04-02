<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace Tests\KitLoong;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SQLAnywherePlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use Mockery;
use Orchestra\Testbench\TestCase;

class MigrationGeneratorSettingTest extends TestCase
{
    public function testSetConnectionTryMysql()
    {
        $dbconn = Mockery::mock(Connection::class);
        $dbPlatform = Mockery::mock(MySqlPlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('mysql')->andReturn($dbconn);

        $setting = new MigrationGeneratorSetting('mysql');
        $setting->setConnection('mysql');

        $this->assertSame(Platform::MYSQL, $setting->getPlatform());
    }

    public function testSetConnectionTryPostreSql()
    {
        $dbconn = Mockery::mock(Connection::class);
        $dbPlatform = Mockery::mock(PostgreSqlPlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('pgsql')->andReturn($dbconn);

        $setting = new MigrationGeneratorSetting('mysql');
        $setting->setConnection('pgsql');

        $this->assertSame(Platform::POSTGRESQL, $setting->getPlatform());
    }

    public function testSetConnectionTrySqlServer()
    {
        $dbconn = Mockery::mock(Connection::class);
        $dbPlatform = Mockery::mock(SQLServerPlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sqlsrv')->andReturn($dbconn);

        $setting = new MigrationGeneratorSetting('mysql');
        $setting->setConnection('sqlsrv');

        $this->assertSame(Platform::SQLSERVER, $setting->getPlatform());
    }

    public function testSetConnectionTrySqlite()
    {
        $dbconn = Mockery::mock(Connection::class);
        $dbPlatform = Mockery::mock(SqlitePlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sqlite')->andReturn($dbconn);

        $setting = new MigrationGeneratorSetting('mysql');
        $setting->setConnection('sqlite');

        $this->assertSame(Platform::SQLITE, $setting->getPlatform());
    }

    public function testSetConnectionTryOthers()
    {
        $dbconn = Mockery::mock(Connection::class);
        $dbPlatform = Mockery::mock(SQLAnywherePlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sql')->andReturn($dbconn);

        $setting = new MigrationGeneratorSetting('mysql');
        $setting->setConnection('sql');

        $this->assertSame(Platform::OTHERS, $setting->getPlatform());
    }
}

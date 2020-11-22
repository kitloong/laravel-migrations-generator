<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace Tests\KitLoong;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use Mockery;

class MigrationGeneratorSettingTest extends TestCase
{
    public function testSetConnectionTryMysql()
    {
        $dbconn = $this->mockConnection();
        $dbPlatform = Mockery::mock(MySqlPlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getSchemaManager->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('mysql')->andReturn($dbconn);

        $setting = new MigrationsGeneratorSetting();
        $setting->setConnection('mysql');

        $this->assertSame(Platform::MYSQL, $setting->getPlatform());
    }

    public function testSetConnectionTryPostreSql()
    {
        $dbconn = $this->mockConnection();
        $dbPlatform = Mockery::mock(PostgreSQL100Platform::class);

        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('pgsql')->andReturn($dbconn);

        $setting = new MigrationsGeneratorSetting();
        $setting->setConnection('pgsql');

        $this->assertSame(Platform::POSTGRESQL, $setting->getPlatform());
    }

    public function testSetConnectionTrySqlServer()
    {
        $dbconn = $this->mockConnection();
        $dbPlatform = Mockery::mock(SQLServer2012Platform::class);

        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sqlsrv')->andReturn($dbconn);

        $setting = new MigrationsGeneratorSetting();
        $setting->setConnection('sqlsrv');

        $this->assertSame(Platform::SQLSERVER, $setting->getPlatform());
    }

    public function testSetConnectionTrySqlite()
    {
        $dbconn = $this->mockConnection();
        $dbPlatform = Mockery::mock(SqlitePlatform::class);
        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sqlite')->andReturn($dbconn);

        $setting = new MigrationsGeneratorSetting();
        $setting->setConnection('sqlite');

        $this->assertSame(Platform::SQLITE, $setting->getPlatform());
    }

    public function testSetConnectionTryOthers()
    {
        $dbconn = $this->mockConnection();
        $dbPlatform = Mockery::mock(OraclePlatform::class);

        $dbconn->shouldReceive('getDoctrineConnection->getDatabasePlatform')
            ->andReturn($dbPlatform);

        DB::shouldReceive('connection')->with('sql')->andReturn($dbconn);

        $setting = new MigrationsGeneratorSetting();
        $setting->setConnection('sql');

        $this->assertSame(Platform::OTHERS, $setting->getPlatform());
    }

    /**
     * @return \Doctrine\DBAL\Connection|\Mockery\Mock
     */
    private function mockConnection(): Connection
    {
        $dbconn = Mockery::mock(Connection::class);

        $schemaManager = Mockery::mock(AbstractSchemaManager::class);

        $dbconn->shouldReceive('getDoctrineConnection')
            ->andReturnSelf();

        $dbconn->shouldReceive('getSchemaManager')
            ->andReturn($schemaManager);

        return $dbconn;
    }
}

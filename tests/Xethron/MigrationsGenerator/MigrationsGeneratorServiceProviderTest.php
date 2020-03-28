<?php namespace Xethron\MigrationsGenerator\Tests;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Foundation\Application;
use KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;
use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\Generator;

class MigrationsGeneratorServiceProviderTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testRegistersMigrationsGenerator()
    {
        $app_mock = $this->getAppMock();

        $reflector = new \ReflectionClass(MigrationsGeneratorServiceProvider::class);
        $fileMock = Mockery::mock('Illuminate\Filesystem\Filesystem');
        $fileMock->shouldReceive('getRequire')
            ->with(dirname($reflector->getFileName()).'/../../config/config.php')
            ->andReturn(['file'])
            ->once();

        $app_mock->shouldReceive('offsetGet')
            ->with('files')
            ->andReturn($fileMock)
            ->once();

        $configMock = Mockery::mock('Illuminate\Config\Repository');
        $configMock->shouldReceive('set')
            ->with('generators.config', ['file'])
            ->once();

        $app_mock->shouldReceive('offsetGet')
            ->with('config')
            ->andReturn($configMock)
            ->once();

        $app_mock
            ->shouldReceive('bind')
            ->atLeast()->once()
            ->with(
                'Illuminate\Database\Migrations\MigrationRepositoryInterface',
                Mockery::any()
            );

        $app_mock
            ->shouldReceive('singleton')
            ->atLeast()->once()
            ->with(
                'migration.generate',
                Mockery::on(function (Closure $callback) {
                    $mock = $this->getAppMock();

                    $mock
                        ->shouldReceive('make')
                        ->atLeast()->once()
                        ->with('Way\Generators\Generator')
                        ->andReturn(
                            $this->getGeneratorMock()
                        );

                    $mock
                        ->shouldReceive('make')
                        ->atLeast()->once()
                        ->with('Way\Generators\Compilers\TemplateCompiler')
                        ->andReturn(
                            $this->getTemplateCompilerMock()
                        );

                    $mock
                        ->shouldReceive('make')
                        ->atLeast()->once()
                        ->with('migration.repository')
                        ->andReturn(
                            $this->getMigrationRepositoryMock()
                        );

                    $repository_mock = $this->getRepositoryMock();

                    $repository_mock
                        ->shouldReceive('get')
                        ->atLeast()->once();

                    $mock
                        ->shouldReceive('make')
                        ->atLeast()->once()
                        ->with('config')
                        ->andReturn(
                            $repository_mock
                        );

                    $this->assertInstanceOf(
                        'Xethron\MigrationsGenerator\MigrateGenerateCommand',
                        $callback($mock)
                    );

                    return true;
                })
            );

        $service_provider_mock = $this->getServiceProviderMock($app_mock);

        $service_provider_mock
            ->shouldReceive('commands')
            ->atLeast()->once();

        $service_provider_mock->register();
    }

    public function testProvidesNothing()
    {
        $mock = $this->getServiceProviderMock();

        $this->assertEquals(
            [],
            $mock->provides()
        );
    }

    protected function getAppMock()
    {
        return Mockery::mock(Application::class);
    }

    protected function getServiceProviderMock($app_mock = null)
    {
        if ($app_mock === null) {
            $app_mock = $this->getAppMock();
        }

        return Mockery::mock(MigrationsGeneratorServiceProvider::class, [
            $app_mock
        ])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    protected function getGeneratorMock()
    {
        return Mockery::mock(Generator::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    protected function getTemplateCompilerMock()
    {
        return Mockery::mock(TemplateCompiler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    protected function getMigrationRepositoryMock()
    {
        return Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    protected function getRepositoryMock()
    {
        return Mockery::mock(Repository::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }
}

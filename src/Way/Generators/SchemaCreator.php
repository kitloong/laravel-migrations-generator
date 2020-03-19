<?php namespace Way\Generators;

use Way\Generators\Filesystem\Filesystem;
use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\Syntax\AddToTable;
use Way\Generators\Syntax\CreateTable;
use Way\Generators\Syntax\DroppedTable;
use Way\Generators\Syntax\RemoveFromTable;
use Exception;

class SchemaCreator {

    /**
     * @var Filesystem\Filesystem
     */
    private $file;

    /**
     * @var Compilers\TemplateCompiler
     */
    private $compiler;

    /**
     * @param Filesystem $file
     * @param TemplateCompiler $compiler
     */
    function __construct(Filesystem $file, TemplateCompiler $compiler)
    {
        $this->file = $file;
        $this->compiler = $compiler;
    }

    /**
     * Build the string for the migration file "up" method
     *
     * @param array $migrationData
     * @param array $fields
     * @throws Exception
     * @return mixed|string
     */
    public function up(array $migrationData, array $fields = [])
    {
        $this->guardAction($migrationData['action']);

        $method = $migrationData['action'] . 'Factory';

        return $this->$method($migrationData, $fields);
    }

    /**
     * Build the string for the migration file "down" method
     *
     * @param array $migrationData
     * @param array $fields
     * @throws Exception
     * @return array|mixed|string
     */
    public function down(array $migrationData, $fields = [])
    {
        $this->guardAction($migrationData['action']);

        $opposites = [
            'delete' => 'create',
            'create' => 'delete',
            'remove' => 'add',
            'add'    => 'remove'
        ];

        $method = $opposites[$migrationData['action']] . 'Factory';

        return $this->$method($migrationData, $fields);
    }

    /**
     * @param $action
     * @throws Exception
     * @internal param array $migrationData
     */
    protected function guardAction($action)
    {
        if (!in_array($action, ['create', 'add', 'remove', 'delete']))
        {
            throw new InvalidMigrationName('Please rewrite your migration name to begin with "create", "add", "remove", or "delete."');
        }
    }

    /**
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    protected function createFactory($migrationData, $fields)
    {
        return (new CreateTable($this->file, $this->compiler))->create($migrationData, $fields);
    }

    /**
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    protected function addFactory($migrationData, $fields)
    {
        return (new AddToTable($this->file, $this->compiler))->add($migrationData, $fields);
    }

    /**
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    protected function removeFactory($migrationData, $fields)
    {
        return (new RemoveFromTable($this->file, $this->compiler))->remove($migrationData, $fields);
    }

    /**
     * @param $migrationData
     * @param $fields
     * @return string
     */
    protected function deleteFactory($migrationData, $fields)
    {
        return (new DroppedTable)->drop($migrationData['table']);
    }

}
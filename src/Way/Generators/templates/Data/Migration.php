<?php namespace Way\Generators\Templates\Data;

use App;
use Way\Generators\Parsers\MigrationFieldsParser;
use Way\Generators\Parsers\MigrationNameParser;

class Migration {

    /**
     * The name of the migration.
     *
     * @var string
     */
    private $migrationName;

    /**
     * A string representation of the migration fields.
     *
     * @var string
     */
    private $fields;

    /**
     * Create a new Migration template data instance.
     *
     * @param string $migrationName
     * @param string $fields
     */
    public function __construct($migrationName, $fields)
    {
        $this->migrationName = $migrationName;
        $this->fields = $fields;
    }

    /**
     * Fetch the template data for a migration generation.
     *
     * @return array
     */
    public function fetch()
    {
        $parsedName = $this->getParsedMigrationName();
        $parsedFields = $this->getParsedMigrationFields();

        return [
            'class' => $this->getClass(),
            'up'    => $this->getMigrationUp($parsedName, $parsedFields),
            'down'  => $this->getMigrationDown($parsedName, $parsedFields)
        ];
    }

    /**
     * Parse the migration name.
     *
     * @return array
     */
    private function getParsedMigrationName()
    {
        $nameParser = new MigrationNameParser;

        return $nameParser->parse($this->migrationName);
    }

    /**
     * Parse the migration fields.
     *
     * @return array
     */
    private function getParsedMigrationFields()
    {
        $fieldParser = new MigrationFieldsParser;

        return $fieldParser->parse($this->fields);
    }

    /**
     * Get the class name for the migration.
     */
    private function getClass()
    {
        return ucwords(camel_case($this->migrationName));
    }

    /**
     * Get the schema for the up() method.
     *
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    private function getMigrationUp($migrationData, $fields)
    {
        return $this->resolveSchemaCreator()->up($migrationData, $fields);
    }

    /**
     * Get the schema for the down() method.
     *
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    private function getMigrationDown($migrationData, $fields)
    {
        return $this->resolveSchemaCreator()->down($migrationData, $fields);
    }

    /**
     * Get a SchemaCreator instance.
     *
     * @return mixed
     */
    private function resolveSchemaCreator()
    {
        return App::make('Way\Generators\SchemaCreator');
    }

} 
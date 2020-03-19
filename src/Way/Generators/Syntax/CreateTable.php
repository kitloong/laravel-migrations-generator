<?php namespace Way\Generators\Syntax;

class CreateTable extends Table {

    /**
     * Build string for creating a
     * table and columns
     *
     * @param $migrationData
     * @param $fields
     * @return mixed
     */
    public function create($migrationData, $fields)
    {
        $migrationData = ['method' => 'create', 'table' => $migrationData['table']];

        // All new tables should have an identifier
        // Let's add that for the user automatically
        array_unshift($fields, ['field' => 'id', 'type' => 'increments']);

        // We'll also add timestamps to new tables for convenience
        array_push($fields, ['field' => '', 'type' => 'timestamps']);

        return (new AddToTable($this->file, $this->compiler))->add($migrationData, $fields);
    }

} 
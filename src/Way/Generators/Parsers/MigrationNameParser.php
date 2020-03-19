<?php namespace Way\Generators\Parsers;

class MigrationNameParser {

    /**
     * Parse a migration name, like:
     * create_orders_table
     * add_last_name_to_recent_orders_table
     *
     * @param $migrationName
     * @throws InvalidActionType
     * @return array
     */
    public function parse($migrationName)
    {
        // Split the migration name into pieces
        // create_orders_table => ['create', 'orders', 'table']
        $pieces = explode('_', $migrationName);

        // We'll start by fetching the CRUD action type
        $action = $this->normalizeActionName(array_shift($pieces));

        // Next, we can remove any "table" string at
        // the end of the migration name, like:
        // create_orders_table
        if (end($pieces) == 'table') array_pop($pieces);

        // Now, we need to figure out the table name
        $table = $this->getTableName($pieces);

        return compact('action', 'table');
    }

    /**
     * Determine what the table name should be
     *
     * @param array $pieces
     * @return string
     */
    protected function getTableName(array $pieces)
    {
        $tableName = [];

        // This is deceptively complex, because
        // there are a number of ways to write
        // these migration names. We'll work backwards
        // to figure out the name.
        foreach(array_reverse($pieces) as $piece)
        {
            // Once we get to a connecting word (if any), this
            // will signal the end of our search. So, for
            // add_name_to_archived_lessons, "archived_lessons"
            // would be the table name
            if (in_array($piece, ['to', 'for', 'on', 'from', 'into'])) break;

            $tableName[] = $piece;
        }

        // We can't forget to reverse it back again!
        return implode('_', array_reverse($tableName));
    }

    /**
     * Try to mold user's input
     * to one of the CRUD operations
     *
     * @param $action
     * @return string
     */
    protected function normalizeActionName($action)
    {
        switch ($action)
        {
            case 'create':
            case 'make':
                return 'create';
            case 'delete':
            case 'destroy':
            case 'drop':
                return 'delete';
            case 'add':
            case 'append':
            case 'update':
            case 'insert':
                return 'add';
            default:
                return $action;
        }
    }

}
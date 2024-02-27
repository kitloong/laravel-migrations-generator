<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

trait SQLSrvParser
{
    /**
     * Parse the default value.
     */
    public function parseDefault(?string $default): ?string
    {
        if ($default === null) {
            return null;
        }

        while (preg_match('/^\((.*)\)$/s', $default, $matches)) {
            $default = $matches[1];
        }

        if ($default === 'NULL') {
            return null;
        }

        if (preg_match('/^\'(.*)\'$/s', $default, $matches) === 1) {
            $default = str_replace("''", "'", $matches[1]);
        }

        if ($default === 'getdate()') {
            return 'CURRENT_TIMESTAMP';
        }

        return $default;
    }
}

<?php

namespace KitLoong\MigrationsGenerator\Support;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Setting;

class IndexNameHelper
{
    public function __construct(private readonly Setting $setting)
    {
    }

    /**
     * Skip generate index name in migration file if following conditions met:
     * 1. Index is primary.
     * 2. Argument `--default-index-names` is true.
     * 3. Index name is identical with framework's default naming practice.
     */
    public function shouldSkipName(string $table, Index $index): bool
    {
        if ($this->setting->isIgnoreIndexNames()) {
            return true;
        }

        if (
            $index->getType() === IndexType::PRIMARY
            && $index->getName() === ''
        ) {
            return true;
        }

        $indexName = strtolower($table . '_' . implode('_', $index->getColumns()) . '_' . $index->getType()->value);
        $indexName = (string) str_replace(['-', '.'], '_', $indexName);
        return $indexName === $index->getName();
    }
}

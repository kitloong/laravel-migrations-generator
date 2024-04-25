<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Setting;

class CharsetModifier implements Modifier
{
    public function __construct(private readonly Setting $setting)
    {
    }

    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, mixed ...$args): Method
    {
        if (!$this->setting->isUseDBCollation()) {
            return $method;
        }

        // Collation is not set in PgSQL
        $tableCollation = $table->getCollation() ?? '';
        $tableCharset   = Str::before($tableCollation, '_');

        $charset = $column->getCharset();

        if ($charset !== null && $charset !== $tableCharset) {
            $method->chain(ColumnModifier::CHARSET, $charset);
        }

        return $method;
    }
}

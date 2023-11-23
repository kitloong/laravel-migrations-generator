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
    /**
     * @var \KitLoong\MigrationsGenerator\Setting
     */
    private $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, ...$args): Method
    {
        if (!$this->setting->isUseDBCollation()) {
            return $method;
        }

        // Collation is not set in PgSQL
        $tableCollation = $table->getCollation() ?? '';
        $tableCharset   = Str::before($tableCollation, '_');

        $charset = $column->getCharset();

        if ($charset !== null && $charset !== $tableCharset) {
            $method->chain(ColumnModifier::CHARSET(), $charset);
        }

        return $method;
    }
}

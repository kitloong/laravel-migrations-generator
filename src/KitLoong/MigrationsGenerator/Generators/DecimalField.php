<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 14:54
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;

class DecimalField
{
    // (8, 2) are default value of decimal, float
    private const DEFAULT_PRECISION = 8;
    private const DEFAULT_SCALE = 2;

    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function makeField(array $field, Column $column): array
    {
        $args = $this->getDecimalPrecision($column->getPrecision(), $column->getScale());
        if (!empty($args)) {
            $field['args'] = $args;
        }

        if ($column->getUnsigned()) {
            $field['decorators'][] = ColumnModifier::UNSIGNED;
        }
        return $field;
    }

    /**
     * @param  int  $precision
     * @param  int  $scale
     * @return array
     */
    private function getDecimalPrecision(int $precision, int $scale): array
    {
        $return = [];
        if ($precision != self::DEFAULT_PRECISION || $scale != self::DEFAULT_SCALE) {
            $return[] = $precision;
            if ($scale != self::DEFAULT_SCALE) {
                $return[] = $scale;
            }
        }
        return $return;
    }
}

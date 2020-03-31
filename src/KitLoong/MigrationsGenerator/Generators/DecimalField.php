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
        $field['args'] = $this->getDecimalPrecision($column->getPrecision(), $column->getScale());
        if ($column->getUnsigned()) {
            $field['decorators'][] = ColumnModifier::UNSIGNED;
        }
        return $field;
    }

    /**
     * @param  int  $precision
     * @param  int  $scale
     * @return string|null
     */
    private function getDecimalPrecision(int $precision, int $scale): ?string
    {
        if ($precision != self::DEFAULT_PRECISION or $scale != self::DEFAULT_SCALE) {
            $result = $precision;
            if ($scale != self::DEFAULT_SCALE) {
                $result .= ', '.$scale;
            }
            return $result;
        }
        return null;
    }
}

<?php

namespace MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Collection;
use MigrationsGenerator\Generators\Writer\WriterConstant;

trait Stringable
{
    /** @var string Empty string will be parsed as line break. */
    protected $lineBreak = '';

    /**
     * Implodes lines with tab.
     *
     * @param  string[]  $lines
     * @param  int  $numberOfPrefixTab  Number of tabs to implode.
     * @return string
     */
    public function implodeLines(array $lines, int $numberOfPrefixTab): string
    {
        $tab = WriterConstant::TAB;
        return implode(
            WriterConstant::LINE_BREAK.str_repeat($tab, $numberOfPrefixTab),
            $lines
        );
    }

    /**
     * Convert $value to printable string.
     *
     * @param  mixed  $value
     * @return string
     */
    public function convertFromAnyTypeToString($value): string
    {
        if ($value === $this->lineBreak) {
            return $value;
        }

        switch (gettype($value)) {
            case 'array':
                return '['.implode(', ', $this->mapArrayItemsToString($value)).']';
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'string':
                return "'".addcslashes($value, "'")."'";
            default:
                return $value;
        }
    }

    /**
     * Convert $list items to printable string.
     *
     * @param  array  $list
     * @return array
     */
    public function mapArrayItemsToString(array $list): array
    {
        return (new Collection($list))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->toArray();
    }
}

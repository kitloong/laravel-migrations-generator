<?php

namespace MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Collection;
use MigrationsGenerator\Generators\Writer\WriterConstant;

trait Stringable
{
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

        $content = '';
        foreach ($lines as $i => $line) {
            // First line or line break
            if ($i === 0 || $line === WriterConstant::LINE_BREAK) {
                $content .= $line;
                continue;
            }

            $content .= WriterConstant::LINE_BREAK.str_repeat($tab, $numberOfPrefixTab).$line;
        }
        return $content;
    }

    /**
     * Convert $value to printable string.
     *
     * @param  mixed  $value
     * @return string
     */
    public function convertFromAnyTypeToString($value): string
    {
        if ($value === WriterConstant::LINE_BREAK) {
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

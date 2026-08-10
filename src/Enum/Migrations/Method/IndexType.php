<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use InvalidArgumentException;

/**
 * Predefined index types of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#available-index-types
 */
enum IndexType: string implements MethodName
{
    case FULLTEXT       = 'fullText';
    case FULLTEXT_CHAIN = 'fulltext'; // Use lowercase.
    case INDEX          = 'index';
    case PRIMARY        = 'primary';
    case SPATIAL_INDEX  = 'spatialIndex';
    case UNIQUE         = 'unique';

    /**
     * Get an index type from a case-insensitive method name.
     */
    public static function tryFromString(string $value): ?self
    {
        foreach (self::cases() as $indexType) {
            if (strtolower($indexType->value) === trim(strtolower($value))) {
                return $indexType;
            }
        }

        return null;
    }

    /**
     * Get index types to skip from the `--skip-indexes` option values.
     *
     * A bare option skips all secondary indexes. A supplied list skips only
     * the listed types.
     *
     * @param  array<int, bool|string|null>  $values
     * @return self[]
     */
    public static function parseSkipIndexes(array $values): array
    {
        if ($values === []) {
            return [];
        }

        if (in_array(null, $values, true) || in_array(true, $values, true)) {
            return array_values(array_filter(
                self::cases(),
                static fn (self $indexType) => $indexType !== self::PRIMARY,
            ));
        }

        $indexTypes = [];

        foreach ($values as $value) {
            foreach (explode(',', (string) $value) as $type) {
                if (trim($type) === '') {
                    continue;
                }

                $indexType = self::tryFromString($type);

                if ($indexType === null) {
                    throw new InvalidArgumentException('Unknown index type: ' . trim($type));
                }

                $indexTypes[$indexType->name] = $indexType;
            }
        }

        return array_values($indexTypes);
    }
}

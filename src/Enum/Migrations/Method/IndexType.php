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
        $value = strtolower(trim($value));

        foreach (self::cases() as $indexType) {
            if (strtolower($indexType->value) === $value) {
                return $indexType;
            }
        }

        return null;
    }

    /**
     * @return self[]
     */
    public static function getSecondaryIndexes(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $indexType) => $indexType !== self::PRIMARY,
        ));
    }

    /**
     * Parse `--skip-indexes` option values.
     *
     * @param  array<int, bool|string|null>  $values
     * @return self[]
     */
    public static function parseValues(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $skipAllSecondaryIndexes = in_array(null, $values, true)
            || in_array(true, $values, true);

        if ($skipAllSecondaryIndexes) {
            return self::getSecondaryIndexes();
        }

        $types = array_merge(...array_map(
            static fn ($value) => explode(',', (string) $value),
            $values,
        ));

        return array_map(
            static function (string $type): self {
                $indexType = self::tryFromString($type);

                if ($indexType === null) {
                    throw new InvalidArgumentException('Unknown index type: ' . trim($type));
                }

                return $indexType;
            },
            array_values(array_filter(
                $types,
                static fn (string $type) => trim($type) !== '',
            )),
        );
    }
}

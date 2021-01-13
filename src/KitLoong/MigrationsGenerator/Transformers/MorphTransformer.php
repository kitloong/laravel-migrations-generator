<?php

namespace KitLoong\MigrationsGenerator\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationMethod\IndexType;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class MorphTransformer
{
    public function transformFields(array $fieldDefinitions): array
    {
        $fieldDefCollection = new Collection($fieldDefinitions);
        $probableMorphs = $this->probableMorphs($fieldDefCollection);
        if ($probableMorphs->isEmpty()) {
            return $fieldDefinitions;
        }

        $morphs = $this->morphs($fieldDefCollection, $probableMorphs);
        $fieldDefCollection = $this->replaceMorphs($fieldDefCollection, $morphs, 'morphs');

        $nullableMorphs = $this->nullableMorphs($fieldDefCollection, $probableMorphs);
        $fieldDefCollection = $this->replaceMorphs($fieldDefCollection, $nullableMorphs, 'nullableMorphs');

        return $fieldDefCollection->values()->all();
    }

    protected function probableMorphs(Collection $fieldDefCollection): Collection
    {
        return $fieldDefCollection
            ->pluck('field')
            ->map(function ($fieldName) {
                if (!is_string($fieldName)) {
                    return null;
                }
                if (!preg_match('~^(\w+)_type$~', $fieldName, $matches)) {
                    return null;
                }
                return $matches[1];
            })
            ->filter();
    }

    protected function morphs(Collection $fieldDefCollection, Collection $probableMorphs): Collection
    {
        return $probableMorphs
            ->map(function ($field) use ($fieldDefCollection) {
                $idFieldKey = $fieldDefCollection
                    ->where('field', $field . '_id')
                    ->whereIn('type', [ColumnType::INTEGER, ColumnType::BIG_INTEGER])
                    ->where('args', [])
                    ->where('decorators', [])
                    ->keys()
                    ->first();
                $typeFieldKey = $fieldDefCollection
                    ->where('field', $field . '_type')
                    ->where('type', DBALTypes::STRING)
                    ->where('args', [])
                    ->where('decorators', [])
                    ->keys()
                    ->first();
                $indexKey = $fieldDefCollection
                    ->where('field.0', $field . '_type')
                    ->where('field.1', $field . '_id')
                    ->where('type', IndexType::INDEX)
                    ->where('args', [])
                    ->keys()
                    ->first();
                if ($idFieldKey !== null && $typeFieldKey !== null && $indexKey !== null) {
                    return [
                        'name' => $field,
                        'id' => $idFieldKey,
                        'type' => $typeFieldKey,
                        'index' => $indexKey,
                    ];
                }
                return null;
            })
            ->filter();
    }

    protected function nullableMorphs(Collection $fieldDefCollection, Collection $probableMorphs): Collection
    {
        return $probableMorphs
            ->map(function ($field) use ($fieldDefCollection) {
                $idFieldKey = $fieldDefCollection
                    ->where('field', $field . '_id')
                    ->whereIn('type', [ColumnType::INTEGER, ColumnType::BIG_INTEGER])
                    ->where('args', [])
                    ->where('decorators', ['nullable'])
                    ->keys()
                    ->first();
                $typeFieldKey = $fieldDefCollection
                    ->where('field', $field . '_type')
                    ->where('type', DBALTypes::STRING)
                    ->where('args', [])
                    ->where('decorators', ['nullable'])
                    ->keys()
                    ->first();
                $indexKey = $fieldDefCollection
                    ->where('field.0', $field . '_type')
                    ->where('field.1', $field . '_id')
                    ->where('type', IndexType::INDEX)
                    ->where('args', [])
                    ->keys()
                    ->first();
                if ($idFieldKey !== null && $typeFieldKey !== null && $indexKey !== null) {
                    return [
                        'name' => $field,
                        'type' => $typeFieldKey,
                        'id' => $idFieldKey,
                        'index' => $indexKey,
                    ];
                }
                return null;
            })
            ->filter();
    }

    protected function replaceMorphs(Collection $fieldDefCollection, Collection $morphs, string $type): Collection
    {
        $fieldDefCollection = clone $fieldDefCollection;
        $morphs->each(function ($morph) use (&$fieldDefCollection, $type) {
            $fieldDefCollection->forget(Arr::only($morph, ['type', 'id', 'index']));
            $fieldDefCollection->put($morph['type'], [
                'field' => $morph['name'],
                'type' => $type,
                'args' => [],
                'decorators' => [],
            ]);
        });
        return $fieldDefCollection->sortKeys()->values();
    }
}

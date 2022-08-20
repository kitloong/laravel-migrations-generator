<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

use Illuminate\Support\Collection;

interface Table extends Model
{
    /**
     * Get the table name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get a list of columns.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\Column>
     */
    public function getColumns(): Collection;

    /**
     * Get a list of custom columns.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\CustomColumn>
     */
    public function getCustomColumns(): Collection;

    /**
     * Get a list of indexes.
     *
     * @return \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\Index>
     */
    public function getIndexes(): Collection;

    /**
     * Get the table collation.
     *
     * @return string|null
     */
    public function getCollation(): ?string;
}

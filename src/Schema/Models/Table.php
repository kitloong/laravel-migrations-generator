<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

use Illuminate\Support\Collection;

interface Table extends Model
{
    /**
     * Get the table name.
     */
    public function getName(): string;

    /**
     * Get the table comment.
     */
    public function getComment(): ?string;

    /**
     * Get a list of columns.
     *
     * @return \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Column>
     */
    public function getColumns(): Collection;

    /**
     * Get a list of user-defined type columns.
     *
     * @return \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\UDTColumn>
     */
    public function getUdtColumns(): Collection;

    /**
     * Get a list of indexes.
     *
     * @return \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Index>
     */
    public function getIndexes(): Collection;

    /**
     * Get the table collation.
     */
    public function getCollation(): ?string;
}

<?php

namespace KitLoong\MigrationsGenerator\Support;

trait AssetNameQuote
{
    /**
     * Checks if this identifier is quoted.
     *
     * @see \Doctrine\DBAL\Schema\AbstractAsset::isIdentifierQuoted()
     */
    public function isIdentifierQuoted(string $identifier): bool
    {
        return isset($identifier[0]) && ($identifier[0] === '`' || $identifier[0] === '"' || $identifier[0] === '[');
    }

    /**
     * Trim quotes from the identifier.
     *
     * @see \Doctrine\DBAL\Schema\AbstractAsset::trimQuotes()
     */
    public function trimQuotes(string $identifier): string
    {
        return str_replace(['`', '"', '[', ']'], '', $identifier);
    }
}

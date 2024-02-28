<?php

namespace KitLoong\MigrationsGenerator\Repositories;

abstract class Repository
{
    /**
     * Quotes a literal string.
     * This method is NOT meant to fix SQL injections!
     * It is only meant to escape this platform's string literal
     * quote character inside the given literal string.
     *
     * @param  string  $str  The literal string to be quoted.
     * @return string The quoted literal string.
     */
    protected function quoteStringLiteral(string $str): string
    {
        $c = $this->getStringLiteralQuoteCharacter();

        return $c . str_replace($c, $c . $c, $str) . $c;
    }

    /**
     * Gets the character used for string literal quoting.
     */
    protected function getStringLiteralQuoteCharacter(): string
    {
        return "'";
    }
}

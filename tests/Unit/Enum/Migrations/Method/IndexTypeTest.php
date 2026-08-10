<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Enum\Migrations\Method;

use InvalidArgumentException;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use PHPUnit\Framework\TestCase;

class IndexTypeTest extends TestCase
{
    public function testTryFromString(): void
    {
        $this->assertSame(IndexType::FULLTEXT, IndexType::tryFromString('fulltext'));
        $this->assertSame(IndexType::INDEX, IndexType::tryFromString('index'));
        $this->assertSame(IndexType::PRIMARY, IndexType::tryFromString(' primary'));
        $this->assertSame(IndexType::SPATIAL_INDEX, IndexType::tryFromString('SPATIALINDEX '));
        $this->assertSame(IndexType::UNIQUE, IndexType::tryFromString(' Unique '));
        $this->assertNull(IndexType::tryFromString('invalid'));
    }

    public function testGetSecondaryIndexes(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                IndexType::FULLTEXT,
                IndexType::FULLTEXT_CHAIN,
                IndexType::INDEX,
                IndexType::SPATIAL_INDEX,
                IndexType::UNIQUE,
            ],
            IndexType::getSecondaryIndexes(),
        );
    }

    public function testParseValuesWithoutValue(): void
    {
        $this->assertEqualsCanonicalizing(
            IndexType::getSecondaryIndexes(),
            IndexType::parseValues([null]),
        );

        $this->assertEqualsCanonicalizing(
            IndexType::getSecondaryIndexes(),
            IndexType::parseValues([true]),
        );
    }

    public function testParseValuesWithoutValues(): void
    {
        $this->assertSame([], IndexType::parseValues([]));
    }

    public function testParseValuesWithSpecificTypes(): void
    {
        $this->assertSame(
            [IndexType::FULLTEXT, IndexType::PRIMARY, IndexType::UNIQUE],
            IndexType::parseValues(['fulltext,primary,unique']),
        );
    }

    public function testParseValuesWithMultipleOptionValues(): void
    {
        $this->assertSame(
            [IndexType::INDEX, IndexType::UNIQUE],
            IndexType::parseValues(['index', 'unique']),
        );
    }

    public function testParseValuesIgnoresEmptyTypes(): void
    {
        $this->assertSame(
            [IndexType::INDEX, IndexType::UNIQUE],
            IndexType::parseValues(['index,,unique']),
        );
    }

    public function testParseValuesThrowsExceptionForUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        IndexType::parseValues(['invalid']);
    }
}

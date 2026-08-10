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

    public function testParseSkipIndexesWithoutValue(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                IndexType::FULLTEXT,
                IndexType::FULLTEXT_CHAIN,
                IndexType::INDEX,
                IndexType::SPATIAL_INDEX,
                IndexType::UNIQUE,
            ],
            IndexType::parseSkipIndexes([null]),
        );
    }

    public function testParseSkipIndexesWithSpecificTypes(): void
    {
        $this->assertSame(
            [IndexType::FULLTEXT, IndexType::PRIMARY, IndexType::UNIQUE],
            IndexType::parseSkipIndexes(['fulltext,primary,unique']),
        );
    }

    public function testParseSkipIndexesIgnoresEmptyTypes(): void
    {
        $this->assertSame(
            [IndexType::INDEX, IndexType::UNIQUE],
            IndexType::parseSkipIndexes(['index,,unique']),
        );
    }

    public function testParseSkipIndexesWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        IndexType::parseSkipIndexes(['invalid']);
    }
}

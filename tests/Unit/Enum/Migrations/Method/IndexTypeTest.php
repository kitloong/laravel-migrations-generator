<?php

namespace KitLoong\MigrationsGenerator\Tests\Unit\Enum\Migrations\Method;

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
}

<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Indexer;

use Iranimij\Badger\Model\Indexer\BadgerIndexer;
use Iranimij\Badger\Model\Indexer\CacheContext;
use Iranimij\Badger\Model\Indexer\IndexBuilder;
use PHPUnit\Framework\TestCase;

class BadgerIndexerTest extends TestCase
{
    public function testExecuteFullDelegatesToBuilder(): void
    {
        $builder = $this->createMock(IndexBuilder::class);
        $builder->expects(self::once())->method('rebuildAll');

        $cache = $this->createMock(CacheContext::class);
        $cache->expects(self::never())->method('registerProducts');

        (new BadgerIndexer($builder, $cache))->executeFull();
    }

    public function testExecuteRowSendsSingleId(): void
    {
        $builder = $this->createMock(IndexBuilder::class);
        $builder->expects(self::once())->method('rebuildForProducts')->with([7]);

        $cache = $this->createMock(CacheContext::class);
        $cache->expects(self::once())->method('registerProducts')->with([7]);

        (new BadgerIndexer($builder, $cache))->executeRow(7);
    }

    public function testExecuteListDeduplicates(): void
    {
        $builder = $this->createMock(IndexBuilder::class);
        $builder->expects(self::once())->method('rebuildForProducts')->with([1, 2]);

        $cache = $this->createMock(CacheContext::class);
        $cache->expects(self::once())->method('registerProducts')->with([1, 2]);

        (new BadgerIndexer($builder, $cache))->executeList([1, 2, 1]);
    }

    public function testEmptyListIsNoop(): void
    {
        $builder = $this->createMock(IndexBuilder::class);
        $builder->expects(self::never())->method('rebuildForProducts');
        $cache = $this->createMock(CacheContext::class);
        $cache->expects(self::never())->method('registerProducts');
        (new BadgerIndexer($builder, $cache))->executeList([]);
    }
}

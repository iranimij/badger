<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Api;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class BadgerRepositoryApiContractTest extends TestCase
{
    public function testInterfaceExposesCrudAndSearch(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m): string => $m->getName(),
            (new ReflectionClass(BadgerRepositoryInterface::class))->getMethods()
        );
        sort($methods);
        self::assertSame(['deleteById', 'duplicate', 'getById', 'getList', 'save'], $methods);
    }

    public function testSaveReturnsBadger(): void
    {
        $rc = new ReflectionClass(BadgerRepositoryInterface::class);
        $type = $rc->getMethod('save')->getReturnType();
        self::assertInstanceOf(ReflectionNamedType::class, $type);
        self::assertSame(BadgerInterface::class, $type->getName());
    }

    public function testGetListReturnsSearchResults(): void
    {
        $rc = new ReflectionClass(BadgerRepositoryInterface::class);
        $type = $rc->getMethod('getList')->getReturnType();
        self::assertInstanceOf(ReflectionNamedType::class, $type);
        self::assertSame(BadgerSearchResultsInterface::class, $type->getName());
    }

    public function testDeleteByIdReturnsBool(): void
    {
        $rc = new ReflectionClass(BadgerRepositoryInterface::class);
        $type = $rc->getMethod('deleteById')->getReturnType();
        self::assertInstanceOf(ReflectionNamedType::class, $type);
        self::assertSame('bool', $type->getName());
    }
}

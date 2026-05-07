<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\Factory\AttributeEqualsFactory;
use Iranimij\Badger\Model\Evaluation\Factory\OnSaleFactory;
use Iranimij\Badger\Model\Evaluation\SpecificationRegistry;
use PHPUnit\Framework\TestCase;

class SpecificationRegistryTest extends TestCase
{
    public function testRegisterAndGet(): void
    {
        $factory = new AttributeEqualsFactory();
        $registry = new SpecificationRegistry(['attr_eq' => $factory]);
        self::assertTrue($registry->has('attr_eq'));
        self::assertSame($factory, $registry->get('attr_eq'));
        self::assertSame(['attr_eq'], $registry->tokens());
    }

    public function testUnknownTokenThrows(): void
    {
        $registry = new SpecificationRegistry([]);
        $this->expectException(\OutOfBoundsException::class);
        $registry->get('missing');
    }

    public function testDuplicateTokenRejected(): void
    {
        $registry = new SpecificationRegistry(['attr_eq' => new AttributeEqualsFactory()]);
        $this->expectException(\LogicException::class);
        $registry->register('attr_eq', new OnSaleFactory());
    }
}

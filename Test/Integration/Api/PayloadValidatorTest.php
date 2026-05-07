<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Api;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Api\BadgerPayloadValidator;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class PayloadValidatorTest extends TestCase
{
    private BadgerPayloadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = Bootstrap::getObjectManager()->get(BadgerPayloadValidator::class);
    }

    public function testValidBadgerPassesValidation(): void
    {
        $badger = $this->makeBadger('My Badge', 10);
        $this->validator->validate($badger);
        self::assertTrue(true);
    }

    public function testEmptyNameThrows(): void
    {
        $this->expectException(InputException::class);
        $badger = $this->makeBadger('', 0);
        $this->validator->validate($badger);
    }

    public function testNegativePriorityThrows(): void
    {
        $this->expectException(InputException::class);
        $badger = $this->makeBadger('Valid Name', -1);
        $this->validator->validate($badger);
    }

    public function testInvalidActiveDatesThrows(): void
    {
        $this->expectException(InputException::class);
        $badger = $this->makeBadger('Valid Name', 0);
        $badger->setActiveFrom('not-a-date');
        $this->validator->validate($badger);
    }

    public function testActiveToBforeFromThrows(): void
    {
        $this->expectException(InputException::class);
        $badger = $this->makeBadger('Valid Name', 0);
        $badger->setActiveFrom('2026-06-01 00:00:00');
        $badger->setActiveTo('2026-01-01 00:00:00');
        $this->validator->validate($badger);
    }

    public function testInvalidSurfaceOnVisualThrows(): void
    {
        $this->expectException(InputException::class);

        $visual = Bootstrap::getObjectManager()->create(VisualInterface::class);
        $visual->setSurface(999); // invalid
        $visual->setPlacement(1);
        $visual->setShapeKind(1);
        $visual->setSizePercent(25);

        $badger = $this->makeBadger('Valid Name', 0);
        $badger->setVisuals([$visual]);
        $this->validator->validate($badger);
    }

    public function testSizePercentOutOfRangeThrows(): void
    {
        $this->expectException(InputException::class);

        $visual = Bootstrap::getObjectManager()->create(VisualInterface::class);
        $visual->setSurface(1);
        $visual->setPlacement(1);
        $visual->setShapeKind(1);
        $visual->setSizePercent(0); // below minimum

        $badger = $this->makeBadger('Valid Name', 0);
        $badger->setVisuals([$visual]);
        $this->validator->validate($badger);
    }

    public function testNameTooLongThrows(): void
    {
        $this->expectException(InputException::class);
        $badger = $this->makeBadger(str_repeat('a', 256), 0);
        $this->validator->validate($badger);
    }

    private function makeBadger(string $name, int $priority): BadgerInterface
    {
        $badger = Bootstrap::getObjectManager()->create(BadgerInterface::class);
        $badger->setName($name);
        $badger->setIsEnabled(true);
        $badger->setPriority($priority);
        $badger->setIsExclusive(false);
        $badger->setUseForParent(false);
        $badger->setStoreIds([]);
        $badger->setCustomerGroupIds([]);
        $badger->setVisuals([]);
        return $badger;
    }
}

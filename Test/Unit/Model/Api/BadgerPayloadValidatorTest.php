<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Api;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Api\BadgerPayloadValidator;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\TestCase;

class BadgerPayloadValidatorTest extends TestCase
{
    public function testAcceptsValidPayload(): void
    {
        $visual = $this->createMock(VisualInterface::class);
        $visual->method('getSurface')->willReturn(0);
        $visual->method('getPlacement')->willReturn(0);
        $visual->method('getShapeKind')->willReturn(2);
        $visual->method('getSizePercent')->willReturn(25);

        $badger = $this->makeBadger('Sale', 0, null, null, [$visual]);
        (new BadgerPayloadValidator())->validate($badger);
        self::assertTrue(true);
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InputException::class);
        (new BadgerPayloadValidator())->validate($this->makeBadger('   '));
    }

    public function testRejectsNegativePriority(): void
    {
        $this->expectException(InputException::class);
        (new BadgerPayloadValidator())->validate($this->makeBadger('B', -1));
    }

    public function testRejectsToBeforeFrom(): void
    {
        $this->expectException(InputException::class);
        (new BadgerPayloadValidator())->validate($this->makeBadger('B', 0, '2026-04-24 12:00:00', '2026-04-24 10:00:00'));
    }

    public function testRejectsUnknownSurface(): void
    {
        $visual = $this->createMock(VisualInterface::class);
        $visual->method('getSurface')->willReturn(999);
        $visual->method('getPlacement')->willReturn(0);
        $visual->method('getShapeKind')->willReturn(0);
        $visual->method('getSizePercent')->willReturn(25);

        $this->expectException(InputException::class);
        (new BadgerPayloadValidator())->validate($this->makeBadger('B', 0, null, null, [$visual]));
    }

    public function testRejectsOutOfRangeSize(): void
    {
        $visual = $this->createMock(VisualInterface::class);
        $visual->method('getSurface')->willReturn(0);
        $visual->method('getPlacement')->willReturn(0);
        $visual->method('getShapeKind')->willReturn(0);
        $visual->method('getSizePercent')->willReturn(200);

        $this->expectException(InputException::class);
        (new BadgerPayloadValidator())->validate($this->makeBadger('B', 0, null, null, [$visual]));
    }

    /**
     * @param VisualInterface[] $visuals
     */
    private function makeBadger(string $name, int $priority = 0, ?string $from = null, ?string $to = null, array $visuals = []): BadgerInterface
    {
        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getName')->willReturn($name);
        $badger->method('getPriority')->willReturn($priority);
        $badger->method('getActiveFrom')->willReturn($from);
        $badger->method('getActiveTo')->willReturn($to);
        $badger->method('getVisuals')->willReturn($visuals);
        return $badger;
    }
}

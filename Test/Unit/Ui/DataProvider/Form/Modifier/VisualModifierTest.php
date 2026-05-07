<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Ui\DataProvider\Form\Modifier\VisualModifier;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use PHPUnit\Framework\TestCase;

class VisualModifierTest extends TestCase
{
    private JsonSerializer $json;

    protected function setUp(): void
    {
        $this->json = $this->createMock(JsonSerializer::class);
    }

    public function testDecodesStylePayload(): void
    {
        $this->json->method('decode')->with('{"color":"red"}')->willReturn(['color' => 'red']);
        $modifier = new VisualModifier($this->json);
        $data = $modifier->modifyData([
            1 => ['visuals' => [['style_payload' => '{"color":"red"}']]],
        ]);
        self::assertSame(['color' => 'red'], $data[1]['visuals'][0]['style']);
    }

    public function testDecodeErrorFallsBackToEmpty(): void
    {
        $this->json->method('decode')->willThrowException(new \RuntimeException('bad'));
        $modifier = new VisualModifier($this->json);
        $data = $modifier->modifyData([
            1 => ['visuals' => [['style_payload' => 'nope']]],
        ]);
        self::assertSame([], $data[1]['visuals'][0]['style']);
    }

    public function testEmptyStyleYieldsEmptyArray(): void
    {
        $modifier = new VisualModifier($this->json);
        $data = $modifier->modifyData([1 => ['visuals' => [[]]]]);
        self::assertSame([], $data[1]['visuals'][0]['style']);
    }

    public function testHandlesMissingVisuals(): void
    {
        $modifier = new VisualModifier($this->json);
        $data = $modifier->modifyData([1 => ['name' => 'x']]);
        self::assertSame(['name' => 'x'], $data[1]);
    }

    public function testMetaPassthrough(): void
    {
        $modifier = new VisualModifier($this->json);
        self::assertSame(['a' => 1], $modifier->modifyMeta(['a' => 1]));
    }
}

<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\ConditionsTreeBuilder;
use Iranimij\Badger\Model\Evaluation\ConditionsTreeSerializer;
use Iranimij\Badger\Model\Evaluation\Factory\AttributeEqualsFactory;
use Iranimij\Badger\Model\Evaluation\SpecificationRegistry;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class ConditionsTreeSerializerTest extends TestCase
{
    private ConditionsTreeSerializer $serializer;

    protected function setUp(): void
    {
        $registry = new SpecificationRegistry(['attr_eq' => new AttributeEqualsFactory()]);
        $builder = new ConditionsTreeBuilder($registry);
        $json = new JsonSerializer(new Json());
        $this->serializer = new ConditionsTreeSerializer($json, $builder);
    }

    public function testDecodeEmptyReturnsEmptyAnd(): void
    {
        $tree = $this->serializer->decode('');
        self::assertSame('and', $tree['op']);
        self::assertSame([], $tree['children']);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $tree = [
            'op' => 'or',
            'children' => [
                ['token' => 'attr_eq', 'args' => ['attribute' => 'color', 'value' => 'red']],
            ],
        ];
        $payload = $this->serializer->encode($tree);
        self::assertSame($tree, $this->serializer->decode($payload));
    }

    public function testToSpecificationBuildsFromJson(): void
    {
        $payload = $this->serializer->encode([
            'op' => 'and',
            'children' => [
                ['token' => 'attr_eq', 'args' => ['attribute' => 'color', 'value' => 'red']],
            ],
        ]);
        $spec = $this->serializer->toSpecification($payload);
        self::assertNotNull($spec);
    }

    public function testMalformedPayloadFallsBackToEmptyTree(): void
    {
        $tree = $this->serializer->decode('not-json');
        self::assertSame('and', $tree['op']);
        self::assertSame([], $tree['children']);
    }
}

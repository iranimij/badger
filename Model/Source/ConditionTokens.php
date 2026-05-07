<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Iranimij\Badger\Model\Evaluation\SpecificationRegistry;
use Magento\Framework\Data\OptionSourceInterface;

class ConditionTokens implements OptionSourceInterface
{
    private const LABELS = [
        'attr_eq' => 'Attribute equals',
        'attr_in' => 'Attribute is one of',
        'attr_range' => 'Attribute in range',
        'on_sale' => 'Product is on sale',
        'is_new' => 'Product is new',
        'stock_status' => 'Stock status',
        'qty_range' => 'Quantity in range',
        'customer_group' => 'Customer group',
        'store' => 'Store view',
        'category_in' => 'Belongs to category',
        'price_range' => 'Price in range',
        'bestseller' => 'Bestseller',
    ];

    public function __construct(private readonly SpecificationRegistry $registry)
    {
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->registry->tokens() as $token) {
            $options[] = [
                'value' => $token,
                'label' => self::LABELS[$token] ?? $token,
            ];
        }
        usort($options, static fn ($a, $b) => strcmp($a['label'], $b['label']));
        return $options;
    }
}

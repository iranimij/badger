<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class PriceRangeSpec implements SpecificationInterface
{
    public const TOKEN = 'price_range';

    public const FIELD_FINAL = 'final_price';
    public const FIELD_REGULAR = 'regular_price';

    public function __construct(
        private readonly ?float $min,
        private readonly ?float $max,
        private readonly string $field = self::FIELD_FINAL
    ) {
        if ($field !== self::FIELD_FINAL && $field !== self::FIELD_REGULAR) {
            throw new \InvalidArgumentException("Unsupported price field: $field");
        }
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $product = $ctx->product;
        $price = null;

        if ($this->field === self::FIELD_REGULAR) {
            $price = $product->getData('price');
        } else {
            $special = $product->getData('special_price');
            $price = ($special !== null && $special !== '' && is_numeric($special) && (float) $special > 0.0)
                ? (float) $special
                : $product->getData('price');
        }

        if (!is_numeric($price)) {
            return false;
        }
        $n = (float) $price;
        if ($this->min !== null && $n < $this->min) {
            return false;
        }
        if ($this->max !== null && $n > $this->max) {
            return false;
        }
        return true;
    }
}

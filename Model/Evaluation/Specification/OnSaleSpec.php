<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class OnSaleSpec implements SpecificationInterface
{
    public const TOKEN = 'on_sale';

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $product = $ctx->product;
        $price = (float) $product->getData('price');
        $special = $product->getData('special_price');

        if ($special === null || $special === '' || !is_numeric($special)) {
            return false;
        }
        $specialPrice = (float) $special;
        if ($specialPrice <= 0.0 || $specialPrice >= $price) {
            return false;
        }

        $now = time();
        $from = $product->getData('special_from_date');
        $to = $product->getData('special_to_date');
        if ($from && strtotime((string) $from) > $now) {
            return false;
        }
        if ($to && strtotime((string) $to) < $now) {
            return false;
        }
        return true;
    }
}

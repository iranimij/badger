<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class CategoryMembershipSpec implements SpecificationInterface
{
    public const TOKEN = 'category_in';

    public const MODE_ANY = 'any';
    public const MODE_ALL = 'all';

    /**
     * @param list<int> $categoryIds
     */
    public function __construct(
        private readonly array $categoryIds,
        private readonly string $mode = self::MODE_ANY
    ) {
        if ($mode !== self::MODE_ANY && $mode !== self::MODE_ALL) {
            throw new \InvalidArgumentException("Unsupported mode: $mode");
        }
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $productCats = $ctx->product->getData('category_ids');
        if ($productCats === null) {
            $getter = [$ctx->product, 'getCategoryIds'];
            $productCats = is_callable($getter) ? $getter() : [];
        }
        if (!is_array($productCats)) {
            return false;
        }
        $productCats = array_map('intval', $productCats);
        $wanted = array_map('intval', $this->categoryIds);

        if ($wanted === []) {
            return false;
        }

        if ($this->mode === self::MODE_ALL) {
            foreach ($wanted as $cid) {
                if (!in_array($cid, $productCats, true)) {
                    return false;
                }
            }
            return true;
        }

        foreach ($wanted as $cid) {
            if (in_array($cid, $productCats, true)) {
                return true;
            }
        }
        return false;
    }
}

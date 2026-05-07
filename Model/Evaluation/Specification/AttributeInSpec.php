<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class AttributeInSpec implements SpecificationInterface
{
    public const TOKEN = 'attr_in';

    /**
     * @param list<scalar> $candidates
     */
    public function __construct(
        private readonly string $attributeCode,
        private readonly array $candidates
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $value = $ctx->product->getData($this->attributeCode);
        $normalized = array_map(static fn($v) => (string) $v, $this->candidates);

        if (is_array($value)) {
            foreach ($value as $v) {
                if (in_array((string) $v, $normalized, true)) {
                    return true;
                }
            }
            return false;
        }

        if (is_scalar($value)) {
            $asString = (string) $value;
            if (str_contains($asString, ',')) {
                foreach (explode(',', $asString) as $part) {
                    if (in_array(trim($part), $normalized, true)) {
                        return true;
                    }
                }
                return false;
            }
            return in_array($asString, $normalized, true);
        }

        return false;
    }
}

<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class IsNewSpec implements SpecificationInterface
{
    public const TOKEN = 'is_new';

    public function __construct(
        private readonly ?int $recencyDays = null
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $product = $ctx->product;
        $now = time();

        $newsFrom = $product->getData('news_from_date');
        $newsTo = $product->getData('news_to_date');
        if ($newsFrom || $newsTo) {
            if ($newsFrom && strtotime((string) $newsFrom) > $now) {
                return false;
            }
            if ($newsTo && strtotime((string) $newsTo) < $now) {
                return false;
            }
            return (bool) ($newsFrom || $newsTo);
        }

        if ($this->recencyDays !== null) {
            $created = $product->getData('created_at');
            if ($created) {
                $createdAt = strtotime((string) $created);
                if ($createdAt !== false) {
                    return ($now - $createdAt) <= $this->recencyDays * 86400;
                }
            }
        }

        return false;
    }
}

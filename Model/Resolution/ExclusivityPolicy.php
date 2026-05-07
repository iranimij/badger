<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Resolution;

use Iranimij\Badger\Model\ReadModel\ResolvedBadger;

class ExclusivityPolicy
{
    /**
     * @param ResolvedBadger[] $sorted
     * @return ResolvedBadger[]
     */
    public function apply(array $sorted): array
    {
        $kept = [];
        $blocked = [];
        foreach ($sorted as $b) {
            $key = $b->surface->value . ':' . $b->placement->value;
            if (isset($blocked[$key])) {
                continue;
            }
            $kept[] = $b;
            if ($b->isExclusive) {
                $blocked[$key] = true;
            }
        }
        return $kept;
    }
}

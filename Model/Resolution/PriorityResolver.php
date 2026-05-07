<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Resolution;

use Iranimij\Badger\Model\ReadModel\ResolvedBadger;

class PriorityResolver
{
    /**
     * @param ResolvedBadger[] $badgers
     * @return ResolvedBadger[]
     */
    public function sort(array $badgers): array
    {
        usort(
            $badgers,
            static function (ResolvedBadger $a, ResolvedBadger $b): int {
                $byPriority = $b->priority <=> $a->priority;
                if ($byPriority !== 0) {
                    return $byPriority;
                }
                return $a->badgerId <=> $b->badgerId;
            }
        );
        return $badgers;
    }
}

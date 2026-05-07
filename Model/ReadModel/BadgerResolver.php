<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ReadModel;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\Resolution\ExclusivityPolicy;
use Iranimij\Badger\Model\Resolution\PriorityResolver;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\TemplateRenderer;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\CacheInterface;

class BadgerResolver
{
    private const CACHE_ID_PREFIX = 'iranimij_badger_resolved_';

    public function __construct(
        private readonly BadgerRepositoryInterface $repository,
        private readonly RuleFactory $ruleFactory,
        private readonly JsonSerializer $jsonSerializer,
        private readonly TemplateRenderer $templateRenderer,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly FilterBuilder $filterBuilder,
        private readonly FilterGroupBuilder $filterGroupBuilder,
        private readonly CacheInterface $cache,
        private readonly PriorityResolver $priorityResolver,
        private readonly ExclusivityPolicy $exclusivityPolicy
    ) {
    }

    /**
     * @return ResolvedBadger[]
     */
    public function resolve(
        ProductInterface $product,
        Surface $surface,
        int $storeId,
        int $customerGroupId,
        float $qty = 1.0
    ): array {
        $now = time();
        $badgers = $this->loadEnabledBadgers();

        $resolved = [];
        foreach ($badgers as $badger) {
            if (!$this->matchesWindow($badger, $now)) {
                continue;
            }
            if (!$this->matchesStore($badger, $storeId)) {
                continue;
            }
            if (!$this->matchesGroup($badger, $customerGroupId)) {
                continue;
            }
            if (!$this->matchesConditions($badger, $product)) {
                continue;
            }
            foreach ($this->visualsForSurface($badger, $surface) as $visual) {
                $resolved[] = $this->hydrate($badger, $visual, $product);
            }
        }

        $sorted = $this->priorityResolver->sort($resolved);
        return $this->exclusivityPolicy->apply($sorted);
    }

    /**
     * @return BadgerInterface[]
     */
    private function loadEnabledBadgers(): array
    {
        $this->searchCriteriaBuilder->addFilter(BadgerInterface::IS_ENABLED, 1);
        $criteria = $this->searchCriteriaBuilder->create();
        $results = $this->repository->getList($criteria);
        return $results->getItems();
    }

    private function matchesWindow(BadgerInterface $badger, int $now): bool
    {
        $from = $badger->getActiveFrom();
        $to = $badger->getActiveTo();
        if ($from && strtotime($from) > $now) {
            return false;
        }
        if ($to && strtotime($to) < $now) {
            return false;
        }
        return true;
    }

    private function matchesStore(BadgerInterface $badger, int $storeId): bool
    {
        $stores = array_map('intval', $badger->getStoreIds());
        return $stores === []
            || in_array(0, $stores, true)
            || in_array($storeId, $stores, true);
    }

    private function matchesGroup(BadgerInterface $badger, int $groupId): bool
    {
        $groups = $badger->getCustomerGroupIds();
        return $groups === [] || in_array($groupId, array_map('intval', $groups), true);
    }

    private function matchesConditions(BadgerInterface $badger, ProductInterface $product): bool
    {
        $payload = $badger->getConditionsPayload();
        if ($payload === null || $payload === '') {
            return true;
        }
        try {
            $decoded = $this->jsonSerializer->decodeArray($payload);
            if ($decoded === []) {
                return true;
            }
            $rule = $this->ruleFactory->create();
            $rule->getConditions()->loadArray($decoded);
            return (bool) $rule->validate($product);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return VisualInterface[]
     */
    private function visualsForSurface(BadgerInterface $badger, Surface $surface): array
    {
        $matches = [];
        foreach ($badger->getVisuals() as $visual) {
            if ((int) $visual->getSurface() === $surface->value) {
                $matches[] = $visual;
            }
        }
        return $matches;
    }

    private function hydrate(BadgerInterface $badger, VisualInterface $visual, ProductInterface $product): ResolvedBadger
    {
        $placeholderCtx = new PlaceholderContext($product, $badger);
        $labelText = $visual->getLabelText() ?? '';
        $labelText = $this->templateRenderer->render($labelText, $placeholderCtx);
        $tooltip = $badger->getTooltip();
        $tooltipText = null;
        if ($tooltip && $tooltip->isEnabled()) {
            $tooltipText = $this->templateRenderer->render((string) ($tooltip->getBodyText() ?? ''), $placeholderCtx);
        }

        $badgeBgColor = null;
        $badgeTextColor = null;
        $stylePayload = $visual->getStylePayload();
        if ($stylePayload !== null && $stylePayload !== '') {
            try {
                $styleData = $this->jsonSerializer->decodeArray($stylePayload);
                $badgeBgColor   = $styleData['bg_color']   ?? null;
                $badgeTextColor = $styleData['text_color'] ?? null;
            } catch (\Throwable) {
            }
        }

        return new ResolvedBadger(
            badgerId: (int) $badger->getBadgerId(),
            name: $badger->getName(),
            priority: $badger->getPriority(),
            isExclusive: $badger->isExclusive(),
            surface: Surface::from((int) $visual->getSurface()),
            placement: Placement::from((int) $visual->getPlacement()),
            shape: ShapeKind::from((int) $visual->getShapeKind()),
            labelText: $labelText,
            imagePath: $visual->getImagePath(),
            redirectUrl: $visual->getRedirectUrl(),
            altText: $visual->getAltText(),
            cssClass: $visual->getCssClass(),
            sizePercent: (int) ($visual->getSizePercent() ?: 25),
            badgeBgColor: $badgeBgColor ?: null,
            badgeTextColor: $badgeTextColor ?: null,
            tooltipText: $tooltipText,
            tooltipBgColor: $tooltip?->getBackgroundColor(),
            tooltipTextColor: $tooltip?->getTextColor()
        );
    }

}

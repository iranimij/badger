<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Magento\Framework\ObjectManagerInterface;

class BadgerHydrator
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    public function fromArray(array $data): BadgerInterface
    {
        /** @var BadgerInterface $badger */
        $badger = $this->objectManager->create(BadgerInterface::class);
        $this->applyScalar($badger, $data);

        $visuals = [];
        foreach ((array) ($data[BadgerInterface::VISUALS] ?? []) as $visualData) {
            $visuals[] = $this->makeVisual((array) $visualData);
        }
        $badger->setVisuals($visuals);

        $tooltipData = $data[BadgerInterface::TOOLTIP] ?? null;
        if (is_array($tooltipData) && $tooltipData !== []) {
            $badger->setTooltip($this->makeTooltip($tooltipData));
        }

        return $badger;
    }

    public function toArray(BadgerInterface $badger): array
    {
        $data = [
            BadgerInterface::BADGER_ID => $badger->getBadgerId(),
            BadgerInterface::NAME => $badger->getName(),
            BadgerInterface::IS_ENABLED => $badger->isEnabled() ? '1' : '0',
            BadgerInterface::PRIORITY => $badger->getPriority(),
            BadgerInterface::IS_EXCLUSIVE => $badger->isExclusive() ? '1' : '0',
            BadgerInterface::USE_FOR_PARENT => $badger->getUseForParent() ? '1' : '0',
            BadgerInterface::ACTIVE_FROM => $badger->getActiveFrom(),
            BadgerInterface::ACTIVE_TO => $badger->getActiveTo(),
            BadgerInterface::CONDITIONS_PAYLOAD => $badger->getConditionsPayload(),
            BadgerInterface::STORE_IDS => $badger->getStoreIds(),
            BadgerInterface::CUSTOMER_GROUP_IDS => $badger->getCustomerGroupIds(),
            BadgerInterface::VISUALS => array_map(fn (VisualInterface $v) => $this->visualToArray($v), $badger->getVisuals()),
            BadgerInterface::TOOLTIP => $badger->getTooltip() ? $this->tooltipToArray($badger->getTooltip()) : null,
        ];

        return $data;
    }

    private function applyScalar(BadgerInterface $badger, array $data): void
    {
        if (array_key_exists(BadgerInterface::BADGER_ID, $data)) {
            $rawId = (int) $data[BadgerInterface::BADGER_ID];
            $badger->setBadgerId($rawId > 0 ? $rawId : null);
        }
        if (array_key_exists(BadgerInterface::NAME, $data)) {
            $badger->setName((string) $data[BadgerInterface::NAME]);
        }
        if (array_key_exists(BadgerInterface::IS_ENABLED, $data)) {
            $badger->setIsEnabled((bool) $data[BadgerInterface::IS_ENABLED]);
        }
        if (array_key_exists(BadgerInterface::PRIORITY, $data)) {
            $badger->setPriority((int) $data[BadgerInterface::PRIORITY]);
        }
        if (array_key_exists(BadgerInterface::IS_EXCLUSIVE, $data)) {
            $badger->setIsExclusive((bool) $data[BadgerInterface::IS_EXCLUSIVE]);
        }
        if (array_key_exists(BadgerInterface::USE_FOR_PARENT, $data)) {
            $badger->setUseForParent((bool) $data[BadgerInterface::USE_FOR_PARENT]);
        }
        if (array_key_exists(BadgerInterface::ACTIVE_FROM, $data)) {
            $badger->setActiveFrom($data[BadgerInterface::ACTIVE_FROM] === null ? null : (string) $data[BadgerInterface::ACTIVE_FROM]);
        }
        if (array_key_exists(BadgerInterface::ACTIVE_TO, $data)) {
            $badger->setActiveTo($data[BadgerInterface::ACTIVE_TO] === null ? null : (string) $data[BadgerInterface::ACTIVE_TO]);
        }
        if (array_key_exists(BadgerInterface::CONDITIONS_PAYLOAD, $data)) {
            $badger->setConditionsPayload($data[BadgerInterface::CONDITIONS_PAYLOAD] === null ? null : (string) $data[BadgerInterface::CONDITIONS_PAYLOAD]);
        }
        if (array_key_exists(BadgerInterface::STORE_IDS, $data) && is_array($data[BadgerInterface::STORE_IDS])) {
            $badger->setStoreIds($data[BadgerInterface::STORE_IDS]);
        }
        if (array_key_exists(BadgerInterface::CUSTOMER_GROUP_IDS, $data) && is_array($data[BadgerInterface::CUSTOMER_GROUP_IDS])) {
            $badger->setCustomerGroupIds($data[BadgerInterface::CUSTOMER_GROUP_IDS]);
        }
    }

    private function makeVisual(array $data): VisualInterface
    {
        /** @var VisualInterface $visual */
        $visual = $this->objectManager->create(VisualInterface::class);
        if (array_key_exists(VisualInterface::BADGER_ID, $data)) {
            $visual->setBadgerId($data[VisualInterface::BADGER_ID] === null ? null : (int) $data[VisualInterface::BADGER_ID]);
        }
        $visual->setSurface((int) ($data[VisualInterface::SURFACE] ?? 0));
        $visual->setShapeKind((int) ($data[VisualInterface::SHAPE_KIND] ?? 0));
        $visual->setPlacement((int) ($data[VisualInterface::PLACEMENT] ?? 0));
        $visual->setLabelText($data[VisualInterface::LABEL_TEXT] ?? null);
        $visual->setImagePath($data[VisualInterface::IMAGE_PATH] ?? null);
        $visual->setRedirectUrl($data[VisualInterface::REDIRECT_URL] ?? null);
        $visual->setAltText($data[VisualInterface::ALT_TEXT] ?? null);
        $visual->setSizePercent((int) ($data[VisualInterface::SIZE_PERCENT] ?? 25));
        $visual->setStylePayload($data[VisualInterface::STYLE_PAYLOAD] ?? null);
        return $visual;
    }

    private function makeTooltip(array $data): TooltipInterface
    {
        /** @var TooltipInterface $tooltip */
        $tooltip = $this->objectManager->create(TooltipInterface::class);
        if (array_key_exists(TooltipInterface::BADGER_ID, $data)) {
            $tooltip->setBadgerId($data[TooltipInterface::BADGER_ID] === null ? null : (int) $data[TooltipInterface::BADGER_ID]);
        }
        $tooltip->setIsEnabled((bool) ($data[TooltipInterface::IS_ENABLED] ?? true));
        $tooltip->setBodyText($data[TooltipInterface::BODY_TEXT] ?? null);
        $tooltip->setBackgroundColor($data[TooltipInterface::BACKGROUND_COLOR] ?? null);
        $tooltip->setTextColor($data[TooltipInterface::TEXT_COLOR] ?? null);
        return $tooltip;
    }

    private function visualToArray(VisualInterface $v): array
    {
        return [
            VisualInterface::BADGER_ID => $v->getBadgerId(),
            VisualInterface::SURFACE => $v->getSurface(),
            VisualInterface::SHAPE_KIND => $v->getShapeKind(),
            VisualInterface::PLACEMENT => $v->getPlacement(),
            VisualInterface::LABEL_TEXT => $v->getLabelText(),
            VisualInterface::IMAGE_PATH => $v->getImagePath(),
            VisualInterface::REDIRECT_URL => $v->getRedirectUrl(),
            VisualInterface::ALT_TEXT => $v->getAltText(),
            VisualInterface::SIZE_PERCENT => $v->getSizePercent(),
            VisualInterface::STYLE_PAYLOAD => $v->getStylePayload(),
        ];
    }

    private function tooltipToArray(TooltipInterface $t): array
    {
        return [
            TooltipInterface::BADGER_ID => $t->getBadgerId(),
            TooltipInterface::IS_ENABLED => $t->isEnabled() ? '1' : '0',
            TooltipInterface::BODY_TEXT => $t->getBodyText(),
            TooltipInterface::BACKGROUND_COLOR => $t->getBackgroundColor(),
            TooltipInterface::TEXT_COLOR => $t->getTextColor(),
        ];
    }
}

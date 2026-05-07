<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Rule\Model\Condition\Combine;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ConditionsModifier implements ModifierInterface
{
    public const FORM_NAME              = 'iranimij_badger_conditions';
    public const CONDITIONS_FIELDSET_ID = 'iranimij_badger_conditions_fieldset';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly BadgerRepositoryInterface $repository,
        private readonly RuleFactory $ruleFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly JsonSerializer $jsonSerializer,
    ) {
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $rule           = $this->prepareRuleModel();
        $newFormChildUrl = $this->urlBuilder->getUrl(
            'badger/badger/newConditionHtml',
            ['form_namespace' => self::FORM_NAME]
        );

        try {
            $conditionsHtml = $rule->getConditions()->asHtmlRecursive();
        } catch (\Exception) {
            $conditionsHtml = '';
        }

        $existing = $meta['conditions']['children']['conditions_payload']['arguments']['data']['config'] ?? [];
        $meta['conditions']['children']['conditions_payload']['arguments']['data']['config'] = array_merge(
            $existing,
            [
                'formContent'     => $conditionsHtml,
                'newFormChildUrl' => $newFormChildUrl,
                'conditionsFormId' => self::CONDITIONS_FIELDSET_ID,
            ]
        );

        return $meta;
    }

    private function prepareRuleModel(): \Iranimij\Badger\Model\Rule
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditions([]);

        $id = (int) $this->request->getParam('badger_id');
        if ($id > 0) {
            try {
                $badger           = $this->repository->getById($id);
                $conditionsPayload = $badger->getConditionsPayload();
                if ($conditionsPayload) {
                    $decoded = $this->jsonSerializer->decode($conditionsPayload);
                    if (is_array($decoded) && isset($decoded['type'])) {
                        $rule->getConditions()->loadArray($decoded);
                    }
                }
            } catch (NoSuchEntityException) {
                // leave rule with empty conditions
            }
        }

        $fieldsetId = self::CONDITIONS_FIELDSET_ID;
        $rule->getConditions()->setJsFormObject($fieldsetId);
        $rule->getConditions()->setFormName(self::FORM_NAME);
        $this->propagateFormName($rule->getConditions()->getConditions() ?: []);

        return $rule;
    }

    /** @param Combine[] $conditions */
    private function propagateFormName(array $conditions): void
    {
        foreach ($conditions as $condition) {
            $condition->setFormName(self::FORM_NAME);
            $this->propagateFormName($condition->getConditions() ?: []);
        }
    }
}

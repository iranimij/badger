<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends AbstractAction implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly BadgerRepositoryInterface $repository,
        private readonly BadgerHydrator $hydrator,
        private readonly JsonSerializer $jsonSerializer,
        private readonly RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        if (!is_array($data) || $data === []) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        }

        $isAjax = (bool) $this->getRequest()->getParam('isAjax');

        try {
            $payload = $this->normalize($data);
            $badger = $this->hydrator->fromArray($payload);
            $saved = $this->repository->save($badger);
            $this->messageManager->addSuccessMessage(__('Badger saved.'));

            $backParam = $this->getRequest()->getParam('back');
            if ($backParam === 'edit') {
                $editParams = ['badger_id' => (int) $saved->getBadgerId()];
                if ($isAjax) {
                    return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                        ->setData(['redirect' => $this->getUrl('*/*/edit', $editParams)]);
                }
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                    ->setPath('*/*/edit', $editParams);
            }

            if ($isAjax) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                    ->setData(['redirect' => $this->getUrl('*/*/')]);
            }
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not save badger: %1', $e->getMessage()));
        }

        $id = (int) ($data[BadgerInterface::BADGER_ID] ?? 0);
        $errorParams = $id > 0 ? ['badger_id' => $id] : [];
        $errorPath = $id > 0 ? '*/*/edit' : '*/*/new';

        if ($isAjax) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData(['redirect' => $this->getUrl($errorPath, $errorParams)]);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($errorPath, $errorParams);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        // conditions_payload arrives as nested form data: {rule:{conditions:{...}}}
        // NOTE: BadgerInterface::CONDITIONS_PAYLOAD === 'conditions_payload', so we must
        // unset the original array before re-assigning the encoded string to the same key.
        if (isset($data['conditions_payload']) && is_array($data['conditions_payload'])) {
            $condData = $data['conditions_payload'];
            unset($data['conditions_payload']);
            if (isset($condData['rule']['conditions'])) {
                $rule = $this->ruleFactory->create();
                $rule->loadPost($condData['rule']);
                $data[BadgerInterface::CONDITIONS_PAYLOAD] = $this->jsonSerializer->encode(
                    $rule->getConditions()->asArray()
                );
            } else {
                $data[BadgerInterface::CONDITIONS_PAYLOAD] = null;
            }
        }

        foreach ([BadgerInterface::STORE_IDS, BadgerInterface::CUSTOMER_GROUP_IDS] as $listField) {
            if (isset($data[$listField]) && !is_array($data[$listField])) {
                $data[$listField] = array_values(array_filter(
                    array_map('trim', explode(',', (string) $data[$listField])),
                    fn($v) => $v !== ''
                ));
            }
        }

        if (isset($data['visuals']) && is_array($data['visuals'])) {
            $rows = $data['visuals']['visuals_container'] ?? $data['visuals'];
            $rows = array_values(is_array($rows) ? $rows : []);
            foreach ($rows as $idx => $visual) {
                if (!is_array($visual)) {
                    continue;
                }
                $bgColor   = trim((string) ($visual['bg_color'] ?? ''));
                $textColor = trim((string) ($visual['text_color'] ?? ''));
                if ($bgColor !== '' || $textColor !== '') {
                    $rows[$idx]['style_payload'] = $this->jsonSerializer->encode(
                        array_filter(['bg_color' => $bgColor ?: null, 'text_color' => $textColor ?: null])
                    );
                }
                unset($rows[$idx]['bg_color'], $rows[$idx]['text_color']);
            }
            $data[BadgerInterface::VISUALS] = $rows;
        }

        if (isset($data['tooltip']) && is_array($data['tooltip'])) {
            $data[BadgerInterface::TOOLTIP] = $data['tooltip'];
        }

        return $data;
    }
}

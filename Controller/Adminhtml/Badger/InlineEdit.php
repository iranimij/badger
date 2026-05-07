<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class InlineEdit extends AbstractAction implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly BadgerRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $items = (array) $this->getRequest()->getParam('items', []);
        if ($items === []) {
            return $result->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $errors = [];
        foreach ($items as $row) {
            if (!is_array($row) || empty($row[BadgerInterface::BADGER_ID])) {
                continue;
            }
            $id = (int) $row[BadgerInterface::BADGER_ID];
            try {
                $badger = $this->repository->getById($id);
                if (array_key_exists(BadgerInterface::NAME, $row)) {
                    $badger->setName((string) $row[BadgerInterface::NAME]);
                }
                if (array_key_exists(BadgerInterface::IS_ENABLED, $row)) {
                    $badger->setIsEnabled((bool) $row[BadgerInterface::IS_ENABLED]);
                }
                if (array_key_exists(BadgerInterface::PRIORITY, $row)) {
                    $badger->setPriority((int) $row[BadgerInterface::PRIORITY]);
                }
                $this->repository->save($badger);
            } catch (\Throwable $e) {
                $errors[] = __('Row %1: %2', $id, $e->getMessage());
            }
        }

        return $result->setData([
            'messages' => $errors,
            'error' => $errors !== [],
        ]);
    }
}

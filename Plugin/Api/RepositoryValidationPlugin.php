<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Api;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\Api\BadgerPayloadValidator;

class RepositoryValidationPlugin
{
    public function __construct(private readonly BadgerPayloadValidator $validator)
    {
    }

    public function beforeSave(BadgerRepositoryInterface $subject, BadgerInterface $badger): array
    {
        $this->validator->validate($badger);
        return [$badger];
    }
}

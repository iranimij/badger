<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Api;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Magento\Framework\Exception\InputException;

class BadgerPayloadValidator
{
    private const NAME_MAX = 255;

    /**
     * @throws InputException
     */
    public function validate(BadgerInterface $badger): void
    {
        $errors = [];

        $name = trim($badger->getName());
        if ($name === '') {
            $errors[] = 'Name is required.';
        } elseif (strlen($name) > self::NAME_MAX) {
            $errors[] = sprintf('Name must be %d characters or less.', self::NAME_MAX);
        }

        if ($badger->getPriority() < 0) {
            $errors[] = 'Priority must be zero or positive.';
        }

        $from = $badger->getActiveFrom();
        $to = $badger->getActiveTo();
        if ($from !== null && strtotime($from) === false) {
            $errors[] = 'Active From is not a valid datetime.';
        }
        if ($to !== null && strtotime($to) === false) {
            $errors[] = 'Active To is not a valid datetime.';
        }
        if ($from !== null && $to !== null && strtotime($from) !== false && strtotime($to) !== false) {
            if (strtotime($to) <= strtotime($from)) {
                $errors[] = 'Active To must be after Active From.';
            }
        }

        foreach ($badger->getVisuals() as $visual) {
            if (Surface::tryFrom((int) $visual->getSurface()) === null) {
                $errors[] = sprintf('Unknown surface "%s".', (string) $visual->getSurface());
            }
            if (Placement::tryFrom((int) $visual->getPlacement()) === null) {
                $errors[] = sprintf('Unknown placement "%s".', (string) $visual->getPlacement());
            }
            if (ShapeKind::tryFrom((int) $visual->getShapeKind()) === null) {
                $errors[] = sprintf('Unknown shape kind "%s".', (string) $visual->getShapeKind());
            }
            $size = (int) $visual->getSizePercent();
            if ($size < 1 || $size > 100) {
                $errors[] = 'Size percent must be between 1 and 100.';
            }
        }

        if ($errors !== []) {
            throw new InputException(__(implode(' ', $errors)));
        }
    }
}

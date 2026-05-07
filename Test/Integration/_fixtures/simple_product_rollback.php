<?php
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$om = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $om->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $om->get(ProductRepositoryInterface::class)->deleteById('badger-test-simple');
} catch (NoSuchEntityException $e) {
    // already gone
}

$registry->unregister('isSecureArea');

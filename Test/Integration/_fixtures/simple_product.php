<?php
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$om = Bootstrap::getObjectManager();

/** @var ProductInterfaceFactory $factory */
$factory = $om->get(ProductInterfaceFactory::class);
$product = $factory->create();
$product->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Badger Test Simple Product')
    ->setSku('badger-test-simple')
    ->setPrice(100.00)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['qty' => 100, 'is_in_stock' => 1]);

/** @var ProductRepositoryInterface $repo */
$repo = $om->get(ProductRepositoryInterface::class);
$repo->save($product);

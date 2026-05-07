<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BadgerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Iranimij\Badger\Api\Data\BadgerInterface[]
     */
    public function getItems();

    /**
     * @param \Iranimij\Badger\Api\Data\BadgerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

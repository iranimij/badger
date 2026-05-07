<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface BadgerRepositoryInterface
{
    /**
     * @param \Iranimij\Badger\Api\Data\BadgerInterface $badger
     * @return \Iranimij\Badger\Api\Data\BadgerInterface
     * @throws CouldNotSaveException
     */
    public function save(BadgerInterface $badger): BadgerInterface;

    /**
     * @param int $badgerId
     * @return \Iranimij\Badger\Api\Data\BadgerInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $badgerId): BadgerInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Iranimij\Badger\Api\Data\BadgerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BadgerSearchResultsInterface;

    /**
     * @param int $badgerId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $badgerId): bool;

    /**
     * @param int $badgerId
     * @return \Iranimij\Badger\Api\Data\BadgerInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function duplicate(int $badgerId): BadgerInterface;
}

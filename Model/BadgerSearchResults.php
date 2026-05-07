<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class BadgerSearchResults extends SearchResults implements BadgerSearchResultsInterface
{
}

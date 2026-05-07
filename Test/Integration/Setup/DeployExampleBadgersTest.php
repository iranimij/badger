<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Setup;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Setup\Patch\Data\DeployExampleBadgers;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DeployExampleBadgersTest extends TestCase
{
    private DeployExampleBadgers $patch;
    private BadgerRepositoryInterface $repository;
    private SearchCriteriaBuilder $criteriaBuilder;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->patch = $om->get(DeployExampleBadgers::class);
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->criteriaBuilder = $om->get(SearchCriteriaBuilder::class);
    }

    public function testImplementsDataPatchInterface(): void
    {
        $this->assertInstanceOf(DataPatchInterface::class, $this->patch);
    }

    public function testGetDependenciesReturnsArray(): void
    {
        $this->assertIsArray(DeployExampleBadgers::getDependencies());
    }

    public function testGetAliasesReturnsArray(): void
    {
        $this->assertIsArray($this->patch->getAliases());
    }

    public function testApplyCreatesBadgers(): void
    {
        $before = $this->repository->getList($this->criteriaBuilder->create())->getTotalCount();

        $this->patch->apply();

        $after = $this->repository->getList($this->criteriaBuilder->create())->getTotalCount();
        $this->assertGreaterThan($before, $after, 'apply() should create at least one example badger');
    }

    public function testApplyIsIdempotentOnSecondCall(): void
    {
        $this->patch->apply();
        $countAfterFirst = $this->repository->getList($this->criteriaBuilder->create())->getTotalCount();

        $this->patch->apply();
        $countAfterSecond = $this->repository->getList($this->criteriaBuilder->create())->getTotalCount();

        $this->assertSame(
            $countAfterFirst,
            $countAfterSecond,
            'Applying the patch twice should not create duplicate records'
        );
    }
}

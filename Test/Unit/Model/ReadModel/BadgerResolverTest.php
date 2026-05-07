<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\ReadModel;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\BadgerResolver;
use Iranimij\Badger\Model\Resolution\ExclusivityPolicy;
use Iranimij\Badger\Model\Resolution\PriorityResolver;
use Iranimij\Badger\Model\Rule;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Badger\Service\Placeholder\TemplateRenderer;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\CacheInterface;
use PHPUnit\Framework\TestCase;

class BadgerResolverTest extends TestCase
{
    private BadgerRepositoryInterface $repository;
    private RuleFactory $ruleFactory;
    private JsonSerializer $jsonSerializer;
    private TemplateRenderer $templateRenderer;
    private SearchCriteriaBuilder $criteriaBuilder;
    private BadgerResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BadgerRepositoryInterface::class);
        $this->ruleFactory = $this->createMock(RuleFactory::class);
        $this->jsonSerializer = $this->createMock(JsonSerializer::class);
        $this->templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->templateRenderer->method('render')->willReturnArgument(0);

        $this->criteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->criteriaBuilder->method('addFilter')->willReturnSelf();
        $this->criteriaBuilder->method('create')->willReturn($this->createMock(SearchCriteria::class));

        $this->resolver = new BadgerResolver(
            $this->repository,
            $this->ruleFactory,
            $this->jsonSerializer,
            $this->templateRenderer,
            $this->criteriaBuilder,
            $this->createMock(FilterBuilder::class),
            $this->createMock(FilterGroupBuilder::class),
            $this->createMock(CacheInterface::class),
            new PriorityResolver(),
            new ExclusivityPolicy()
        );
    }

    private function makeBadger(array $opts): BadgerInterface
    {
        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn($opts['id'] ?? 1);
        $badger->method('getName')->willReturn($opts['name'] ?? 'B');
        $badger->method('getPriority')->willReturn($opts['priority'] ?? 0);
        $badger->method('isExclusive')->willReturn($opts['exclusive'] ?? false);
        $badger->method('getActiveFrom')->willReturn($opts['from'] ?? null);
        $badger->method('getActiveTo')->willReturn($opts['to'] ?? null);
        $badger->method('getStoreIds')->willReturn($opts['stores'] ?? []);
        $badger->method('getCustomerGroupIds')->willReturn($opts['groups'] ?? []);
        $badger->method('getConditionsPayload')->willReturn($opts['conditions'] ?? null);
        $badger->method('getVisuals')->willReturn($opts['visuals'] ?? []);
        $badger->method('getTooltip')->willReturn($opts['tooltip'] ?? null);
        return $badger;
    }

    private function makeVisual(int $surface, int $placement = 0, int $shape = 2): VisualInterface
    {
        $v = $this->createMock(VisualInterface::class);
        $v->method('getSurface')->willReturn($surface);
        $v->method('getPlacement')->willReturn($placement);
        $v->method('getShapeKind')->willReturn($shape);
        $v->method('getLabelText')->willReturn('SALE');
        $v->method('getImagePath')->willReturn(null);
        $v->method('getRedirectUrl')->willReturn(null);
        $v->method('getAltText')->willReturn(null);
        $v->method('getCssClass')->willReturn(null);
        $v->method('getSizePercent')->willReturn(25);
        return $v;
    }

    private function setRepoReturn(array $badgers): void
    {
        $sr = $this->createMock(BadgerSearchResultsInterface::class);
        $sr->method('getItems')->willReturn($badgers);
        $this->repository->method('getList')->willReturn($sr);
    }

    public function testFiltersByStore(): void
    {
        $b = $this->makeBadger([
            'stores' => [2],
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value)],
        ]);
        $this->setRepoReturn([$b]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 1, 0);
        self::assertSame([], $r);
    }

    public function testKeepsBadgerInStore(): void
    {
        $b = $this->makeBadger([
            'stores' => [1, 2],
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value)],
        ]);
        $this->setRepoReturn([$b]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 1, 0);
        self::assertCount(1, $r);
    }

    public function testFiltersBySurface(): void
    {
        $b = $this->makeBadger([
            'visuals' => [$this->makeVisual(Surface::PRODUCT_PAGE->value)],
        ]);
        $this->setRepoReturn([$b]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0);
        self::assertSame([], $r);
    }

    public function testEvaluatesConditions(): void
    {
        $rule = $this->createMock(Rule::class);
        $rule->method('validate')->willReturn(false);
        $this->ruleFactory->method('create')->willReturn($rule);
        $this->jsonSerializer->method('decodeArray')->willReturn(['type' => 'Combine']);

        $b = $this->makeBadger([
            'conditions' => '{"type":"Combine"}',
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value)],
        ]);
        $this->setRepoReturn([$b]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0);
        self::assertSame([], $r);
    }

    public function testActiveWindowExcluded(): void
    {
        $b = $this->makeBadger([
            'from' => date('Y-m-d H:i:s', time() + 3600),
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value)],
        ]);
        $this->setRepoReturn([$b]);
        self::assertSame([], $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0));
    }

    public function testSortsByPriorityDesc(): void
    {
        $low = $this->makeBadger(['id' => 1, 'priority' => 1, 'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value, 0)]]);
        $high = $this->makeBadger(['id' => 2, 'priority' => 9, 'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value, 1)]]);
        $this->setRepoReturn([$low, $high]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0);
        self::assertSame(2, $r[0]->badgerId);
        self::assertSame(1, $r[1]->badgerId);
    }

    public function testExclusivitySuppressesLower(): void
    {
        $high = $this->makeBadger([
            'id' => 1, 'priority' => 5, 'exclusive' => true,
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value, 0)],
        ]);
        $low = $this->makeBadger([
            'id' => 2, 'priority' => 1,
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value, 0)],
        ]);
        $this->setRepoReturn([$high, $low]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0);
        self::assertCount(1, $r);
        self::assertSame(1, $r[0]->badgerId);
    }

    public function testRendersTooltipWhenEnabled(): void
    {
        $tooltip = $this->createMock(TooltipInterface::class);
        $tooltip->method('isEnabled')->willReturn(true);
        $tooltip->method('getBodyText')->willReturn('See details');
        $tooltip->method('getBackgroundColor')->willReturn('#000');
        $tooltip->method('getTextColor')->willReturn('#fff');
        $b = $this->makeBadger([
            'tooltip' => $tooltip,
            'visuals' => [$this->makeVisual(Surface::CATEGORY_GRID->value)],
        ]);
        $this->setRepoReturn([$b]);
        $r = $this->resolver->resolve($this->createMock(Product::class), Surface::CATEGORY_GRID, 0, 0);
        self::assertSame('See details', $r[0]->tooltipText);
    }
}

<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Controller\Adminhtml\Badger\Save;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Model\Rule;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Rule\Model\Condition\Combine;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    private Save $action;
    private BadgerRepositoryInterface $repository;
    private BadgerHydrator $hydrator;
    private JsonSerializer $json;
    private RuleFactory $ruleFactory;
    private HttpRequest $request;
    private ResultFactory $resultFactory;
    private Redirect $redirect;
    private ManagerInterface $messageManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BadgerRepositoryInterface::class);
        $this->hydrator = $this->createMock(BadgerHydrator::class);
        $this->json = $this->createMock(JsonSerializer::class);
        $this->ruleFactory = $this->createMock(RuleFactory::class);
        $this->request = $this->createMock(HttpRequest::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->redirect->method('setPath')->willReturnSelf();

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->redirect);

        $this->messageManager = $this->createMock(ManagerInterface::class);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getResultFactory')->willReturn($this->resultFactory);
        $context->method('getMessageManager')->willReturn($this->messageManager);

        $this->action = new Save($context, $this->repository, $this->hydrator, $this->json, $this->ruleFactory);
    }

    public function testRedirectsWithoutPostData(): void
    {
        $this->request->method('getPostValue')->willReturn(null);
        $this->repository->expects($this->never())->method('save');
        $this->redirect->expects($this->once())->method('setPath')->with('*/*/');

        $this->action->execute();
    }

    public function testNormalizesConditionsAndSaves(): void
    {
        $conditionsArray = [
            'type' => 'Iranimij\Badger\Model\Condition\Combine',
            'aggregator' => 'all',
            'value' => '1',
            'conditions' => [
                ['type' => 'Iranimij\Badger\Model\Condition\OnSale'],
            ],
        ];

        $combine = $this->getMockBuilder(Combine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['asArray'])
            ->getMock();
        $combine->method('asArray')->willReturn($conditionsArray);

        $rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadPost', 'getConditions'])
            ->getMock();
        $rule->method('getConditions')->willReturn($combine);

        $this->ruleFactory->method('create')->willReturn($rule);
        $this->json->expects($this->once())->method('encode')->with($conditionsArray)->willReturn('encoded');

        $post = [
            BadgerInterface::NAME => 'Sale',
            'conditions_payload' => [
                'rule' => [
                    'conditions' => ['1' => ['type' => 'Iranimij\Badger\Model\Condition\OnSale']],
                ],
            ],
            BadgerInterface::STORE_IDS => '1,2',
            'visuals' => ['visuals_container' => [['label_text' => 'x']]],
            'tooltip' => ['is_enabled' => '1'],
        ];
        $this->request->method('getPostValue')->willReturn($post);
        $this->request->method('getParam')->willReturn(null);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(5);
        $this->hydrator->method('fromArray')->willReturnCallback(
            function (array $payload) use ($badger) {
                self::assertSame('encoded', $payload[BadgerInterface::CONDITIONS_PAYLOAD]);
                self::assertSame(['1', '2'], $payload[BadgerInterface::STORE_IDS]);
                self::assertSame([['label_text' => 'x']], $payload[BadgerInterface::VISUALS]);
                return $badger;
            }
        );
        $this->repository->method('save')->willReturn($badger);
        $this->messageManager->expects($this->once())->method('addSuccessMessage');
        $this->redirect->expects($this->once())->method('setPath')->with('*/*/');

        $this->action->execute();
    }

    public function testMissingConditionsDataClearsPayload(): void
    {
        $post = [
            BadgerInterface::NAME => 'x',
            'conditions_payload' => ['rule' => []],
        ];
        $this->request->method('getPostValue')->willReturn($post);
        $this->request->method('getParam')->willReturn(null);

        $this->json->expects($this->never())->method('encode');

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(1);
        $this->hydrator->method('fromArray')->willReturnCallback(
            function (array $payload) use ($badger) {
                self::assertNull($payload[BadgerInterface::CONDITIONS_PAYLOAD]);
                return $badger;
            }
        );
        $this->repository->method('save')->willReturn($badger);
        $this->redirect->expects($this->once())->method('setPath')->with('*/*/');

        $this->action->execute();
    }

    public function testBackToEditPassesId(): void
    {
        $this->request->method('getPostValue')->willReturn([BadgerInterface::NAME => 'x']);
        $this->request->method('getParam')->willReturnCallback(fn ($key) => $key === 'back' ? 'edit' : null);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(7);
        $this->hydrator->method('fromArray')->willReturn($badger);
        $this->repository->method('save')->willReturn($badger);

        $this->redirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['badger_id' => 7]);

        $this->action->execute();
    }

    public function testAddsErrorOnException(): void
    {
        $this->request->method('getPostValue')->willReturn([BadgerInterface::NAME => 'x']);
        $this->request->method('getParam')->willReturn(null);
        $this->hydrator->method('fromArray')
            ->willThrowException(new \RuntimeException('oops'));
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->redirect->expects($this->once())->method('setPath')->with('*/*/new', []);

        $this->action->execute();
    }
}

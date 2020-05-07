<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http;

use App\Domain\Model\ObjectEntry;
use App\Domain\Service\ObjectStoreInterface;
use App\Interfaces\Http\ApiController;
use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiControllerTest extends TestCase
{
    protected ObjectStoreInterface $objectStore;
    protected ApiController $apiController;

    protected function setUp(): void
    {
        $this->objectStore = $this->createMock(ObjectStoreInterface::class);
        $this->apiController = new ApiController($this->objectStore);
    }

    public function testItStoresMultipleEntries(): void
    {
        $values = ['test1' => 'value1', 'test2' => ['value2']];
        $this->objectStore->expects($this->exactly(2))
            ->method('store')
            ->withConsecutive(
                [new ObjectEntry('test1', 'value1'), $this->isInstanceOf(DateTime::class)],
                [new ObjectEntry('test2', ['value2']), $this->isInstanceOf(DateTime::class)],
            );

        $response = $this->apiController->storeValue($this->getRequest([], json_encode($values)));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * @param mixed $invalidValue
     * @dataProvider invalidValueProvider
     */
    public function testItThrowsErrorWhenInvalidRequest($value): void
    {
        $this->objectStore->expects($this->never())->method('store');
        $this->expectException(BadRequestHttpException::class);
        $this->apiController->storeValue($this->getRequest([], json_encode($value)));
    }

    public function testItReturnsValueFromStore(): void
    {
        $key = 'test-key';
        $value = 'value1';
        $this->objectStore->expects($this->once())
            ->method('get')
            ->with($key, $this->isInstanceOf(DateTime::class))
            ->willReturn(new ObjectEntry($key, $value));

        $response = $this->apiController->getValue($key, $this->getRequest());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(json_encode($value), $response->getContent());
    }

    public function testItReturnsValueAtGivenTimestamp(): void
    {
        $timestamp = (new DateTime('2020-05-01'))->getTimestamp();
        $key = 'test-key';
        $value = 'value1';
        $this->objectStore->expects($this->once())
            ->method('get')
            ->with(
                $key,
                $this->callback(function (DateTime $value) use ($timestamp) {
                    return $value->getTimestamp() === $timestamp;
                }
            ))
            ->willReturn(new ObjectEntry($key, $value));

        $response = $this->apiController->getValue($key, $this->getRequest(['timestamp' => $timestamp]));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(json_encode($value), $response->getContent());
    }

    public function testItThrowsErrorWhenObjectIsNotFound(): void
    {
        $key = 'test-key';
        $this->objectStore->expects($this->once())
            ->method('get')
            ->with($key, $this->isInstanceOf(DateTime::class))
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->apiController->getValue($key, $this->getRequest());
    }

    public function testItThrowsErrorWhenTimestampIsInvalid(): void
    {
        $this->objectStore->expects($this->never())->method('get');
        $this->expectException(BadRequestHttpException::class);

        $this->apiController->getValue('test-key', $this->getRequest(['timestamp' => 'not-a-valid-timestamp']));
    }

    public function invalidValueProvider(): Generator
    {
        yield [null];
        yield [false];
        yield ['test'];
    }

    protected function getRequest(array $query = [], ?string $content = null): Request
    {
        return new Request($query, [], [], [], [], [], $content);
    }
}

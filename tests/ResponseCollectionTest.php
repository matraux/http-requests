<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Response\Response;
use Matraux\HttpRequests\Response\ResponseCollection;
use Tester\Assert;
use Tester\TestCase;
use Throwable;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::tester();

/**
 * @testCase
 */
final class ResponseCollectionTest extends TestCase
{

	public function testCreate(): void
	{
		Assert::noError(function (): void {
			ResponseCollection::create([
				Response::create(new Psr7Response(), Request::create(Method::Get, ''))
			]);
		});
	}

	public function testArrayAccess(): void
	{
		$response = Response::create(new Psr7Response(), Request::create(Method::Get, ''));
		$collection = ResponseCollection::create([
			$response,
			'A' => $response,
			1 => $response,
		]);

		Assert::equal($response, $collection[1]);
		Assert::equal($response, $collection['A']);

		Assert::exception(function () use($collection, $response): void {
			$collection[] = $response;
		}, Throwable::class);

		Assert::exception(function () use($collection): void {
			unset($collection['A']);
		}, Throwable::class);
	}

	public function testIterator(): void
	{
		$response = Response::create(new Psr7Response(), Request::create(Method::Get, ''));
		$collection = ResponseCollection::create([$response]);

		foreach($collection as $response) {
			Assert::type(Response::class, $response);
		}
	}

	public function testCount(): void
	{
		$response = Response::create(new Psr7Response(), Request::create(Method::Get, ''));
		$collection = ResponseCollection::create([$response, $response]);

		Assert::count(2, $collection);
	}

}

new ResponseCollectionTest()->run();

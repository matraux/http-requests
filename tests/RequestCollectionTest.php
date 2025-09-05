<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest;

use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Request\RequestCollection;
use Tester\Assert;
use Tester\TestCase;
use Throwable;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::tester();

/**
 * @testCase
 */
final class RequestCollectionTest extends TestCase
{

	public function testCreate(): void
	{
		Assert::noError(function(){
			RequestCollection::create();
		});
	}

	public function testArrayAccess(): void
	{
		$collection = RequestCollection::create();
		$request = Request::create(Method::Get, '');

		Assert::noError(function()use($collection, $request){
			$collection[1] = $request;
			$collection['A'] = $request;
			$collection[] = $request;
		});

		Assert::equal($request, $collection[1]);
		Assert::equal($request, $collection['A']);

		Assert::noError(function()use($collection){
			unset($collection['A']);
		});
	}

	public function testIterator(): void
	{
		$collection = RequestCollection::create();
		$request = Request::create(Method::Get, '');
		$collection[] = $request;

		foreach($collection as $request) {
			Assert::type(Request::class, $request);
		}
	}

	public function testCount(): void
	{
		$collection = RequestCollection::create();
		$request = Request::create(Method::Get, '');

		Assert::count(0, $collection);

		$collection[] = $request;

		Assert::count(1, $collection);
	}

}

new RequestCollectionTest()->run();
<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest;

use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Tester\Assert;
use Tester\TestCase;
use Throwable;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::tester();

/**
 * @testCase
 */
final class RequestTest extends TestCase
{

	public function testCreate(): void
	{
		Assert::noError(function (): void {
			Request::create(Method::Get, '');
		});
	}

	public function testMethod(): void
	{
		Assert::noError(function(){
			Request::create(Method::Get, '');
		});

		Assert::noError(function(){
			Request::create('GET', '');
		});
	}

	public function testBody(): void
	{
		Assert::noError(function(){
			Request::create(Method::Get, '')->body = 'example string';
		});
	}

}

new RequestTest()->run();

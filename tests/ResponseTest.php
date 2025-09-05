<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Response\Response;
use Tester\Assert;
use Tester\TestCase;
use Throwable;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::tester();

/**
 * @testCase
 */
final class ResponseTest extends TestCase
{


	public function testCreate(): void
	{
		Assert::noError(function (): void {
			Response::create(new Psr7Response(), Request::create(Method::Get, ''));
		});
	}

}

new ResponseTest()->run();

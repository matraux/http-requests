<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest;

use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Request\RequestCollection;
use Matraux\HttpRequests\Requester;
use Matraux\HttpRequestsTest\Server\HttpServer;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::tester();

/**
 * @testCase
 */
final class RequesterTest extends TestCase
{

	public function testCreate(): void
	{
		Assert::noError(function (): void {
			Requester::create();
		});
	}

	public function testSend(): void
	{
		$httpServer = HttpServer::create();
		$requester = Requester::create();
		$request200 = Request::create(Method::Get, $httpServer->url . '/200.php');
		$request403 = Request::create(Method::Get, $httpServer->url . '/403.php');
		$request500 = Request::create(Method::Get, $httpServer->url . '/500.php');

		$httpServer->start();
		$response200 = $requester->send($request200);
		$response403 = $requester->send($request403);
		$response500 = $requester->send($request500);
		$httpServer->stop();

		Assert::equal(200, $response200->code);
		Assert::equal(403, $response403->code);
		Assert::equal(500, $response500->code);
	}

	public function testSendBatch(): void
	{
		$httpServer = HttpServer::create();
		$requester = Requester::create();
		$requests = RequestCollection::create();
		$requests[200] = Request::create(Method::Get, $httpServer->url . '/200.php');
		$requests[403] = Request::create(Method::Get, $httpServer->url . '/403.php');
		$requests[500] = Request::create(Method::Get, $httpServer->url . '/500.php');

		$httpServer->start();
		$responses = $requester->sendBatch($requests);
		$httpServer->stop();

		Assert::equal(200, $responses[200]->code);
		Assert::equal(403, $responses[403]->code);
		Assert::equal(500, $responses[500]->code);
	}

}

new RequesterTest()->run();

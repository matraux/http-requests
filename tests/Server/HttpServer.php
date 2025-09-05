<?php declare(strict_types = 1);

namespace Matraux\HttpRequestsTest\Server;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class HttpServer
{

	public private(set) string $host = '127.0.0.1';

	public int $port
	{
		get => $this->port ??= (function (): int {
			if (!$socket = stream_socket_server(sprintf('tcp://%s:0', $this->host), $code, $message)) {
				throw new RuntimeException(
					is_string($message) ? $message : 'Unknown socket server error',
					is_numeric($code) ? (int) $code : 0
				);
			}

			if (!$authority = stream_socket_get_name($socket, false)) {
				throw new RuntimeException('Failed to obtain authority');
			}

			fclose($socket);

			if (!preg_match('/:(\d+)$/', $authority, $match)) {
				throw new RuntimeException('Failed to obtain port from authority.');
			}

			return (int) $match[1];
		})();
	}

	public string $url
	{
		get => $this->url ??= sprintf('http://%s:%d', $this->host, $this->port);
	}

	protected Process $process
	{
		get => $this->process ??= new Process([
			PHP_BINARY,
			'-S',
			sprintf('%s:%d', $this->host, $this->port),
			'-t',
			__DIR__,
		]);
	}

	protected function __construct()
	{
	}

	public function __destruct()
	{
		try {
			$this->stop();
		} catch (Throwable $th) {

		}
	}

	public static function create(): static
	{
		return new static();
	}

	public function start(): static
	{
		if ($this->process->isRunning()) {
			return $this;
		}

		$this->process->start();
		usleep(100 * 1000);

		return $this;
	}

	public function stop(): static
	{
		if (!$this->process->isRunning()) {
			return $this;
		}

		try {
			$this->process->stop();
		} catch (Throwable $th) {

		}

		return $this;
	}

}

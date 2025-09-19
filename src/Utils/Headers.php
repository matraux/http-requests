<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Stringable;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<string,array<string>>
 * @implements ArrayAccess<string,array<string>>
 */
final class Headers implements IteratorAggregate, ArrayAccess, Countable
{

	/** @var array<string,array<string>> */
	protected array $headers = [];

	protected function __construct()
	{
	}

	public static function create(): static
	{
		return new static();
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->headers);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->headers);
	}

	/**
	 * @return array<string>
	 */
	public function offsetGet(mixed $offset): array
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->headers[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "string", "%s" given.', get_debug_type($offset)));
		} elseif (!is_array($value) || !static::isStrings($value)) {
			throw new UnexpectedValueException(sprintf('Expected value type "array<string>", "%s" given.', get_debug_type($value)));
		}

		$this->headers[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->headers[$offset]);
	}

	public function count(): int
	{
		return count($this->headers);
	}

	public function add(string $name, string|int|float|bool|Stringable $value): static
	{
		$this->headers[$name][] = (string) $value;

		return $this;
	}

	/**
	 *
	 * @param array<mixed> $values
	 */
	protected static function isStrings(array $values): bool
	{
		if (!array_is_list($values)) {
			return false;
		}

		foreach ($values as $value) {
			if (!is_string($value)) {
				return false;
			}
		}

		return true;
	}

}

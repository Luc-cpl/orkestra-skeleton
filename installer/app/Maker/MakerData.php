<?php

namespace Orkestra\Skeleton\Maker;

use ArrayAccess;

class MakerData implements ArrayAccess
{
	protected array $questions = [];
	protected array $data = [];

	public function setQuestion(string $slug, string $title, string $description, string $default, string $validation): void
	{
		$this->questions[$slug] = array_merge(
			$this->questions[$slug] ?? [],
			[
				'title' => $title,
				'description' => $description,
				'validation' => $validation,
			]
		);

		$this->data[$slug] ??= $default;
	}

	public function getQuestions(): array
	{
		return $this->questions;
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->data[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->data[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->data[$offset]);
	}
}
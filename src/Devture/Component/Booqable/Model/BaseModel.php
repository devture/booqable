<?php
namespace Devture\Component\Booqable\Model;

abstract class BaseModel {

	private $record = array();

	public function __construct(array $record) {
		$this->hydrate($record);
	}

	public function hydrate(array $record) {
		$this->record = $record;
	}

	public function getAttribute(string $key, $defaultValue) {
		return (array_key_exists($key, $this->record) ? $this->record[$key] : $defaultValue);
	}

	public function setAttribute(string $key, $value): void {
		$this->record[$key] = $value;
	}

	public function export(): array {
		return $this->record;
	}

}

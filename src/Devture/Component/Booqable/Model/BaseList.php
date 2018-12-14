<?php
namespace Devture\Component\Booqable\Model;

abstract class BaseList implements \IteratorAggregate {

	private $totalCount;
	private $results;

	public function setTotalCount(?int $value) {
		$this->totalCount = $value;
	}

	public function getTotalCount(): ?int {
		return $this->totalCount;
	}

	public function setResults(array $results) {
		$this->results = $results;
	}

	public function getResults() {
		return $this->results;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->results);
	}

}

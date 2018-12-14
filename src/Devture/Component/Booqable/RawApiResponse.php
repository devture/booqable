<?php
namespace Devture\Component\Booqable;

use Symfony\Component\PropertyAccess\PropertyAccess;

class RawApiResponse {

	private $data;

	public function __construct(array $data) {
		$this->data = $data;
	}

	public function __toString() {
		return json_encode($this->data);
	}

	public function getValue(string $propertyPath) {
		$accessor = PropertyAccess::createPropertyAccessor();
		return $accessor->getValue($this->data, $propertyPath);
	}

	public function getData(): array {
		return $this->data;
	}

}

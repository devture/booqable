<?php
namespace Devture\Component\Booqable\Model;

class OperatingRule extends BaseModel {

	const TYPE_HOURS = 'hours';

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getType(): string {
		return $this->getAttribute('data_type', null);
	}

	public function getData(): array {
		return $this->getAttribute('data', []);
	}

	public function setCustomerId(?string $value): void {
		$this->setAttribute('customer_id', $value);
	}

	public function getCreatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('created_at', null));
	}

	public function getUpdatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('updated_at', null));
	}

	public function __toString() {
		return sprintf('[Operating Rule #%s]', $this->getId());
	}

}

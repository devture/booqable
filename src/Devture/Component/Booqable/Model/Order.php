<?php
namespace Devture\Component\Booqable\Model;

class Order extends BaseModel implements \Devture\Component\Booqable\Model\TaggableInterface {

	use Traits\Taggable;

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getNumber(): int {
		return $this->getAttribute('number', null);
	}

	public function getCustomerId(): ?string {
		return $this->getAttribute('customer_id', null);
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

	public function getTaggingResourceIdentifierKey(): string {
		return 'order_id';
	}

	public function __toString() {
		return sprintf('[Order #%d (%s)]', $this->getNumber(), $this->getId());
	}

}

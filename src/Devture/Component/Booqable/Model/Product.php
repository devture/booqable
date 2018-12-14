<?php
namespace Devture\Component\Booqable\Model;

class Product extends BaseModel {

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getProductGroupId(): ?string {
		return $this->getAttribute('product_group_id', null);
	}

	public function getName(): ?string {
		return $this->getAttribute('name', null);
	}

	public function getDescription(): ?string {
		return $this->getAttribute('description', null);
	}

	public function getQuantity(): ?int {
		return $this->getAttribute('quantity', null);
	}

	public function getCreatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('created_at', null));
	}

	public function getUpdatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('updated_at', null));
	}

	public function __toString() {
		return sprintf('[Product #%s (%s)]', $this->getId(), $this->getName());
	}

}

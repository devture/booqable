<?php
namespace Devture\Component\Booqable\Model;

class Property extends BaseModel {

	const TYPE_TEXT_FIELD = 'Property::TextField';

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getType(): string {
		return $this->getAttribute('type', null);
	}

	public function setType(string $value): void {
		$this->setAttribute('type', $value);
	}

	public function getName(): string {
		return $this->getAttribute('name', null);
	}

	public function setName(string $value): void {
		$this->setAttribute('name', $value);
	}

	public function getValue() {
		return $this->getAttribute('value', null);
	}

	public function setValue($value): void {
		$this->setAttribute('value', $value);
	}

	public function getDefaultPropertyId() {
		return $this->getAttribute('default_property_id', null);
	}

	public function setDefaultPropertyId($value): void {
		$this->setAttribute('default_property_id', $value);
	}

	public function getCreatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('created_at', null));
	}

	public function getUpdatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('updated_at', null));
	}

	public function __toString() {
		return sprintf('[Property %s (%s)]', $this->getName(), $this->getId());
	}

}

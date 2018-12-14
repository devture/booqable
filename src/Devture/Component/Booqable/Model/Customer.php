<?php
namespace Devture\Component\Booqable\Model;

class Customer extends BaseModel implements \Devture\Component\Booqable\Model\TaggableInterface {

	use Traits\Taggable;

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getNumber(): ?int {
		return $this->getAttribute('number', null);
	}

	public function getName(): ?string {
		return $this->getAttribute('name', null);
	}

	public function setName(string $value): void {
		$this->setAttribute('name', $value);
	}

	public function getEmail(): ?string {
		return $this->getAttribute('email', null);
	}

	public function setEmail(string $value): void {
		$this->setAttribute('email', $value);
	}

	public function isArchived(): bool {
		return $this->getAttribute('archived', false);
	}

	public function getCreatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('created_at', null));
	}

	public function getUpdatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('updated_at', null));
	}

	public function getMergeSuggestionCustomerId(): ?string {
		return $this->getAttribute('merge_suggestion_customer_id', null);
	}

	public function setMergeSuggestionCustomerId(?string $value): void {
		$this->setAttribute('merge_suggestion_customer_id', $value);
	}

	public function clearProperties(): void {
		$this->setAttribute('properties', []);
	}

	/**
	 * @return Property[]
	 */
	public function getProperties(): array {
		$records = $this->getAttribute('properties', []);
		if ($records === null) {
			$records = [];
		}

		return array_map(function (array $record): Property {
			return new Property($record);
		}, $records);
	}

	public function getPropertyByName(string $name): ?Property {
		foreach ($this->getProperties() as $property) {
			if ($property->getName() === $name) {
				return $property;
			}
		}

		return null;
	}

	public function removePropertyByName(string $name): void {
		$properties = $this->getProperties();

		$properties = array_filter($properties, function (Property $property) use ($name): bool {
			return ($property->getName() !== $name);
		});

		$properties = array_values($properties);

		$this->setAttribute('properties', array_map(function (Property $property): array {
			return $property->export();
		}, $properties));
	}

	public function addProperty(Property $property): void {
		$properties = $this->getAttribute('properties', []);
		$properties[] = $property->export();
		$this->setAttribute('properties', $properties);
	}

	/**
	 * @return Order[]
	 */
	public function getOrders(): array {
		$records = $this->getAttribute('orders', []);
		if ($records === null) {
			$records = [];
		}

		return array_map(function (array $record): Order {
			return new Order($record);
		}, $records);
	}

	public function getTaggingResourceIdentifierKey(): string {
		return 'customer_id';
	}

	public function __toString() {
		return sprintf('[Customer #%d - %s (#%s)]', $this->getNumber(), $this->getName(), $this->getId());
	}

	public function export(): array {
		$export = parent::export();

		if (array_key_exists('properties', $export)) {
			$export['properties_attributes'] = $export['properties'];
			unset($export['properties']);
		}

		return $export;
	}

}

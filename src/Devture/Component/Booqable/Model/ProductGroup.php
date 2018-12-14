<?php
namespace Devture\Component\Booqable\Model;

class ProductGroup extends BaseModel implements \Devture\Component\Booqable\Model\TaggableInterface {

	use Traits\Taggable;

	public function getId(): ?string {
		return $this->getAttribute('id', null);
	}

	public function getName(): ?string {
		return $this->getAttribute('name', null);
	}

	public function hasVariations(): bool {
		return $this->getAttribute('has_variations', false);
	}

	public function getCreatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('created_at', null));
	}

	public function getUpdatedAtTimestamp(): ?int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('updated_at', null));
	}

	/**
	 * @return Product[]
	 */
	public function getProducts(): array {
		$records = $this->getAttribute('products', []);
		if ($records === null) {
			$records = [];
		}

		return array_map(function (array $record): Product {
			return new Product($record);
		}, $records);
	}

	public function getProductById(string $id): ?Product {
		foreach ($this->getProducts() as $product) {
			if ($product->getId() === $id) {
				return $product;
			}
		}
		return null;
	}

	public function getTaggingResourceIdentifierKey(): string {
		return 'product_group_id';
	}

	public function __toString() {
		return sprintf('[Product Group #%s (%s)]', $this->getId(), $this->getName());
	}

}

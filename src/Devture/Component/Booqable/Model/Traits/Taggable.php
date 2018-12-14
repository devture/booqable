<?php
namespace Devture\Component\Booqable\Model\Traits;

/**
 * Represents a model that is taggable.
 *
 * It's important to note that tags are managed (added/removed) through
 * another API, separate from the one that manages the model.
 *
 * It should be noted that tag capitalization will not be preserved by Booqable.
 * Creating tags gives us random capitalization, supposedly depending on whether the tag
 * existed in the past and how it looked back then.
 * Deleting a tag, however, needs to happen with the exact same capitalization that Booqable gives us.
 */
trait Taggable {

	abstract public function getId();

	/**
	 * Specifies the field name used for the add/remove API.
	 * E.g. `customer_id`.
	 */
	abstract public function getTaggingResourceIdentifierKey(): string;

	public function setTags(array $value): void {
		$this->setAttribute('tags', $value);
	}

	public function getTags(): array {
		$tags = $this->getAttribute('tags', []);
		if ($tags === null) {
			$tags = [];
		}

		return $tags;
	}

	public function hasTag(string $value): bool {
		return (in_array($value, static::normalizeTags($this->getTags())));
	}

	public function addTag(string $value): void {
		$tags = array_merge($this->getTags(), [$value]);
		$this->setAttribute('tags', $tags);
	}

	static public function normalizeTag(string $value): string {
		return strtolower($value);
	}

	static public function normalizeTags(array $tags): array {
		return array_map([__CLASS__, 'normalizeTag'], $tags);
	}

}
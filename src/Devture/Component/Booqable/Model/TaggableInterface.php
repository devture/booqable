<?php
namespace Devture\Component\Booqable\Model;

interface TaggableInterface {

	public function getId();

	/**
	 * Specifies the field name used for the add/remove API.
	 * E.g. `customer_id`.
	 */
	public function getTaggingResourceIdentifierKey(): string;

	public function setTags(array $value): void;

	public function getTags(): array;

	public function hasTag(string $value): bool;

}
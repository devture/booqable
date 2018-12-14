<?php
namespace Devture\Component\Booqable;

class Client {

	private $communicator;

	public function __construct(Communicator $communicator) {
		$this->communicator = $communicator;
	}

	public function getCustomers(array $filterParams, bool $retrieveFullModelData): Model\CustomerListCollection {
		/** @var Model\CustomerListCollection $collection */
		$collection = $this->getCollectionList(
			'/api/1/customers',
			$filterParams,
			'customers',
			$retrieveFullModelData,
			'/api/1/customers/%s',
			'customer',
			Model\CustomerListCollection::class,
			Model\Customer::class
		);
		return $collection;
	}

	public function getCustomerById(string $id): Model\Customer {
		/** @var Model\Customer $entity */
		$entity = $this->getEntity(sprintf('/api/1/customers/%s', $id), 'customer', Model\Customer::class);
		return $entity;
	}

	public function createCustomer(Model\Customer $entity): void {
		$this->createEntity($entity, '/api/1/customers', 'customer');
	}

	public function updateCustomer(Model\Customer $entity): void {
		$this->updateEntity($entity, sprintf('/api/1/customers/%s', $entity->getId()), 'customer');
	}

	public function deleteCustomer(Model\Customer $entity): void {
		$rawApiResponse = $this->communicator->delete(sprintf('/api/1/customers/%s/archive', $entity->getId()), [], []);
		$entity->hydrate($rawApiResponse->getValue('[customer]'));
	}

	public function restoreCustomer(Model\Customer $entity): void {
		$rawApiResponse = $this->communicator->post(sprintf('/api/1/customers/%s/restore', $entity->getId()), [], []);
		$entity->hydrate($rawApiResponse->getValue('[customer]'));
	}

	public function transferCustomerResourcesToAnother(Model\Customer $source, Model\Customer $target): void {
		foreach ($source->getOrders() as $orderIncomplete) {
			$order = $this->getOrderById($orderIncomplete->getId());
			$order->setCustomerId($target->getId());

			$this->updateOrder($order);
		}
	}

	public function getOrderById(string $id): Model\Order {
		/** @var Model\Order $entity */
		$entity = $this->getEntity(sprintf('/api/1/orders/%s', $id), 'order', Model\Order::class);
		return $entity;
	}

	public function updateOrder(Model\Order $entity): void {
		$this->updateEntity($entity, sprintf('/api/1/orders/%s', $entity->getId()), 'order');
	}

	public function getProductGroups(array $filterParams, bool $retrieveFullModelData): Model\ProductGroupListCollection {
		/** @var Model\ProductGroupListCollection $collection */
		$collection = $this->getCollectionList(
			'/api/1/product_groups',
			$filterParams,
			'product_groups',
			$retrieveFullModelData,
			'/api/1/product_groups/%s',
			'product_group',
			Model\ProductGroupListCollection::class,
			Model\ProductGroup::class
		);
		return $collection;
	}

	public function getProductGroupById(string $id): Model\ProductGroup {
		/** @var Model\ProductGroup $entity */
		$entity = $this->getEntity(sprintf('/api/1/product_groups/%s', $id), 'product_group', Model\ProductGroup::class);
		return $entity;
	}

	public function getProductById(string $id): Model\Product {
		/** @var Model\Product $entity */
		$entity = $this->getEntity(sprintf('/api/1/products/%s', $id), 'product', Model\Product::class);
		return $entity;
	}

	/**
	 * @param string $productId
	 * @param string $from - `d-m-Y`-formatted date
	 * @param string $till - `d-m-Y`-formatted date
	 * @param string $interval - one of the `Model\ProductAvailabilitySlot::INTERVAL_` constants
	 */
	public function getAvailabilityByProductId(string $productId, string $from, string $till, string $interval): Model\ProductAvailabilitySlotListCollection {
		$resultsMap = $this->getAvailabilityByProductIds([$productId], $from, $till, $interval);
		if (!array_key_exists($productId, $resultsMap)) {
			throw new \LogicException('Cannot find result');
		}

		/** @var Model\ProductAvailabilitySlotListCollection $result */
		$result = $resultsMap[$productId];
		return $result;
	}

	/**
	 * @param string[] $productIds
	 * @param string $from - `d-m-Y`-formatted date
	 * @param string $till - `d-m-Y`-formatted date
	 * @param string $interval - one of the `Model\ProductAvailabilitySlot::INTERVAL_` constants
	 * @return array<string, Model\ProductAvailabilitySlotListCollection>
	 */
	public function getAvailabilityByProductIds(array $productIds, string $from, string $till, string $interval): array {
		$rawApiResponses = $this->communicator->executeAll(function () use ($productIds, $from, $till, $interval) {
			$queryParams = [
				'from' => $from,
				'till' => $till,
				'interval' => $interval,
			];
			foreach ($productIds as $productId) {
				yield $this->communicator->createRequest(sprintf('/api/1/products/%s/availability', $productId), $queryParams);
			}
		}, 15);

		$resultsMap = []; // product id => Model\ProductAvailabilitySlotListCollection
		foreach ($rawApiResponses as $idx => $rawApiResponse) {
			$models = [];
			foreach ($rawApiResponse->getData() as $_slotId => $slotRecord) {
				$models[] = new Model\ProductAvailabilitySlot($slotRecord);
			}

			$list = new Model\ProductAvailabilitySlotListCollection();
			$list->setResults($models);
			$list->setTotalCount(count($models));

			$productId = $productIds[$idx];

			$resultsMap[$productId] = $list;
		}

		return $resultsMap;
	}

	public function getOperatingRules(array $filterParams): Model\OperatingRuleListCollection {
		/** @var Model\OperatingRuleListCollection $collection */
		$collection = $this->getCollectionList(
			'/api/2/operating_rules',
			$filterParams,
			// Retrieving full model data is not supported for this.
			'operating_rules',
			false,
			null,
			null,
			Model\OperatingRuleListCollection::class,
			Model\OperatingRule::class
		);
		return $collection;
	}

	/**
	 * Creates the given entity via the API and rehydrates the new data into the same entity.
	 */
	private function createEntity(Model\BaseModel $entity, string $uri, string $resourceFieldKey): void {
		$tags = [];
		if ($entity instanceof Model\TaggableInterface) {
			// Preserve the tags temporarily.
			// We'll sync them later.
			$tags = $entity->getTags();
		}

		$rawApiResponse = $this->communicator->post($uri, [], [
			$resourceFieldKey => $entity->export(),
		]);

		$newData = $rawApiResponse->getValue(sprintf('[%s]', $resourceFieldKey));
		$entity->hydrate($newData);

		if ($entity instanceof Model\TaggableInterface) {
			// The requests (and hydration) above made us lose the tags,
			// since they're managed through another API.
			// Let's assign/de-assign tags now.
			$this->ensureTagsSynced($entity, $tags);
		}
	}

	/**
	 * Update the given entity via the API and rehydrates the new data into the same entity.
	 */
	private function updateEntity(Model\BaseModel $entity, string $uri, string $resourceFieldKey): void {
		$tags = [];
		if ($entity instanceof Model\TaggableInterface) {
			// Preserve the tags temporarily.
			// We'll sync them later.
			$tags = $entity->getTags();
		}

		$rawApiResponse = $this->communicator->put($uri, [], [
			$resourceFieldKey => $entity->export(),
		]);

		$newData = $rawApiResponse->getValue(sprintf('[%s]', $resourceFieldKey));
		$entity->hydrate($newData);

		if ($entity instanceof Model\TaggableInterface) {
			// The requests (and hydration) above made us lose the tags,
			// since they're managed through another API.
			// Let's assign/de-assign tags now.
			$this->ensureTagsSynced($entity, $tags);
		}
	}

	private function ensureTagsSynced(Model\TaggableInterface $entity, array $tags): void {
		$tagsNormalized = Model\Traits\Taggable::normalizeTags($tags);

		$identifierKey = $entity->getTaggingResourceIdentifierKey();

		$tagMapNow = []; // normalized -> actual
		foreach ($entity->getTags() as $tag) {
			$tagMapNow[Model\Traits\Taggable::normalizeTag($tag)] = $tag;
		}

		// Figure out which tags to delete.
		foreach ($tagMapNow as $tagNormalized => $tagActual) {
			if (in_array($tagNormalized, $tagsNormalized)) {
				// Good. Tag exists on the entity now and we want it to exist.
				continue;
			}

			$this->communicator->post('/api/1/tags/remove', [], [
				$identifierKey => $entity->getId(),
				'name' => $tagActual,
			]);
		}

		foreach ($tags as $tag) {
			$tagNormalized = Model\Traits\Taggable::normalizeTag($tag);
			if (array_key_exists($tagNormalized, $tagMapNow)) {
				// Good. We have this tag.
				continue;
			}

			$this->communicator->post('/api/1/tags/add', [], [
				$identifierKey => $entity->getId(),
				'name' => $tag,
			]);
		}

		// Note: the capitalization of these $tags may change on actual rehydration.
		$entity->setTags($tags);
	}

	private function getCollectionList(
		string $apiEndpointList,
		array $filterParams,
		string $listFieldKey,
		bool $retrieveFullModelData,
		?string $apiEndPointTemplateResource,
		?string $resourceFieldKey,
		string $collectionClassName,
		string $itemClassName
	): Model\BaseList {
		$rawApiResponseList = $this->communicator->get($apiEndpointList, $filterParams);

		$list = new $collectionClassName;

		$totalCount = $rawApiResponseList->getValue('[meta][total_count]');
		if ($totalCount !== null) {
			$list->setTotalCount((int) $totalCount);
		}

		$resources = $rawApiResponseList->getValue(sprintf('[%s]', $listFieldKey));

		if ($retrieveFullModelData) {
			if ($apiEndPointTemplateResource === null || $resourceFieldKey === null) {
				throw new \LogicException('Cannot retrieve full model data when required parameters are missing');
			}

			$rawApiResourceResponses = $this->communicator->executeAll(function () use ($resources, $itemClassName, $apiEndPointTemplateResource) {
				foreach ($resources as $resultData) {
					if (!array_key_exists('id', $resultData)) {
						throw new Exception\BadResponse(sprintf('Expected %s resource to contain an id field', $itemClassName));
					}

					yield $this->communicator->createRequest(sprintf($apiEndPointTemplateResource, $resultData['id']), []);
				}
			}, 15);

			foreach ($rawApiResourceResponses as $idx => $rawApiResourceResponse) {
				$resources[$idx] = $rawApiResourceResponse->getValue(sprintf('[%s]', $resourceFieldKey));
			}
		}

		$list->setResults(array_map(function (array $resourceData) use ($itemClassName) {
			return new $itemClassName($resourceData);
		}, $resources));

		return $list;
	}

	/**
	 * @throws Exception\NotFound
	 */
	private function getEntity(string $uri, string $resourceFieldKey, string $itemClassName): Model\BaseModel {
		$rawApiResponse = $this->communicator->get($uri, []);
		$resourceData = $rawApiResponse->getValue(sprintf('[%s]', $resourceFieldKey));
		return new $itemClassName($resourceData);
	}

}

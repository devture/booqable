<?php
namespace Devture\Component\Booqable\Model;

class ProductGroupListCollection extends BaseList {

	/**
	 * {@inheritDoc}
	 * @see \Devture\Component\Booqable\Model\BaseList::getResults()
	 * @return ProductGroup[]
	 */
	public function getResults() {
		return parent::getResults();
	}

}

<?php
namespace Devture\Component\Booqable\Model;

class CustomerListCollection extends BaseList {

	/**
	 * {@inheritDoc}
	 * @see \Devture\Component\Booqable\Model\BaseList::getResults()
	 * @return Customer[]
	 */
	public function getResults() {
		return parent::getResults();
	}

}

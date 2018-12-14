<?php
namespace Devture\Component\Booqable\Model;

class ProductAvailabilitySlot extends BaseModel {

	const INTERVAL_MINUTE = 'minute';
	const INTERVAL_HOUR = 'hour';
	const INTERVAL_DAY = 'day';
	const INTERVAL_WEEK = 'week';
	const INTERVAL_MONTH = 'month';

	public function getReservedCount(): int {
		return $this->getAttribute('reserved', 0);
	}

	public function getConceptCount(): int {
		return $this->getAttribute('concept', 0);
	}

	public function getAvailableCount(): int {
		return $this->getAttribute('available', 0);
	}

	public function getTotalCount(): int {
		return $this->getAttribute('total', 0);
	}

	public function getInterval(): string {
		return $this->getAttribute('interval', '');
	}

	public function getDateTimestamp(): int {
		return \Devture\Component\Booqable\Util::convertDatetimeToTimestamp($this->getAttribute('date', null));
	}

	public function __toString() {
		return sprintf('[Product Availability Slot @ %s]', $this->getDateTimestamp());
	}

}

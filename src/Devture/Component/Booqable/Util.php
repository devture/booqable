<?php
namespace Devture\Component\Booqable;

class Util {

	static public function convertDatetimeToTimestamp(string $value = null): ?int {
		if ($value === null) {
			return null;
		}
		$valueTimestamp = strtotime($value);
		return ($valueTimestamp === false ? null : $valueTimestamp);
	}

}
<?php

trait CommonKanban {

	public function getStdClassFields(){
		$object = new stdClass();
		foreach ($this->fields as $field => $value) {
			$object->{$field} = $this->{$field};
		}
		return $object;
	}
}

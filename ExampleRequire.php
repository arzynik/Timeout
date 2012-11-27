<?php

class User {
	public function __construct($data) {
		$this->data = $data;
	}
	public function data() {
		return $this->data;
	}
}
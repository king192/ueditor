<?php
interface upload {
	public function checkRootPath();

	public function checkSavePath();

	public function save();

	public function mkdir();

	public function getError();
}
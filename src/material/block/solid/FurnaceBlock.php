<?php

/***REM_START***/
require_once("BurningFurnaceBlock.php");
/***REM_END***/


class FurnaceBlock extends BurningFurnaceBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct($meta);
		$this->id = FURNACE;
		$this->name = "Furnace";
		$this->isActivable = true;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
		$this->lightEmission = 0;
	}
}
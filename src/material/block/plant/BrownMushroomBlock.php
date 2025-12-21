<?php
require_once("BaseMushroomBlock.php");
class BrownMushroomBlock extends BaseMushroomBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(BROWN_MUSHROOM, 0, "Brown Mushroom");
		$this->lightEmission = 1;
	}
}
<?php
require_once("BaseMushroomBlock.php");

class RedMushroomBlock extends BaseMushroomBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(RED_MUSHROOM, 0, "Red Mushroom");
	}
}
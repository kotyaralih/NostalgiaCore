<?php

class WoodDoorBlock extends DoorBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(WOOD_DOOR_BLOCK, $meta, "Wood Door Block");
		$this->isActivable = true;
		$this->hardness = 15;
		$this->breakTime = 3;
		$this->material = Material::$wood;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(WOODEN_DOOR, 0, 1),
		);
	}
}

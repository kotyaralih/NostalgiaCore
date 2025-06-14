<?php

class JungleWoodStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(JUNGLE_WOOD_STAIRS, $meta, "Jungle Wood Stairs");
		$this->hardness = 15;
		$this->breakTime = 2;
		$this->material = Material::$wood;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}
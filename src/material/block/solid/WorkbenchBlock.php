<?php

class WorkbenchBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(WORKBENCH, $meta, "Crafting Table");
		$this->isActivable = true;
		$this->hardness = 12.5;
		$this->breakTime = 2.5;
		$this->material = Material::$wood;
	}
	
	public function onActivate(Item $item, Player $player){
		$player->craftingType = CraftingRecipes::TYPE_CRAFTIGTABLE;
		return true;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}
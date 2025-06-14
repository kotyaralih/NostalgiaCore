<?php

class CarpetBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(CARPET, $meta, "Carpet");
		$names = array(
			0 => "White Carpet",
			1 => "Orange Carpet",
			2 => "Magenta Carpet",
			3 => "Light Blue Carpet",
			4 => "Yellow Carpet",
			5 => "Lime Carpet",
			6 => "Pink Carpet",
			7 => "Gray Carpet",
			8 => "Light Gray Carpet",
			9 => "Cyan Carpet",
			10 => "Purple Carpet",
			11 => "Blue Carpet",
			12 => "Brown Carpet",
			13 => "Green Carpet",
			14 => "Red Carpet",
			15 => "Black Carpet",
		);
		$this->name = $names[$this->meta];
		$this->hardness = 0.5;
		$this->breakTime = 0.1;
		$this->isFullBlock = false;		
		$this->isSolid = true;
		$this->material = Material::$cloth;
	}
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [static::getAABB($level, $x, $y, $z)];
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() !== AIR){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) == AIR){ //Replace with common break method
			[$id, $meta] = $level->level->getBlock($x, $y, $z);
			ServerAPI::request()->api->entity->drop(new Position($x + 0.5, $y, $z + 0.5, $level), BlockAPI::getItem($id, $meta));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		}
	}
}
<?php

class TallGrassBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 1){
		parent::__construct(TALL_GRASS, $meta, "Tall Grass");
		$this->isReplaceable = true;
		$names = array(
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$replaceable_plant;
	}

	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		$downID = $level->level->getBlockID($x, $y - 1, $z);
		if(StaticBlock::getIsTransparent($downID) && $downID !== FARMLAND){ //Replace with common break method
			if(Utils::chance(15)) ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(WHEAT_SEEDS));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->isShears()) $drops[] = array($this->id, $this->meta & 0x03, 1);
		elseif(Utils::chance(15)) $drops[] = array(WHEAT_SEEDS, 0, 1);
		return $drops;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() == GRASS or $down->getID() == DIRT || $down->getID() == FARMLAND){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		} 
		return false;
	}

}

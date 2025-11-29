<?php

class IceBlock extends TransparentBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(ICE, 0, "Ice");
		$this->hardness = 2.5;
		$this->breakTime = 0.5;
		$this->slipperiness = 0.98;
		$this->material = Material::$ice;
		$this->lightBlock = 3;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [new AxisAlignedBB($x, $y, $z, $x + 1, $y + 1, $z + 1)];
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ret = $this->level->setBlock($this, $this, true, false, true);
		return $ret;
	}
	
	public function onBreak(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0){
			$this->level->setBlock($this, new WaterBlock(), true, false, true);
			ServerAPI::request()->api->block->scheduleBlockUpdate(clone $this, 10, BLOCK_UPDATE_NORMAL);
		}else{
			$this->level->setBlock($this, new AirBlock(), true, false, true);
		}
		return true;
	}

	public static function onRandomTick(Level $level, $x, $y, $z){
		if($level->level->getBlockLight($x, $y, $z) > 11 - StaticBlock::$lightBlock[ICE]){
			$level->fastSetBlockUpdate($x, $y, $z, STILL_WATER, true);
		}
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.1,
			4 => 0.15,
			3 => 0.2,
			2 => 0.1,
			1 => 0.4,
			default => 0.75,
		};
	}

	public function getDrops(Item $item, Player $player){
		return array();
	}
}

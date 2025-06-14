<?php

class TorchBlock extends FlowableBlock implements LightingBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(TORCH, $meta, "Torch");
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$decoration;
	}
	
	public function getMaxLightValue(){
		return 15;
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		$side = $level->level->getBlockDamage($x, $y, $z);
		$attach = match($side){
			1 => $level->level->getBlockID($x - 1, $y, $z),
			2 => $level->level->getBlockID($x + 1, $y, $z),
			3 => $level->level->getBlockID($x, $y, $z - 1),
			4 => $level->level->getBlockID($x, $y, $z + 1),
			default => $level->level->getBlockID($x, $y  - 1, $z)
		};
			
		if(StaticBlock::getIsTransparent($attach) && !($side === 0 && $attach === FENCE)){ //Replace with common break method
			ServerAPI::request()->api->entity->drop(new Position($x, $y, $z, $level), BlockAPI::getItem(TORCH, 0, 1));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}

	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->isTransparent === false and $face !== 0){
			$faces = array(
				1 => 5,
				2 => 4,
				3 => 3,
				4 => 2,
				5 => 1,
			);
			$this->meta = $faces[$face];
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}elseif($this->getSide(0)->isTransparent === false or $this->getSide(0)->getID() === FENCE or $this->getSide(0)->getID() === STONE_WALL){
			$this->meta = 0;
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}
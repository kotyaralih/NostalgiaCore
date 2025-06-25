<?php

class CactusBlock extends TransparentBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(CACTUS, $meta, "Cactus");
		$this->isFullBlock = false;
		$this->hardness = 2;
		$this->breakTime = 0.4;
		$this->material = Material::$cactus;
	}
	
	public static function onEntityCollidedWithBlock(Level $level, $x, $y, $z, Entity $entity){
		$entity->harm(1, "cactus");
	}
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [new AxisAlignedBB($x + 0.0625, $y, $z + 0.0625, $x + 1 - 0.0625, $y + 1 - 0.0625, $z + 1 - 0.0625)];
	}
	
	public static function onRandomTick(Level $level, $x, $y, $z){
		//$b = $level->level->getBlock($x, $y - 1, $z);
		$underID = $level->level->getBlockID($x, $y - 1, $z);
		$b = $level->level->getBlock($x, $y, $z);
		$id = $b[0];
		$meta = $b[1];
		if($underID !== CACTUS){
			if($meta == 0x0F){
				for($yy = 1; $yy < 3; ++$yy){
					$bID = $level->level->getBlockID($x, $y + $yy, $z);
					if($bID === AIR){
						$level->fastSetBlockUpdate($x, $y + $yy, $z, CACTUS, 0, true);
						break;
					}
				}
				$meta = 0;
				$level->fastSetBlockUpdate($x, $y, $z, $id, $meta);
			}else{
				$level->fastSetBlockUpdate($x, $y, $z, $id, $meta + 1);
			}
			return BLOCK_UPDATE_RANDOM;
		}
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		$down = $level->level->getBlockID($x, $y - 1, $z);
		$b0 = $level->level->getBlockID($x, $y, $z - 1);
		$b1 = $level->level->getBlockID($x, $y, $z + 1);
		$b2 = $level->level->getBlockID($x - 1, $y, $z);
		$b3 = $level->level->getBlockID($x + 1, $y, $z);
		if(!StaticBlock::getIsFlowable($b0) || !StaticBlock::getIsFlowable($b1) || !StaticBlock::getIsFlowable($b2) || !StaticBlock::getIsFlowable($b3) || ($down !== SAND and $down !== CACTUS)){ //Replace with common break method
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
			ServerAPI::request()->api->entity->drop(new Position($x + 0.5, $y, $z + 0.5, $level), BlockAPI::getItem(CACTUS));
		}
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === SAND or $down->getID() === CACTUS){
			$block0 = $this->getSide(2);
			$block1 = $this->getSide(3);
			$block2 = $this->getSide(4);
			$block3 = $this->getSide(5);
			if($block0->isFlowable === true and $block1->isFlowable === true and $block2->isFlowable === true and $block3->isFlowable === true){
				$this->level->setBlock($this, $this, true, false, true);
				ServerAPI::request()->api->block->scheduleBlockUpdate(clone $this, 10, BLOCK_UPDATE_NORMAL);
				return true;
			}
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}
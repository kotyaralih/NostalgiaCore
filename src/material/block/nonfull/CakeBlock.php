<?php

class CakeBlock extends TransparentBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(CAKE_BLOCK, 0, "Cake Block");
		$this->isFullBlock = false;
		$this->isActivable = true;
		$this->meta = $meta & 0x07;
		$this->hardness = 2.5;
		$this->breakTime = 0.5;
		$this->material = Material::$cake;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		$data = $level->level->getBlockDamage($x, $y, $z);
		return [new AxisAlignedBB($x + (2*$data + 1) * 0.0625, $y, $z + 0.0625, $x + 0.9375, $y + 0.5, $z + 0.9375)];
	}
	
	public static function updateShape(Level $level, $x, $y, $z){
		[$id, $data] = $level->level->getBlock($x, $y, $z);
		
		StaticBlock::setBlockBounds($id, (2 * $data + 1) * 0.0625, 0.0, 0.0625, 0.9375, 0.5, 0.9375);
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
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		}
	}
	
	public function getDrops(Item $item, Player $player){
		return array();
	}
	
	public function onActivate(Item $item, Player $player){
		if($player->entity->getHealth() < 20){
			++$this->meta;
			$player->entity->heal(3, "cake");
			if($this->meta >= 0x06){
				$this->level->setBlock($this, new AirBlock(), true, false, true);
			}else{
				$this->level->setBlock($this, $this, true, false, true);
			}
			return true;
		}
		return false;
	}
	
}
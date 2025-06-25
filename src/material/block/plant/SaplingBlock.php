<?php

class SaplingBlock extends FlowableBlock{
	public static $blockID;
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const JUNGLE = 3;
	const BURN_TIME = 5;
	
	public function __construct($meta = SaplingBlock::OAK){
		parent::__construct(SAPLING, $meta, "Sapling");
		$this->isActivable = true;
		$names = array(
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
			3 => "Jungle Sapling",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$plant;
	}
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === GRASS or $down->getID() === DIRT or $down->getID() === FARMLAND){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			TreeObject::growTree($this->level, $this, new Random(), $this->meta & 0x03);
			$player->consumeSingleItem();
			return true;
		}
		return false;
	}
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		$downID = $level->level->getBlockID($x, $y - 1, $z);
		if(StaticBlock::getIsTransparent($downID) && $downID !== FARMLAND){ //Replace with common break method
			[$id, $meta] = $level->level->getBlock($x, $y, $z);
			ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem($id, $meta));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	public static function onRandomTick(Level $level, $x, $y, $z){
		if(mt_rand(1,7) === 1){
			$meta = $level->level->getBlockDamage($x, $y, $z);
			if(($meta & 0x08) === 0x08){
				TreeObject::growTree($level, new Vector3($x, $y, $z), new Random(), $meta & 0x03);
			}else{
				$meta |= 0x08;
				$level->fastSetBlockUpdate($x, $y, $z, SAPLING, $meta);
				//$level->setBlock($this, $this, true, false, true);
			}
		}
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, $this->meta & 0x03, 1),
		);
	}
}

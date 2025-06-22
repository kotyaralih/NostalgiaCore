<?php

class FireBlock extends FlowableBlock implements LightingBlock{
	public static $blockID;
	public static $flammability = [];
	public static $fireCatchingChance = [];
	
	public function __construct($meta = 0){
		parent::__construct(FIRE, $meta, "Fire");
		$this->isReplaceable = true;
		$this->breakable = false;
		$this->isFullBlock = true;
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$fire;
		$this->lightEmission = 15;
	}
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	public static function setFlammabilityAndCatchingChance($blockID, $flammability, $v){
		self::$flammability[$blockID] = $flammability;
		self::$fireCatchingChance[$blockID] = $v;
	}
	
	public static function canBurn(Level $level, $x, $y, $z){
		return self::$flammability[$level->level->getBlockID($x, $y, $z)] > 0;
	}
	
	public static function onRandomTick(Level $level, $x, $y, $z){
		if($level->level->getBlockID($x, $y - 1, $z) !== NETHERRACK){
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		}
	}
	
	public static function onPlace(Level $level, $x, $y, $z){
		ServerAPI::request()->api->block->scheduleBlockUpdateXYZ($level, $x, $y, $z, BLOCK_UPDATE_SCHEDULED, 30);
	}
	
	public function getDrops(Item $item, Player $player){
		return array();
	}
	public function getMaxLightValue(){
		return 15;
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		$b = $level->level->getBlockID($x, $y - 1, $z);
		if(!StaticBlock::getIsSolid($b)) $level->fastSetBlockUpdate($x, $y, $z, 0, 0, true); //TODO more vanilla later?
	}
	
	public static function onUpdate(Level $level, $x, $y, $z, $type){
		if($type === BLOCK_UPDATE_SCHEDULED){
			$idBelow = $level->level->getBlockID($x, $y - 1, $z);
			[$id, $meta] = $level->level->getBlock($x, $y, $z);
			$alwaysBurn = $idBelow == NETHERRACK;
			
			if($meta < 15){
				$newMeta = $meta + 1; //TODO better formula
				if($newMeta > 15) $newMeta = 15;
				$level->fastSetBlockUpdate($x, $y, $z, $id, $newMeta);
			}
			if($meta == 15){
				if(!$alwaysBurn && !self::canBurn($level, $x, $y - 1, $z) && mt_rand(0, 4) == 0){
					REMOVE_FIRE:
					$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
					return false;
				}
			}
			$chance = self::$fireCatchingChance[$idBelow];
				
			if(mt_rand(0, 249) < $chance){
				//TODO ignite tnt
				$level->fastSetBlockUpdate($x, $y - 1, $z, 0, 0, true);
				goto REMOVE_FIRE;
			}
			
			ServerAPI::request()->api->block->scheduleBlockUpdateXYZ($level, $x, $y, $z, BLOCK_UPDATE_SCHEDULED, 30); //TODO looks like it also adds mt_rand(0, 9) to it
		}
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		parent::place($item, $player, $block, $target, $face, $fx, $fy, $fz);
		$this->level->scheduleBlockUpdate($this, 30, BLOCK_UPDATE_SCHEDULED);
		
		
	}
}
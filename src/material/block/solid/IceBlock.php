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
	
	private function scanForNearbyLightSources($offsetX, $offsetY, $offsetZ){ 
		for($x = -$offsetX; $x <= $offsetX; ++$x){ //i hope it is possible to optimize it
			for($z = -$offsetZ; $z <= $offsetZ; ++$z){
				for($y = -$offsetY; $y <= $offsetY; ++$y){
					$pX = $this->x+$x;
					$pY = $this->y+$y;
					$pZ = $this->z+$z;
					$block = $this->level->getBlock(new Vector3($pX, $pY, $pZ)); //D= slow what was u thinking of gameherobrine from 2022
					if($block instanceof LightingBlock){ //idk is it possible to make it better
						return $block;
					}
				}	
			}	
		}
	}
	//public static function onUpdate(Level $level, $x, $y, $z, $type){ /*Taken from https://github.com/PocketMine/PocketMine-MP/issues/3249*/
		/*if($type === BLOCK_UPDATE_RANDOM){
			$light = $this->scanForNearbyLightSources(3,3,3);
			if(LightUtils::getLightValueFromNearbySource($light,$this) > 12){
				$this->level->setBlock($this, new WaterBlock(), true);
				ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL); //additional request for update
			}
			return BLOCK_UPDATE_RANDOM;
		}
		return false;*/ //TODO ice melting(after light?)
	//}
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

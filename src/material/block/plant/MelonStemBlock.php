<?php

class MelonStemBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(MELON_STEM, $meta, "Melon Stem");
		$this->isActivable = true;
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$plant;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->getID() === FARMLAND){
				$this->level->setBlock($block, $this, true, false, true);
				return true;
			}
		return false;
	}
	
	public static function getGrowthSpeed(Level $level, $x, $y, $z){
		$zneg = $level->level->getBlockID($x, $y, $z-1);
		$zpos = $level->level->getBlockID($x, $y, $z+1);
		$xneg = $level->level->getBlockID($x-1, $y, $z);
		$xpos = $level->level->getBlockID($x+1, $y, $z);
		$znxn = $level->level->getBlockID($x-1, $y, $z-1);
		$znxp = $level->level->getBlockID($x+1, $y, $z-1);
		$zpxp = $level->level->getBlockID($x+1, $y, $z+1);
		$zpxn = $level->level->getBlockID($x-1, $y, $z+1);
		$xEqual = $xneg == MELON_STEM || $xpos == MELON_STEM;
		$zEqual = $zneg == MELON_STEM || $zpos == MELON_STEM;
		$xzEqual = $znxn == MELON_STEM || $znxp == MELON_STEM || $zpxn == MELON_STEM || $zpxp == MELON_STEM;
		
		$speed = 1;
		for($xx = $x-1; $xx >= $x+1; ++$xx){
			for($zz = $z-1; $zz >= $z+1; ++$zz){
				[$id, $meta] = $level->level->getBlock($x, $y, $z);
				$v18 = 0;
				if($id == FARMLAND) $v18 = $meta > 0 ? 3 : 1;
				if($xx != $x || $zz != $z) $v18 *= 0.25;
				$speed += $v18;
			}
		}
		if($xzEqual || ($xEqual && $zEqual)) return $speed*0.5;
		return $speed;
	}
	
	public static function onRandomTick(Level $level, $x, $y, $z){
		//TODO checkAlive
		//if ( Level::getRawBrightness(a2, a3, a4 + 1, a5) > 8 ) TODO - skylight
		
		$growSpeed = static::getGrowthSpeed($level, $x, $y, $z);
		$rand = mt_rand(0, (int)(25/$growSpeed));
		if($rand != 0) return;
		
		$meta = $level->level->getBlockDamage($x, $y, $z);
		if($meta <= 6){
			$level->fastSetBlockUpdateMeta($x, $y, $z, $meta+1);
			return;
		}
		
		$xn = $level->level->getBlockID($x-1, $y, $z);
		$xp = $level->level->getBlockID($x+1, $y, $z);
		$zn = $level->level->getBlockID($x, $y, $z-1);
		$zp = $level->level->getBlockID($x, $y, $z+1);
		if($xn != MELON_BLOCK && $xp != MELON_BLOCK && $zn != MELON_BLOCK && $zp != MELON_BLOCK){
			$below = $level->level->getBlockID($x-1, $y-1, $z);
			if($level->level->getBlockID($x-1, $y, $z) == 0 && ($below == FARMLAND || $below == DIRT || $below == GRASS)){
				$level->fastSetBlockUpdate($x-1, $y, $z, MELON_BLOCK, 0);
				return;
			}
			
			$below = $level->level->getBlockID($x+1, $y-1, $z);
			if($level->level->getBlockID($x+1, $y, $z) == 0 && ($below == FARMLAND || $below == DIRT || $below == GRASS)){
				$level->fastSetBlockUpdate($x+1, $y, $z, MELON_BLOCK, 0);
				return;
			}
			
			$below = $level->level->getBlockID($x, $y-1, $z-1);
			if($level->level->getBlockID($x, $y, $z-1) == 0 && ($below == FARMLAND || $below == DIRT || $below == GRASS)){
				$level->fastSetBlockUpdate($x, $y, $z-1, MELON_BLOCK, 0);
				return;
			}
			
			$below = $level->level->getBlockID($x, $y-1, $z+1);
			if($level->level->getBlockID($x, $y, $z+1) == 0 && ($below == FARMLAND || $below == DIRT || $below == GRASS)){
				$level->fastSetBlockUpdate($x, $y, $z+1, MELON_BLOCK, 0);
				return;
			}
		}
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) != FARMLAND){
			ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(MELON_SEEDS, 0, mt_rand(0, 2)));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			$this->meta += mt_rand(0, 3) + 2;
			if ($this->meta > 7) {
				$this->meta = 7;
			}
			$this->level->setBlock($this, $this, true, false, true);
			$player->consumeSingleItem(send:true);
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = [];
		for($i = 0; $i < 3; ++$i){
			if(mt_rand(0, 15) <= $this->meta) $drops[] = [MELON_SEEDS, 0, 1];
		}
		return $drops;
	}
}
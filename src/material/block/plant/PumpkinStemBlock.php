<?php

class PumpkinStemBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(PUMPKIN_STEM, $meta, "Pumpkin Stem");
		$this->isActivable = true;
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$plant;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->getID() === FARMLAND){
				$this->level->setBlock($block, $this, true, false, true);
				$this->level->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), Utils::getRandomUpdateTicks(), BLOCK_UPDATE_RANDOM);
				return true;
			}
		return false;
	}
	public static function onRandomTick(Level $level, $x, $y, $z){
		if(mt_rand(0, 2) == 1){
			$block = $level->level->getBlock($x, $y, $z);
			if($block[1] < 0x07){
				//++$this->meta;
				//$this->level->setBlock($this, $this, true, false, true);
				$level->fastSetBlockUpdate($x, $y, $z, $block[0], $block[1] + 1);
			}else{
				
				$position = new AirBlock(); //feke block
				$position->x = $x;
				$position->y = $y;
				$position->z = $z;
				$position->level = $level;
				for($side = 2; $side <= 5; ++$side){
					$b = $position->getSide($side);
					if($b->getID() === PUMPKIN){
						return;
					}
				}
				$side = $position->getSide(mt_rand(2,5));
				$d = $side->getSide(0);
				if($side->getID() === AIR and ($d->getID() === FARMLAND or $d->getID() === GRASS or $d->getID() === DIRT)){
					$level->setBlock($side, new PumpkinBlock(), true, false, true);
				}
			}
		}
	}
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) != FARMLAND){
			ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(PUMPKIN_SEEDS, 0, mt_rand(0, 2)));
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
			$player->consumeSingleItem();
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = [];
		if($this->meta >= 0x07){
			$drops[] = [PUMPKIN_SEEDS, 0, mt_rand(1, 2)];
		}
		elseif($this->meta >= 0x01 and $this->meta <= 0x07){
			$drops[] = [PUMPKIN_SEEDS, 0, 1];
		}
		else{
			$drops[] = [PUMPKIN_SEEDS, 0, 0];
		}
		return $drops;
	}
}
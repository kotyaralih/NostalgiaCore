<?php

class BeetrootBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(BEETROOT_BLOCK, $meta, "Beetroot Block");
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
	public static function onRandomTick(Level $level, $x, $y, $z){
		if(mt_rand(0, 2) == 1){
			$b = $level->level->getBlock($x, $y, $z);
			if($b[1] < 0x07){
				$level->fastSetBlockUpdate($x, $y, $z, $b[0], $b[1] + 1);
			}
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

	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) != FARMLAND){
			ServerAPI::request()->api->entity->drop(new Position($x + 0.5, $y, $z + 0.5, $level), BlockAPI::getItem(BEETROOT_SEEDS, 0, 1), 10);
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = [];
		if($this->meta >= 0x07){
			$drops[] = [BEETROOT, 0, 1];
		}
		for($i = 0; $i < 3; ++$i){
			if(mt_rand(0, 15) <= $this->meta){ //a way from 1.4.7
				$drops[] = [BEETROOT_SEEDS, 0, 1];
			}
		}
		return $drops;
	}
}
<?php


class GenericBlock extends Block{
	/**
	 * @param int $id
	 * @param int $meta
	 * @param string $name
	 */
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 * @param Block $block
	 * @param Block $target
	 * @param integer $face
	 * @param integer $fx
	 * @param integer $fy
	 * @param integer $fz
	 *
	 * @return mixed
	 */
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return $this->level->setBlock($this, $this, true, false, true);
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 *
	 * @return boolean
	 */
	public function isBreakable(Item $item, Player $player){
		return ($this->breakable);
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 *
	 * @return mixed
	 */
	public function onBreak(Item $item, Player $player){
		return $this->level->setBlock($this, new AirBlock(), true, false, true);
	}

	public static function onPlace(Level $level, $x, $y, $z){
		[$id, $meta] = $level->level->getBlock($x, $y, $z);
		if(StaticBlock::getHasPhysics($id)){
			ServerAPI::request()->api->block->scheduleBlockUpdateXYZ($level, $x, $y, $z, BLOCK_UPDATE_SCHEDULED, 2); //tick delay == 2
		}else{
			parent::onPlace($level, $x, $y, $z);
		}
	}
	
	public static function onUpdate(Level $level, $x, $y, $z, $type){
		[$id, $meta] = $level->level->getBlock($x, $y, $z);
		if(StaticBlock::getHasPhysics($id)){
			$down = $level->level->getBlockID($x, $y - 1, $z);
			
			if($down == AIR || StaticBlock::getIsLiquid($down)){
				$data = array(
					"x" => $x + 0.5,
					"y" => $y,
					"z" => $z + 0.5,
					"Tile" => $id,
				);
				$server = ServerAPI::request();
				$e = $server->api->entity->add($level, ENTITY_FALLING, FALLING_SAND, $data);
				$server->api->entity->spawnToAll($e);
			}
		}
	}
	
	/**
	 * @param integer $type
	 *
	 * @return boolean
	 */
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		[$id, $meta] = $level->level->getBlock($x, $y, $z);
		if(StaticBlock::getHasPhysics($id)){ //TODO move from here to different class
			static::onUpdate($level, $x, $y, $z, BLOCK_UPDATE_NORMAL);
		}
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 *
	 * @return boolean
	 */
	public function onActivate(Item $item, Player $player){
		return $this->isActivable;
	}
}

<?php

class LeavesBlock extends TransparentBlock{
	public static $blockID;
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	public function __construct($meta = 0){
		parent::__construct(LEAVES, $meta, "Leaves");
		$names = array(
			LeavesBlock::OAK => "Oak Leaves",
			LeavesBlock::SPRUCE => "Spruce Leaves",
			LeavesBlock::BIRCH => "Birch Leaves",
			3 => "",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 1;
		$this->breakTime = 0.2;
		$this->material = Material::$leaves;
		$this->lightBlock = 1;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [new AxisAlignedBB($x, $y, $z, $x + 1, $y + 1, $z + 1)];
	}
	
	public static function createIndex($x, $y, $z){
		return "$x.$y.$z";
	}
	/**
	 * @deprecated leaf decay was rewritten
	 */
	public static function findLog(Level $level, $x, $y, $z, array &$visited, $distance){ //port from newest pocketmine
		$index = self::createIndex($x, $y, $z);
		if(isset($visited[$index])){
			return $visited[$index];
		}
		

		$block = $level->level->getBlockID($x, $y, $z);
		if($block === WOOD){ //type doesn't matter
			return ($visited[$index] = true);
		}

		if($block === LEAVES && $distance <= 4){
			if(self::findLog($level, $x - 1, $y, $z, $visited, $distance + 1)) return ($visited[$index] = true);
			if(self::findLog($level, $x + 1, $y, $z, $visited, $distance + 1)) return ($visited[$index] = true);
			if(self::findLog($level, $x, $y, $z - 1, $visited, $distance + 1)) return ($visited[$index] = true);
			if(self::findLog($level, $x, $y, $z + 1, $visited, $distance + 1)) return ($visited[$index] = true);
		}
		
		return ($visited[$index] = false);
	}
	
	private static $treeBlocksNearby = null;
	public static function onRandomTick(Level $level, $x, $y, $z){
		$b = $level->level->getBlock($x, $y, $z);
		$id = $b[0];
		$meta = $b[1];
		//TODO MCPE uses 0x04 for detecting updates
		if(($meta & 0b1100) == 0b1000){
			if(self::$treeBlocksNearby == null) self::$treeBlocksNearby = array_fill(0, 32*32*32, 0);
			for ($xx = - 4; $xx <= 4; $xx ++) {
				for ($yy = - 4; $yy <= 4; $yy ++) {
					for ($zz = - 4; $zz <= 4; $zz ++) {
						$k3 = $level->level->getBlockID($x + $xx, $y + $yy, $z + $zz);
						if ($k3 == TRUNK) {
							self::$treeBlocksNearby[($xx + 16) * 1024 + ($yy + 16) * 32 + ($zz + 16)] = 0;
						} else if ($k3 == LEAVES) {
							self::$treeBlocksNearby[($xx + 16) * 1024 + ($yy + 16) * 32 + ($zz + 16)] = - 2;
						} else {
							self::$treeBlocksNearby[($xx + 16) * 1024 + ($yy + 16) * 32 + ($zz + 16)] = - 1;
						}
					}
				}
			}

			for ($i2 = 1; $i2 <= 4; $i2 ++) {
				for ($l2 = - 4; $l2 <= 4; $l2 ++) {
					for ($j3 = - 4; $j3 <= 4; $j3 ++) {
						for ($l3 = - 4; $l3 <= 4; $l3 ++) {
							if (self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16) * 32 + ($l3 + 16)] != $i2 - 1) continue;
							if (self::$treeBlocksNearby[(($l2 + 16) - 1) * 1024 + ($j3 + 16) * 32 + ($l3 + 16)] == - 2) {
								self::$treeBlocksNearby[(($l2 + 16) - 1) * 1024 + ($j3 + 16) * 32 + ($l3 + 16)] = $i2;
							}
							if (self::$treeBlocksNearby[($l2 + 16 + 1) * 1024 + ($j3 + 16) * 32 + ($l3 + 16)] == - 2) {
								self::$treeBlocksNearby[($l2 + 16 + 1) * 1024 + ($j3 + 16) * 32 + ($l3 + 16)] = $i2;
							}
							if (self::$treeBlocksNearby[($l2 + 16) * 1024 + (($j3 + 16) - 1) * 32 + ($l3 + 16)] == - 2) {
								self::$treeBlocksNearby[($l2 + 16) * 1024 + (($j3 + 16) - 1) * 32 + ($l3 + 16)] = $i2;
							}
							if (self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16 + 1) * 32 + ($l3 + 16)] == - 2) {
								self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16 + 1) * 32 + ($l3 + 16)] = $i2;
							}
							if (self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16) * 32 + (($l3 + 16) - 1)] == - 2) {
								self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16) * 32 + (($l3 + 16) - 1)] = $i2;
							}
							if (self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16) * 32 + ($l3 + 16 + 1)] == - 2) {
								self::$treeBlocksNearby[($l2 + 16) * 1024 + ($j3 + 16) * 32 + ($l3 + 16 + 1)] = $i2;
							}
						}
					}
				}
			}
			$j2 = self::$treeBlocksNearby[(32/2) * (32*32) + (32/2) * 32 + (32/2)];
			if($j2 >= 0)
			{
				$level->level->setBlockDamage($x, $y, $z, $meta & 0xf7);
			} else
			{
				if(mt_rand(1,20) === 1){ //Saplings
					ServerAPI::request()->api->entity->drop(new Position($x, $y, $z, $level), BlockAPI::getItem(SAPLING, $meta & 0x03, 1));
				}
				if(($meta & 0x03) === LeavesBlock::OAK and mt_rand(1,200) === 1){ //Apples
					ServerAPI::request()->api->entity->drop(new Position($x, $y, $z, $level), BlockAPI::getItem(APPLE, 0, 1));
				}
				$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true, false);
			}
		}
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		//TODO MCPE uses 0x08 for detecting was it placed by player or not
		if($player instanceof Player && $player != PlayerNull::$INSTANCE) $this->meta |= 0x04;
		$this->level->setBlock($this, $this, true, false, true);
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->isShears()){
			$drops[] = array(LEAVES, $this->meta & 0x03, 1);
		}else{
			if(mt_rand(1,20) === 1){ //Saplings
				$drops[] = array(SAPLING, $this->meta & 0x03, 1);
			}
			if(($this->meta & 0x03) === LeavesBlock::OAK and mt_rand(1,100) === 1){ //Apples
				$drops[] = array(APPLE, 0, 1);
			}
		}
		return $drops;
	}
}
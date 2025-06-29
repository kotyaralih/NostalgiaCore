<?php

class FenceGateBlock extends TransparentBlock{
	public static $blockID;
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		$aabb = static::getAABB($level, $x, $y, $z);
		if($aabb == null) return [];
		return [$aabb];
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		$data = $level->level->getBlockDamage($x, $y, $z);
		
		if($data & 4){
			return null;
		}
		
		if($data != 2 && $data != 0){
			return new AxisAlignedBB($x + 0.375, $y, $z, $x + 0.625, $y + 1.5, $z + 1.0);
		}else{
			return new AxisAlignedBB($x, $y, $z + 0.375, $x + 1, $y + 1.5, $z + 0.625);
		}
	}
	
	public function __construct($meta = 0){
		parent::__construct(FENCE_GATE, $meta, "Fence Gate");
		$this->isActivable = true;
		if(($this->meta & 0x04) === 0x04){
			$this->isFullBlock = true;
		}else{
			$this->isFullBlock = false;
		}
		$this->hardness = 15;
		$this->breakTime = 2;
		$this->material = Material::$wood;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 3,
			1 => 0,
			2 => 1,
			3 => 2,
		);
		$this->meta = $faces[$player->entity->getDirection()] & 0x03;
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
	public function onActivate(Item $item, Player $player){
		
		$meta = $this->meta;
		if(($meta & 4) != 0){
			$meta ^= 4;
		}else{
			$direction = ($player->entity->yaw * 4 / 360) + 0.5;
			$blockDirection = (int)$direction;
			if($direction < $blockDirection) --$blockDirection;
			$blockDirection &= 3;
			if(($meta & 3) == (($blockDirection + 2) & 3)){
				$meta = $blockDirection;
			}
			$meta |= 4;
		}
		
		$this->level->fastSetBlockUpdate($this->x, $this->y, $this->z, $this->id, $meta);
		$players = $this->level->players;
		unset($players[$player->CID]);
		$pk = new LevelEventPacket;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->evid = 1003;
		$pk->data = 0;
		ServerAPI::request()->api->player->broadcastPacket($players, $pk); //TODO use blok queue?
		return true;
	}
}

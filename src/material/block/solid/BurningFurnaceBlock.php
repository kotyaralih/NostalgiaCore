<?php

class BurningFurnaceBlock extends SolidBlock implements LightingBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(BURNING_FURNACE, $meta, "Burning Furnace");
		$this->isActivable = true;
		$this->hardness = 17.5;
		$this->breakTime = 3.5;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
		$this->lightEmission = 13;
	}

	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);
		$this->meta = $faces[$player->entity->getDirection()];
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}
	public function getMaxLightValue(){
		return 13;
	}
	public function onBreak(Item $item, Player $player){
		$this->level->setBlock($this, new AirBlock(), true, true, true);
		return true;
	}

	public function onActivate(Item $item, Player $player){

		$server = ServerAPI::request();
		$t = $server->api->tile->get($this);
		$furnace = false;
		if($t !== false){
			$furnace = $t;
		}else{
			$furnace = $server->api->tile->add($this->level, TILE_FURNACE, $this->x, $this->y, $this->z, array(
				"Items" => array(),
				"id" => TILE_FURNACE,
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z			
			));
		}
		
		if(($player->gamemode & 0x01) === 0x01){
			return true;
		}
		
		$furnace->openInventory($player);
		return true;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.7,
			4 => 0.9,
			3 => 1.35,
			2 => 0.45,
			1 => 2.65,
			default => 17.5,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->getPickaxeLevel() >= 1){
			$drops[] = array(FURNACE, 0, 1);
		}
		$t = ServerAPI::request()->api->tile->get($this);
		if($t !== false and $t->class === TILE_FURNACE){
			for($s = 0; $s < FURNACE_SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
		}
		return $drops;
	}
}

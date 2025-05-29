<?php

class SpawnEggItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(SPAWN_EGG, $meta, $count, "Spawn Egg");
		$this->meta = $meta;
		$this->isActivable = true;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ageable = $this->meta === MOB_CHICKEN || $this->meta === MOB_COW || $this->meta === MOB_SHEEP || $this->meta === MOB_PIG;
		$data = array(
			"x" => $block->x + 0.5,
			"y" => $block->y,
			"z" => $block->z + 0.5,
			"IsBaby" => $ageable ? mt_rand(0, 5) == 0 ? 1 : 0 : 0
		);
		$e = ServerAPI::request()->api->entity->add($block->level, ENTITY_MOB, $this->meta, $data);
		ServerAPI::request()->api->entity->spawnToAll($e);
		$player->consumeSingleItem();
		return true;
	}
}
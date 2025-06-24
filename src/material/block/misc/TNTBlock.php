<?php

class TNTBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(TNT, 0, "TNT");
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->isActivable = true;
		$this->material = Material::$explosive;
		$this->lightBlock = 255;
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === FLINT_STEEL){
			$item->hurtAndBreak(1, $player);
			$data = array(
				"x" => $this->x + 0.5,
				"y" => $this->y,
				"z" => $this->z + 0.5,
				"power" => 3,
				"fuse" => 20 * 4, //4 seconds
			);
			$this->level->fastSetBlockUpdate($this->x, $this->y, $this->z, 0, 0, true);
			$e = ServerAPI::request()->api->entity->add($this->level, ENTITY_OBJECT, OBJECT_PRIMEDTNT, $data);
			ServerAPI::request()->api->entity->spawnToAll($e);
			return true;
		}
		return false;
	}
}
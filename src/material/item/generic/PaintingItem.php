<?php

class PaintingItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(PAINTING, 0, $count, "Painting");
		$this->isActivable = true;
	}
	public static $motives = array(
		// Motive Width Height
		"Kebab" => array(1, 1),
		"Aztec" => array(1, 1),
		"Alban" => array(1, 1),
		"Aztec2" => array(1, 1),
		"Bomb" => array(1, 1),
		"Plant" => array(1, 1),
		"Wasteland" => array(1, 1),
		"Wanderer" => array(1, 2),
		"Graham" => array(1, 2),
		"Pool" => array(2, 1),
		"Courbet" => array(2, 1),
		"Sunset" => array(2, 1),
		"Sea" => array(2, 1),
		"Creebet" => array(2, 1),
		"Match" => array(2, 2),
		"Bust" => array(2, 2),
		"Stage" => array(2, 2),
		"Void" => array(2, 2),
		"SkullAndRoses" => array(2, 2),
		//"Wither" => array(2, 2),
		"Fighters" => array(4, 2),
		"Skeleton" => array(4, 3),
		"DonkeyKong" => array(4, 3),
		"Pointer" => array(4, 4),
		"Pigscene" => array(4, 4),
		"BurningSkull" => array(4, 4),
	);
	private static $direction = array(2, 0, 1, 3);
	private static $right = array(4, 5, 3, 2);
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->isTransparent === false && $face > 1 and $block->isSolid === false){
			$server = ServerAPI::request();
			
			if($face < 2 || $face > 5) return;
			$data = array(
				"x" => $target->x,
				"y" => $target->y,
				"z" => $target->z,
				"Direction" => match($face){
					2 => 2,
					3 => 0,
					4 => 1,
					default => 3
				},
				"xPos" => $target->x,
				"yPos" => $target->y,
				"zPos" => $target->z,
			);
			
			$painting = new Painting($level, 0, ENTITY_OBJECT, OBJECT_PAINTING, $data);
			if(!$painting->isValid){
				$player->sendInventory(); //force resync
				return false;
			}
			$painting->eid = $server->api->entity->getNextEID();
			$server->api->entity->addRaw($painting);
			$server->api->entity->spawnToAll($painting);
			
			$player->consumeSingleItem();
			return true;
		}
		return false;
	}

}
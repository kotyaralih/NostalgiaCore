<?php

class BlockAPI{

	public static $creative = [

		//Building
		[STONE, 0],
		[COBBLESTONE, 0],
		[STONE_BRICKS, 0],
		[STONE_BRICKS, 1],
		[STONE_BRICKS, 2],
		[MOSS_STONE, 0],
		[WOODEN_PLANKS, 0],
		[WOODEN_PLANKS, 1],
		[WOODEN_PLANKS, 2],
		[WOODEN_PLANKS, 3],
		[BRICKS, 0],

		[DIRT, 0],
		[GRASS, 0],
		[CLAY_BLOCK, 0],
		[SANDSTONE, 0],
		[SANDSTONE, 1],
		[SANDSTONE, 2],
		[SAND, 0],
		[GRAVEL, 0],
		[TRUNK, 0],
		[TRUNK, 1],
		[TRUNK, 2],
		[TRUNK, 3],
		[NETHER_BRICKS, 0],
		[NETHERRACK, 0],
		[BEDROCK, 0],
		[COBBLESTONE_STAIRS, 0],
		[OAK_WOODEN_STAIRS, 0],
		[SPRUCE_WOODEN_STAIRS, 0],
		[BIRCH_WOODEN_STAIRS, 0],
		[JUNGLE_WOODEN_STAIRS, 0],
		[BRICK_STAIRS, 0],
		[SANDSTONE_STAIRS, 0],
		[STONE_BRICK_STAIRS, 0],
		[NETHER_BRICKS_STAIRS, 0],
		[QUARTZ_STAIRS, 0],
		[SLAB, 0],
		[SLAB, 1],
		[WOODEN_SLAB, 0],
		[WOODEN_SLAB, 1],
		[WOODEN_SLAB, 2],
		[WOODEN_SLAB, 3],
		[SLAB, 3],
		[SLAB, 4],
		[SLAB, 5],
		[SLAB, 6],
		[QUARTZ_BLOCK, 0],
		[QUARTZ_BLOCK, 1],
		[QUARTZ_BLOCK, 2],
		[COAL_ORE, 0],
		[IRON_ORE, 0],
		[GOLD_ORE, 0],
		[DIAMOND_ORE, 0],
		[LAPIS_ORE, 0],
		[REDSTONE_ORE, 0],
		[OBSIDIAN, 0],
		[ICE, 0],
		[SNOW_BLOCK, 0],

		//Decoration
		[COBBLESTONE_WALL, 0],
		[COBBLESTONE_WALL, 1],
		[GOLD_BLOCK, 0],
		[IRON_BLOCK, 0],
		[DIAMOND_BLOCK, 0],
		[LAPIS_BLOCK, 0],
		[COAL_BLOCK, 0],
		[SNOW_LAYER, 0],
		[GLASS, 0],
		[GLOWSTONE_BLOCK, 0],
		[NETHER_REACTOR, 0],
		[WOOL, 0],
		[WOOL, 7],
		[WOOL, 6],
		[WOOL, 5],
		[WOOL, 4],
		[WOOL, 3],
		[WOOL, 2],
		[WOOL, 1],
		[WOOL, 15],
		[WOOL, 14],
		[WOOL, 13],
		[WOOL, 12],
		[WOOL, 11],
		[WOOL, 10],
		[WOOL, 9],
		[WOOL, 8],
		[LADDER, 0],
		[SPONGE, 0],
		[GLASS_PANE, 0],
		[WOODEN_DOOR, 0],
		[TRAPDOOR, 0],
		[FENCE, 0],
		[FENCE_GATE, 0],
		[IRON_BARS, 0],
		[BED, 0],
		[BOOKSHELF, 0],
		[PAINTING, 0],
		[WORKBENCH, 0],
		[STONECUTTER, 0],
		[CHEST, 0],
		[FURNACE, 0],
		[DANDELION, 0],
		[CYAN_FLOWER, 0],
		[BROWN_MUSHROOM, 0],
		[RED_MUSHROOM, 0],
		[CACTUS, 0],
		[MELON_BLOCK, 0],
		[PUMPKIN, 0],
		[LIT_PUMPKIN, 0],
		[COBWEB, 0],
		[HAY_BALE, 0],
		[TALL_GRASS, 1],
		[TALL_GRASS, 2],
		[DEAD_BUSH, 0],
		[SAPLING, 0],
		[SAPLING, 1],
		[SAPLING, 2],
		[SAPLING, 3],
		[LEAVES, 0],
		[LEAVES, 1],
		[LEAVES, 2],
		[LEAVES, 3],
		[CAKE, 0],
		[SIGN, 0],
		[CARPET, 0],
		[CARPET, 7],
		[CARPET, 6],
		[CARPET, 5],
		[CARPET, 4],
		[CARPET, 3],
		[CARPET, 2],
		[CARPET, 1],
		[CARPET, 15],
		[CARPET, 14],
		[CARPET, 13],
		[CARPET, 12],
		[CARPET, 11],
		[CARPET, 10],
		[CARPET, 9],
		[CARPET, 8],

		//Tools
		[RAIL, 0],
		[POWERED_RAIL, 0],
		[TORCH, 0],
		[BUCKET, 0],
		[BUCKET, 8],
		[BUCKET, 10],
		[TNT, 0],
		[IRON_HOE, 0],
		[IRON_SWORD, 0],
		[BOW, 0],
		[SHEARS, 0],
		[FLINT_AND_STEEL, 0],
		[CLOCK, 0],
		[COMPASS, 0],
		[MINECART, 0],
		[SPAWN_EGG, MOB_CHICKEN],
		[SPAWN_EGG, MOB_COW],
		[SPAWN_EGG, MOB_PIG],
		[SPAWN_EGG, MOB_SHEEP],

		//Seeds
		[SUGARCANE, 0],
		[WHEAT, 0],
		[SEEDS, 0],
		[MELON_SEEDS, 0],
		[PUMPKIN_SEEDS, 0],
		[CARROT, 0],
		[POTATO, 0],
		[BEETROOT_SEEDS, 0],
		[EGG, 0],
		[DYE, 0],
		[DYE, 7],
		[DYE, 6],
		[DYE, 5],
		[DYE, 4],
		[DYE, 3],
		[DYE, 2],
		[DYE, 1],
		[DYE, 15],
		[DYE, 14],
		[DYE, 13],
		[DYE, 12],
		[DYE, 11],
		[DYE, 10],
		[DYE, 9],
		[DYE, 8]

	];
	private $server;
	private $scheduledUpdates = [];
	private $randomUpdates = [];

	function __construct(){
		$this->server = ServerAPI::request();
	}

	public static function get($id, $meta = 0, $v = false){
		if(isset(Block::$class[$id])){
			$classname = Block::$class[$id];
			$b = new $classname($meta);
		}else{
			$b = new GenericBlock((int) $id, $meta);
		}
		if($v instanceof Position){
			$b->position($v);
		}
		return $b;
	}

	public function init(){
		$this->server->schedule(1, [$this, "blockUpdateTick"], [], true);
		$this->server->api->console->register("setblock", "<x> <y> <z> <block[:damage]>", [$this, "commandHandler"]);
		$this->server->api->console->register("give", "<player> <item[:damage]> [amount]", [$this, "commandHandler"]);
		$this->server->api->console->register("id", "Check id of item", [$this, "commandHandler"]);
		$this->server->api->console->cmdWhitelist("id");
	}

	public function commandHandler($cmd, $args, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "id":
				if(!($issuer instanceof Player)){
					$output .= "Run this command in-game!";
					break;
				}
				$itemheld = $issuer->getSlot($issuer->slot);
				$output = self::getItem($itemheld->getID(), $itemheld->getMetadata())."";
				break;
			case "setblock":
				if(!($issuer instanceof Player)){
					if(count($args) < 5){
						$output .= "Usage: /setblock <x> <y> <z> <level> <block[:damage]>";
						break;
					}else{
						if(!is_numeric($args[0]) or !is_numeric($args[1]) or !is_numeric($args[2])){
							$output .= "You need to use a numeric coords!";
							break;
						}
						$level = $this->server->api->level->get($args[3]);
						if($level === false){
							$output .= "Unknown level";
							break;
						}
						$block = self::fromString($args[4])->getBlock();
					}
				}else{
					if(count($args) < 4){
						$output .= "Usage: /setblock <x> <y> <z> <block[:damage]>";
						break;
					}
					
					$level = $issuer->entity->level;
					$block = self::fromString($args[3])->getBlock();
				}

				$coords = [];
				for($i = 0; $i < 3; $i++){
					if($args[$i] === '~'){
						if($i === 0) $coord = $issuer->entity->x;
						elseif($i === 1) $coord = $issuer->entity->y;
						else $coord = $issuer->entity->z;
						$coords[$i] = round($coord, 0);
					}
					elseif(is_numeric($args[$i])){
						if($args[$i] >= 0 and $args[$i] <= ($i === 1 ? 127 : 255)){
							$coords[$i] = $args[$i];
						}
					}
				}
				if(!isset($coords[2])){
					$output .= "Usage: /setblock <x> <y> <z> <block[:damage]>";
					break;
				}

				$pos = new Position($coords[0], $coords[1], $coords[2], $level);
				if(!($block instanceof Block)){
					$output .= "Usage: /setblock <x> <y> <z> <block[:damage]>";
					break;
				}else{
					$level->setBlock($pos, $block, true, false, true);
					$output .= "Placed $block in ".implode(", ", $coords).", w:".$level->getName();
				}
				break;
			case "give":
				$player = $this->server->api->player->get($args[0] ?? "");
				
				if($player instanceof Player){			
					$item = self::fromString($args[1] ?? "");
					if(($player->gamemode & 0x01) === 0x01){
						return "Player is in creative mode.";
					}
					if($item->getID() === 0){
						return "You cannot give an air block to a player.";
					}
					
					if(!isset($args[2])){
						$item->count = $item->getMaxStackSize();
					}else{
						$item->count = (int) $args[2];
					}
					
					$player->addItem($item->getID(), $item->getMetadata(), $item->count);
					$output .= "Giving ".$item->count." of ".$item->getName()." (".$item->getID().":".$item->getMetadata().") to ".$player->username;
					break;
				}else{
					$item = self::fromString($args[0] ?? "");	
					if(!($issuer instanceof Player)){
						return "You cant give an item to a non-player.";
					}
					if(($issuer->gamemode & 0x01) === 0x01){
						$output .= "You are in creative mode.";
						break;
					}
					if($item->getID() === 0){
						$output .= "You cannot give an air block to a player.";
						break;
					}

					if(!isset($args[1])){
						$item->count = $item->getMaxStackSize();
					}else{
						$item->count = (int) $args[1];
					}

					$issuer->addItem($item->getID(), $item->getMetadata(), $item->count);
					$output .= "Giving ".$item->count." of ".$item->getName()." (".$item->getID().":".$item->getMetadata().") to ".$issuer->username;
					break;
				}
		}
		return $output;
	}

	public static function blockIDFromString($str){
		if(defined(strtoupper($str))){
			$ida = explode(":", constant(strtoupper($str)));
			if(count($ida) === 2){}
			return (int) $ida[0];
		}else if(is_numeric($str)){
			return (int) $str;
		}
	}
	
	public static function fromString($str, $multiple = false){
		if($multiple === true){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = BlockAPI::fromString($b, false);
			}
			return $blocks;
		}else{
			$b = explode(":", str_replace(" ", "_", trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = ((int) $b[1]) & 0xFFFF;
			}

			if(defined(strtoupper($b[0]))){
				$explodedString = explode(":", constant(strtoupper($b[0])));
				if(count($explodedString) === 2){
					$meta = (int) $explodedString[1];
				}
				$item = BlockAPI::getItem(constant(strtoupper($b[0])), $meta);
				if($item->getID() === AIR and strtoupper($b[0]) !== "AIR"){
					$item = BlockAPI::getItem(((int) $b[0]) & 0xFFFF, $meta);
				}
			}else{
				$item = BlockAPI::getItem(((int) $b[0]) & 0xFFFF, $meta);
			}
			return $item;
		}
	}

	public static function getItem($id, $meta = 0, $count = 1){
		$id = (int) $id;
		if(isset(Item::$class[$id])){
			$classname = Item::$class[$id];
			$i = new $classname($meta, $count);
		}else{
			$i = new Item($id, $meta, $count);
		}
		return $i;
	}

	public function playerBlockBreak(Player $player, Vector3 $vector){
		$target = $player->level->getBlock($vector);
		$item = $player->getSlot($player->slot);

		if($this->server->api->dhandle("player.block.touch", ["type" => "break", "player" => $player, "target" => $target, "item" => $item]) === false){
			if($this->server->api->dhandle("player.block.break.bypass", ["player" => $player, "target" => $target, "item" => $item]) !== true){
				return $this->cancelAction($target, $player, false);
			}
		}

		if((!$target->isBreakable($item, $player) and $this->server->api->dhandle("player.block.break.invalid", ["player" => $player, "target" => $target, "item" => $item]) !== true) or ($player->gamemode & 0x02) === 0x02 or (($player->lastBreak - $player->getLag() / 1000) + $target->getBreakTime($item, $player) - 0.2) >= microtime(true)){
			if($this->server->api->dhandle("player.block.break.bypass", ["player" => $player, "target" => $target, "item" => $item]) !== true){
				return $this->cancelAction($target, $player, false);
			}
		}
		$player->lastBreak = microtime(true);

		if($this->server->api->dhandle("player.block.break", ["player" => $player, "target" => $target, "item" => $item]) !== false){
			$drops = $target->getDrops($item, $player);
			if($target->onBreak($item, $player) === false){
				return $this->cancelAction($target, $player, false);
			}
			if(($player->gamemode & 0x01) === 0 and $item->useOn($target) and $item->getMetadata() >= $item->getMaxDurability()){
				$player->setSlot($player->slot, new Item(AIR, 0, 0), false);
			}
		}else{
			return $this->cancelAction($target, $player, false);
		}


		if(is_array($drops) && ($player->gamemode & 0x01) === 0x00 and count($drops) > 0){
			foreach($drops as $drop){
				$this->server->api->entity->drop(new Position($target->x + 0.5, $target->y, $target->z + 0.5, $target->level), BlockAPI::getItem($drop[0] & 0xFFFF, $drop[1] & 0xFFFF, $drop[2]));
			}
		}
		return false;
	}

	private function cancelAction(Block $block, Player $player, $send = true){
		$player->addBlockUpdateIntoQueue($block->x, $block->y, $block->z, $block->getID(), $block->getMetadata());
		if($send === true){
			$player->sendInventory();
		}
		return false;
	}

	public function playerBlockAction(Player $player, Vector3 $vector, $face, $fx, $fy, $fz){
		if($face < 0 or $face > 5){
			return false;
		}

		$target = $player->level->getBlock($vector);
		$block = $target->getSide($face);
		$item = $player->getSlot($player->slot);

		if($target->getID() === AIR and $this->server->api->dhandle("player.block.place.invalid", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) !== true){ //If no block exists or not allowed in CREATIVE
			if($this->server->api->dhandle("player.block.place.bypass", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) !== true){
				$this->cancelAction($target, $player);
				return $this->cancelAction($block, $player);
			}
		}

		if($this->server->api->dhandle("player.block.touch", ["type" => "place", "player" => $player, "block" => $block, "target" => $target, "item" => $item]) === false){
			if($this->server->api->dhandle("player.block.place.bypass", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) !== true){
				return $this->cancelAction($block, $player);
			}
		}
		
		StaticBlock::getBlock($target->getID())::interact($target->level, $target->x, $target->y, $target->z, $player);

		if($target->isActivable === true){
			if($this->server->api->dhandle("player.block.activate", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) !== false and $target->onActivate($item, $player) === true){
				return false;
			}
		}

		if(($player->gamemode & 0x02) === 0x02){ //Adventure mode!!
			if($this->server->api->dhandle("player.block.place.bypass", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) !== true){
				return $this->cancelAction($block, $player);
			}
		}

		if($block->y > 127 or $block->y < 0){
			return false;
		}

		if($item->isActivable === true and $item->onActivate($player->level, $player, $block, $target, $face, $fx, $fy, $fz) === true){
			if($item->count <= 0){
				$player->setSlot($player->slot, BlockAPI::getItem(AIR, 0, 0), false);
			}
			return false;
		}

		if($item->isPlaceable()){
			$hand = $item->getBlock();
			$hand->position($block);
		}elseif($block->getID() === FIRE){
			$player->level->fastSetBlockUpdate($block->x, $block->y, $block->z, 0, 0, true, false);
			return false;
		}else{
			return $this->cancelAction($block, $player);
		}

		if(!($block->isReplaceable === true or ($hand->getID() === SLAB and $block->getID() === SLAB))){
			return $this->cancelAction($block, $player);
		}

		if($target->isReplaceable === true){
			$block = $target;
			$hand->position($block);
			//$face = -1;
		}
		
		if($hand->isSolid === true && ($hand->getID() != BED_BLOCK && $hand->getID() != CARPET)){
			$aabb = $hand->getAABB($block->level, $block->x, $block->y, $block->z);
			foreach($block->level->players as $pl){
				$playerbb = $pl->entity->boundingBox;
				if(($aabb->maxX > $playerbb->minX && $aabb->minX < $playerbb->maxX) && ($aabb->maxY > ($playerbb->minY+0.21) && $aabb->minY < $playerbb->maxY) && ($aabb->maxZ > $playerbb->minZ && $aabb->minZ < $playerbb->maxZ)){
					return $this->cancelAction($block, $player); //Entity in block
				}
			}
			
			
		}

		if($this->server->api->dhandle("player.block.place", ["player" => $player, "block" => $block, "target" => $target, "item" => $item]) === false){
			return $this->cancelAction($block, $player);
		}elseif($hand->place($item, $player, $block, $target, $face, $fx, $fy, $fz) === false){
			return $this->cancelAction($block, $player);
		}
		if($hand->getID() === SIGN_POST or $hand->getID() === WALL_SIGN){
			$t = $this->server->api->tile->addSign($player->level, $block->x, $block->y, $block->z);
			$t->data["creator"] = $player->username;
		}

		if(($player->gamemode & 0x01) === 0x00){
			--$item->count;
			if($item->count <= 0){
				$player->setSlot($player->slot, BlockAPI::getItem(AIR, 0, 0), false);
			}
		}

		return false;
	}

	public function blockUpdate(Position $pos, $type = BLOCK_UPDATE_NORMAL){
		$block = StaticBlock::getBlock($pos->level->level->getBlockID($pos->x, $pos->y, $pos->z));
		if($block === false){
			return false;
		}
		$level = $block::onUpdate($pos->level, $pos->x, $pos->y, $pos->z, $type);
		
		return $level;
	}
	

	
	public function blockUpdateAround(Position $pos, $type = BLOCK_UPDATE_NORMAL, $delay = false){
		
		if($type == BLOCK_UPDATE_NORMAL){
			try{
				throw new Exception("Deprecated: tried updating $pos using BLOCK_UPDATE_NORMAL.");
			}catch(Exception $e){
				ConsoleAPI::error($e->getMessage());
				ConsoleAPI::error($e->getTraceAsString());
			}
			return;
		}
		
		if($delay !== false){
			$this->scheduleBlockUpdate($pos->getSide(0), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(1), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(2), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(3), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(4), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(5), $delay, $type);
		}else{
			$this->blockUpdate($pos->getSide(0), $type);
			$this->blockUpdate($pos->getSide(1), $type);
			$this->blockUpdate($pos->getSide(2), $type);
			$this->blockUpdate($pos->getSide(3), $type);
			$this->blockUpdate($pos->getSide(4), $type);
			$this->blockUpdate($pos->getSide(5), $type);
		}
	}
	
	public function removeAllBlockUpdates(Level $level){
		$update = $this->server->query("SELECT x,y,z,level,type FROM blockUpdates WHERE level = '{$level->getName()}';");
		if($update instanceof SQLite3Result){
			while(($up = $update->fetchArray(SQLITE3_ASSOC)) !== false){
				$index = $up["x"] . "." . $up["y"] . "." . $up["z"] . "." . $up["level"] . "." . $up["type"];
				unset($this->scheduledUpdates[$index]);
			}
			$this->server->query("DELETE FROM blockUpdates WHERE level = '{$level->getName()}';");
		}
	}
	
	public function scheduleBlockUpdateXYZ(Level $level, $x, $y, $z, $type = BLOCK_UPDATE_SCHEDULED, $delay = false){
		$type = (int) $type;
		if($delay < 0){
			return false;
		}
		
		$index = $x . "." . $y . "." . $z . "." . $level->getName() . "." . $type;
		$delay = microtime(true) + $delay * 0.05;
		if(!isset($this->scheduledUpdates[$index])){
			$this->scheduledUpdates[$index] = new Position($x, $y, $z, $level); //TODO dont create a position
			$this->server->query("INSERT INTO blockUpdates (x, y, z, level, type, delay) VALUES ($x, $y, $z, '{$level->getName()}', $type, $delay);");
			return true;
		}
		return false;
	}
	
	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
		return $this->scheduleBlockUpdateXYZ($pos->level, $pos->x, $pos->y, $pos->z, $type, $delay);
	}


	public function blockUpdateTick(){
		$time = microtime(true);
		if(count($this->scheduledUpdates) > 0){
			$update = $this->server->query("SELECT x,y,z,level,type FROM blockUpdates WHERE delay <= " . $time . ";");
			if($update instanceof SQLite3Result){
				$upp = [];
				while(($up = $update->fetchArray(SQLITE3_ASSOC)) !== false){
					$index = $up["x"] . "." . $up["y"] . "." . $up["z"] . "." . $up["level"] . "." . $up["type"];
					if(isset($this->scheduledUpdates[$index])){
						$upp[] = [(int) $up["type"], $this->scheduledUpdates[$index]];
						unset($this->scheduledUpdates[$index]);
					}
				}
				$this->server->query("DELETE FROM blockUpdates WHERE delay <= " . $time . ";");
				foreach($upp as $b){
					$this->blockUpdate($b[1], $b[0]);
				}
			}
		}
	}
	
	/**
	 * @deprecated random updates are handled differently now.
	 */
	public function nextRandomUpdate(Position $pos){
		
	}
}

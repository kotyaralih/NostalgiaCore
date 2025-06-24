<?php

class NetherReactorBlock extends SolidBlock{
	public static $blockID;
	public static $enableReactor = true;
	public function __construct($meta = 0){
		parent::__construct(NETHER_REACTOR, $meta, "Nether Reactor");
		$this->isActivable = true;
		$this->hardness = 15;
		$this->breakTime = 3;
		$this->material = Material::$metal;
		$this->lightBlock = 255;
	}
	
	public function onActivate(Item $item, Player $player){
		//if(($item->getID() === IRON_SWORD || $item->getID() === WOODEN_SWORD || $item->getID() === STONE_SWORD || $item->getID() === DIAMOND_SWORD || $item->getID() === GOLD_SWORD) /*&& $player->gamemode === 0*/){
		if($this->getMetadata() === 0 && $this->isCorrect($this->getX(),$this->getY(),$this->getZ()) && $this->getY() < 101 && NetherReactorBlock::$enableReactor){
			NetherReactorStructure::buildReactor($this->level, $this->getX(),$this->getY(),$this->getZ());
			$this->meta = 1;
			$this->level->setBlock($this,$this);
			$server = ServerAPI::request();
			$server->schedule(40, array($this, "glow"), 1);
			$server->schedule(60, array($this, "glow"), 2);
			$server->schedule(80, array($this, "glow"), 3);
			$server->schedule(140, array($this, "glow"), 4);
			$server->schedule(200, array($this, "spawnItems"), [0,15,2,true]); //200
			$server->schedule(260, array($this, "spawnItems"), [0,15,"checkPigmen",true]);
			$server->schedule(300, array($this, "spawnItems"), [0,15,"checkPigmen",true]);
			$server->schedule(340, array($this, "spawnItems"), [11,20,"checkPigmen",false]);
			$server->schedule(400, array($this, "spawnItems"), [0,10,"checkPigmen",false]);
			$server->schedule(500, array($this, "spawnItems"), [17,32, "checkPigmen",false]); //500
			$server->schedule(580, array($this, "spawnItems"), [17,32, "checkPigmen",false]);
			$server->schedule(620, array($this, "spawnItems"), [1,32, "checkPigmen",false]);
			$server->schedule(660, array($this, "spawnItems"), [1,32,"checkPigmen",false]);
			$server->schedule(700, array($this, "spawnItems"), [1,32,"checkPigmen",false]);
			$server->schedule(860, array($this, "glow"), 5);
			$server->schedule(880, array($this, "glow"), 6);
			$server->schedule(900, array($this, "glow"), 7);
			$server->schedule(920, array($this, "destroy"));
			return true;
		}
		//}
		
	}
	
	public function destroy(){
		$this->level->setBlock(new Vector3($this->x, $this->y, $this->z),new NetherReactorBlock(2));
		$this->decay($this->x-8, $this->y-3, $this->z-8, 0, 17, 16, 2, 34, 1, 0, 17, 1);
		$this->decay($this->x-8, $this->y-3, $this->z-8, 1, 16, 1, 2, 34, 1, 0, 17, 16);
		$this->decay($this->x-8, $this->y-3, $this->z-8, 3, 14, 10, 8, 34, 1, 3, 14, 1);
		$this->decay($this->x-8, $this->y-3, $this->z-8, 4, 13, 1, 8, 34, 1, 3, 14, 10);
		$this->decay($this->x-8, $this->y-3, $this->z-8, 5, 12, 6, 14, 34, 1, 5, 12, 1);
		$this->decay($this->x-8, $this->y-3, $this->z-8, 6, 11, 1, 14, 34, 1, 5, 12, 16);	
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return [[NETHER_REACTOR, 0, 1]];
		}
	}
	
	private function decay($x, $y, $z, $aOne, $aTwo, $aThree, $bOne, $bTwo, $bThree, $cOne, $cTwo, $cThree) {
		for($a = $aOne; $a < $aTwo; $a += $aThree) {
			for($b = $bOne; $b < $bTwo; $b += $bThree) {
				for($c = $cOne; $c < $cTwo; $c += $cThree) {
					if ($this->level->level->getBlockID($x+$a, $y+$b, $z+$c) === NETHERRACK && lcg_value() > 0.75){
						$this->level->fastSetBlockUpdate($x+$a, $y+$b, $z+$c, 0, 0);
					}
				}
			}
		}
	}
	
	private function pigmenCheck($x,$y,$z) {
		$pigCount = 0;
		$server = ServerAPI::request();
		$allEntities = $server->api->entity->entities;
		foreach($allEntities as $entity) {
			if($entity->type === MOB_PIGMAN && $entity->x < $x + 8 && $entity->x > $x - 8 && $entity->z < $z + 8 && $entity->z > $z - 8 && $entity->y > $y - 2 && $entity->y < $y + 3){
				$pigCount += 1;
			}
		}
		return $pigCount < 3 ? $pigCount < 2 ? 2 : 1 : 0;
	}
	public function spawnItems($data) {
		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		$minAmount = $data[0];
		$maxAmount = $data[1];
		$pigmen = $data[2] === "checkPigmen" ? $this->pigmenCheck($x, $y, $z) : $data[2];
		$forceAmount = $data[3];
		$server = ServerAPI::request();
		if(!$forceAmount){
			$spawnNumber = $minAmount + floor(lcg_value()*($maxAmount-$minAmount+1));
		}
		else{
			$spawnNumber = $maxAmount;
		}
		for($i = 0; $i < $spawnNumber; $i++) {
			$randomRange = floor(lcg_value()*5+3);
			$shiftX = cos(floor(lcg_value()*360)*(pi()/180));
			$shiftZ = sin(floor(lcg_value()*360)*(pi()/180));
			if(Utils::chance(5)) $randomID = $this->rarePossibleLoot[array_rand($this->rarePossibleLoot)];
			else $randomID = $this->possibleLoot[array_rand($this->possibleLoot)];
			$server->api->entity->drop(new Position($x+($shiftX*$randomRange)+0.5, $y, $z+($shiftZ*$randomRange)+0.5, $this->level), BlockAPI::getItem($randomID, 0, 1));
		}
		for($i = 0; $i < $pigmen; $i++) {
			$randomRange = floor(lcg_value()*5+3);
			$shiftX = cos(floor(lcg_value()*360)*(pi()/180));
			$shiftZ = sin(floor(lcg_value()*360)*(pi()/180));
			$data = array(
					"x" => $x+($shiftX*$randomRange)+0.5,
					"y" => $y,
					"z" => $z+($shiftZ*$randomRange)+0.5,
				);
			$e = $server->api->entity->add($this->level, ENTITY_MOB, MOB_PIGMAN, $data);
			$server->api->entity->spawnToAll($e);
		}
	}
	public function glow($part){
		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		switch($part){
			case 1:
				$this->level->setBlock(new Vector3($x, $y-1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y-1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y-1, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y-1, $z-1),new GlowingObsidianBlock);
				break;
			case 2:
				$this->level->setBlock(new Vector3($x+1, $y, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y, $z-1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y, $z-1),new GlowingObsidianBlock);
				break;
			case 3:
				$this->level->setBlock(new Vector3($x, $y+1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y+1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y+1, $z),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y+1, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y+1, $z-1),new GlowingObsidianBlock);
				break;
			case 4:
				$this->level->setBlock(new Vector3($x+1, $y-1, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y-1, $z-1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z+1),new GlowingObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z-1),new GlowingObsidianBlock);
				break;
			case 5:
				$this->level->setBlock(new Vector3($x, $y+1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y+1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y+1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y+1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y+1, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y+1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y+1, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y+1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y+1, $z-1),new ObsidianBlock);
				break;
			case 6:
				$this->level->setBlock(new Vector3($x, $y, $z), new NetherReactorBlock(2));
				$this->level->setBlock(new Vector3($x+1, $y, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y, $z-1),new ObsidianBlock);
				break;
			case 7:
				$this->level->setBlock(new Vector3($x, $y-1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y-1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y-1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x, $y-1, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y-1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x+1, $y-1, $z-1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z+1),new ObsidianBlock);
				$this->level->setBlock(new Vector3($x-1, $y-1, $z-1),new ObsidianBlock);
				break;
		}
	}
	private function isCorrect($x, $y, $z){
		$offsetX = -1;
		$offsetZ = -1;
		foreach($this->core as $yOffset => $layer){
			foreach($layer as $line){
				foreach(str_split($line) as $char){
					$b = $this->level->level->getBlockID($x + $offsetX, $y + $yOffset, $z + $offsetZ);
					switch($char){
						case "G":
							if($b === GOLD_BLOCK){
								break;
							}
							return false;
						case "C":
							if($b === COBBLESTONE){
								break;
							}
							return false;
						case "R":
							if($b === NETHER_REACTOR and $this->level->level->getBlockDamage($x + $offsetX, $y + $yOffset, $z + $offsetZ) === 0){
								break;
							}
							return false;
						case " ":
							if($b === 0){
								break;
							}
							return false;
					}
					++$offsetX;
				}
				++$offsetZ;
				$offsetX = -1;
			}
			$offsetZ = -1;
		}
		return true;
	}
	
	private $possibleLoot = [
		GLOWSTONE_DUST, QUARTZ, CACTUS, SUGARCANE, BROWN_MUSHROOM, RED_MUSHROOM, PUMPKIN_SEEDS, MELON_SEEDS
	];
	
	private $rarePossibleLoot = [
		BOW, BED, BOWL, ARROW, WOODEN_DOOR, FEATHER, PAINTING, BONE, DANDELION
	];
	
	private $core = [
		-1 => ["GCG", "CCC", "GCG"],
		0 => ["C C", " R ", "C C",],
		1 =>[" C ", "CCC", " C "]
	];
}

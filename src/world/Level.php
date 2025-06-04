<?php

class Level{
	public static $disableEmojisOnSigns = true;
	/**
	 * @var Config
	 */
	public $entities;
	/**
	 * This is an array of entities in this world. 
	 * @var Entity[]
	 */
	public $entityList;
	
	public $entityListPositioned = [];
	public $entitiesInLove = [];
	
	/**
	 * @var Player[]
	 */
	public $players = [];
	
	public $tiles, $blockUpdates, $nextSave, $level, $mobSpawner, $totalMobsAmount = 0;
	private $time, $startCheck, $startTime, $server, $name, $usedChunks, $changedBlocks, $changedCount, $stopTime;
	
	public $randInt1, $randInt2;
	public $queuedBlockUpdates = [];
	
	public $forceDisableBlockQueue = false;
	
	public static $randomUpdateBlocks = [
		FIRE => true,
		FARMLAND => true,
		GLOWING_REDSTONE_ORE => true,
		BEETROOT_BLOCK => true,
		SAPLING => true,
		CACTUS => true,
		CARROT_BLOCK => true,
		MELON_STEM => true,
		POTATO_BLOCK => true,
		PUMPKIN_STEM => true,
		SUGARCANE_BLOCK => true,
		WHEAT_BLOCK => true,
		DIRT => true,
		GRASS => true,
		ICE => true,
		LEAVES => true
	];
	
	public function __construct(PMFLevel $level, Config $entities, Config $tiles, Config $blockUpdates, $name){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->level->level = $this;
		$this->entityList = [];
		$this->entities = $entities;
		$this->tiles = $tiles;
		$this->blockUpdates = $blockUpdates;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->nextSave = $this->startCheck = microtime(true);
		$this->nextSave += 90;
		$this->stopTime = false;
		$this->server->schedule(15, [$this, "checkThings"], [], true);
		$this->server->schedule(20 * 13, [$this, "checkTime"], [], true);
		$this->name = $name;
		$this->usedChunks = [];
		$this->changedBlocks = [];
		$this->changedCount = [];
		$this->mobSpawner = new MobSpawner($this);
		$this->randInt1 = 0x283AE83;
		$this->randInt2 = 0x3C6EF35F;
	}

	public function close(){
		if(isset($this->level)){
			$this->save(true, true, true, true);
			$this->server->api->block->removeAllBlockUpdates($this);
			foreach($this->server->api->player->getAll($this) as $player){
				$player->teleport($this->server->spawn);
			}
			foreach($this->entityList as $entity){
				if($entity->class !== ENTITY_PLAYER){
					$entity->close();
				}
			}
			foreach($this->server->api->tile->getAll($this) as $tile){
				$tile->close();
			}
			$this->level->close();
			unset($this->level);
		}
		unset($this->mobSpawner->level);
	}
	
	public function isTopSolidBlocking($x, $y, $z){
		$idmeta = $this->level->getBlock($x, $y, $z);
		$id = $idmeta[0];
		$meta = $idmeta[1];
		if($id == 0) return false;
		if(StaticBlock::getIsTransparent($id)) return false;
		
		return true;
	}

	public function rayTraceBlocks(Vector3 $start, Vector3 $end){
		
		$par3 = false; //TODO move to params?
		$par4 = true;
		
		$xStart = floor($start->x);
		$yStart = floor($start->y);
		$zStart = floor($start->z);
		$xEnd = floor($end->x);
		$yEnd = floor($end->y);
		$zEnd = floor($end->z);
		
		[$startID, $startMeta] = $this->level->getBlock($xStart, $yStart, $zStart);
		$block = StaticBlock::getBlock($startID);

		//$block::updateShape($this, $xStart, $yStart, $zStart); //TODO better way to do it
		$aabb = $block::getAABB($this, $xStart, $yStart, $zStart);
		if($startID > 0 && $aabb != null){ //TODO also block::canColideCheck
			$v14 = $block::clip($this, $xStart, $yStart, $zStart, $start, $end);
			if($v14 != null) return $v14;
		}
		
		for($i = 0; $i <= 200; ++$i){
			
			if(is_nan($start->x) || is_nan($start->y) || is_nan($start->z) || ($xStart == $xEnd && $yStart == $yEnd && $zStart == $zEnd)){ //also add checks for nan?
				return null;
			}
			
			$v39 = $v40 = $v41 = true;
			$v15 = $v17 = $v19 = 999.0; //nice mojang
			
			if($xEnd > $xStart) $v15 = $xStart + 1;
			elseif($xEnd < $xStart) $v15 = $xStart;
			else $v39 = false;
			
			if($yEnd > $yStart) $v17 = $yStart + 1;
			elseif($yEnd < $yStart) $v17 = $yStart;
			else $v40 = false;
			
			if($zEnd > $zStart) $v19 = $zStart + 1;
			elseif($zEnd < $zStart) $v19 = $zStart;
			else $v41 = false;
			
			$v21 = $v23 = $v25 = 999.0; //nice mojang x2
			$v27 = $end->x - $start->x;
			$v29 = $end->y - $start->y;
			$v31 = $end->z - $start->z;
			
			if($v39) $v21 = $v27 == 0 ? ($v15 - $start->x)*INF : ($v15 - $start->x) / $v27;
			if($v40) $v23 = $v29 == 0 ? ($v17 - $start->y)*INF : ($v17 - $start->y) / $v29;
			if($v41) $v25 = $v31 == 0 ? ($v19 - $start->z)*INF : ($v19 - $start->z) / $v31;
			
			if($v21 < $v23 && $v21 < $v25){
				$v42 = $xEnd > $xStart ? 4 : 5;
				
				$start->x = $v15;
				$start->y += $v29 * $v21;
				$start->z += $v31 * $v21;
			}elseif($v23 < $v25){
				$v42 = $yEnd > $yStart ? 0 : 1;
				
				$start->x += $v27 * $v23;
				$start->y = $v17;
				$start->z += $v31 * $v23;
			}else{
				$v42 = $zEnd > $zStart ? 2 : 3;
				
				$start->x += $v27 * $v25;
				$start->y += $v29 * $v25;
				$start->z = $v19;
			}
			
			$xStart = floor($start->x);
			if($v42 == 5) --$xStart;
			
			$yStart = floor($start->y);
			if($v42 == 1) --$yStart;
			
			$zStart = floor($start->z);
			if($v42 == 3) --$zStart;
			
			[$blockID, $blockMeta] = $this->level->getBlock($xStart, $yStart, $zStart);
			$block = StaticBlock::getBlock($blockID);

			$aabb = $block::getAABB($this, $xStart, $yStart, $zStart);
			if($blockID > 0 && $aabb != null){ //TODO also block::canColideCheck
				$v38 = $block::clip($this, $xStart, $yStart, $zStart, $start, $end);
				if($v38 != null) return $v38;
			}
		}
		
		return null;
	}
	 
	public function isLavaInBB($aabb){
		$minX = floor($aabb->minX);
		$maxX = floor($aabb->maxX + 1);
		$minY = floor($aabb->minY);
		$maxY = floor($aabb->maxY + 1);
		$minZ = floor($aabb->minZ);
		$maxZ = floor($aabb->maxZ + 1);
		
		for($x = $minX; $x < $maxX; ++$x){
			for($y = $minY; $y < $maxY; ++$y){
				for($z = $minZ; $z < $maxZ; ++$z){
					$blockId = $this->level->getBlockID($x, $y, $z);
					
					if($blockId == LAVA || $blockId == STILL_LAVA) return true;
				}
			}
		}
		
		return false;
	}
	
	public function handleMaterialAcceleration(AxisAlignedBB $aabb, $materialType, Entity $entity){
		$minX = floor($aabb->minX);
		$maxX = ceil($aabb->maxX);
		$minY = floor($aabb->minY);
		$maxY = ceil($aabb->maxY);
		$minZ = floor($aabb->minZ);
		$maxZ = ceil($aabb->maxZ);
		
		//1.5.2 checks that all chunks exist, not needed here i think
		
		$appliedVelocity = false;
		$velocityVec = new Vector3(0, 0, 0);
		for($x = $minX; $x < $maxX; ++$x){
			for($y = $minY; $y < $maxY; ++$y){
				for($z = $minZ; $z < $maxZ; ++$z){
					[$block, $meta] = $this->level->getBlock($x, $y, $z);
					if(($materialType == 0 && ($block == WATER || $block == STILL_WATER)) || ($materialType == 1 && ($block == LAVA || $block == STILL_LAVA))){ //TODO better material system
						$v16 = ($y + 1) - LiquidBlock::getPercentAir($meta);
						if($maxY >= $v16){
							$appliedVelocity = true;
							StaticBlock::$prealloc[$block]::addVelocityToEntity($this, $x, $y, $z, $entity, $velocityVec);
						}
						
					}
				}
			}
		}
		
		if($velocityVec->length() > 0){ //also checks is player flying
			$velocityVec = $velocityVec->normalize(); //TODO do not use vec methods
			$v18 = 0.014;
			$entity->speedX += $velocityVec->x * $v18;
			$entity->speedY += $velocityVec->y * $v18;
			$entity->speedZ += $velocityVec->z * $v18;
		}
		
		return $appliedVelocity;
	}
	/**
	 * @param Entity $e
	 * @param AxisAlignedBB $aABB
	 * @return AxisAlignedBB[]
	 */
	public function getCubes(Entity $e, AxisAlignedBB $aABB) {
		$aABBs = [];
		$x0 = floor($aABB->minX);
		$x1 = floor($aABB->maxX + 1);
		$y0 = floor($aABB->minY);
		$y1 = floor($aABB->maxY + 1);
		$z0 = floor($aABB->minZ);
		$z1 = floor($aABB->maxZ + 1);
		
		for($x = $x0; $x < $x1; ++$x) {
			for($z = $z0; $z < $z1; ++$z) {
				for($y = $y0 - 1; $y < $y1; ++$y) {
					$bid = $this->level->getBlockID($x, $y, $z);
					if($bid > 0){
						$blockBounds = StaticBlock::$prealloc[$bid]::getCollisionBoundingBoxes($this, $x, $y, $z, $e); //StaticBlock::getBoundingBoxForBlockCoords($b, $x, $y, $z);
						
						foreach($blockBounds as $blockBound){
							if($aABB->intersectsWith($blockBound)) $aABBs[] = $blockBound;
						}
					}
				}
			}
		}
		
		return $aABBs;
	}
	
	public function __destruct(){
		$this->close();
	}
	
	public function isDay(){
		return $this->getTime() % 19200 < TimeAPI::$phases["sunset"];
	}
	public function isNight(){
		$t = $this->getTime() % 19200;
		return $t < TimeAPI::$phases["sunrise"] && $t > TimeAPI::$phases["sunset"];
	}
	public function save($force = false, $entities = true, $tiles = true, $blockupdates = true){
		if(!isset($this->level)){
			return false;
		}
		if($this->server->saveEnabled === false and $force === false){
			return;
		}

		if($entities){
			$entities = [];
			
			foreach($this->entityList as $entity){
				if($entity instanceof Entity){
					$entities[] = $entity->createSaveData();
				}
			}
			$this->entities->setAll($entities);
			$this->entities->save();
		}
		if($tiles){
			$tiles = [];
			foreach($this->server->api->tile->getAll($this) as $tile){
				$tiles[] = $tile->data;
			}
			$this->tiles->setAll($tiles);
			$this->tiles->save();
		}
		if($blockupdates){
			$blockUpdates = [];
			$updates = $this->server->query("SELECT x,y,z,type,delay FROM blockUpdates WHERE level = '" . $this->getName() . "';");
			if($updates !== false and $updates !== true){
				$timeu = microtime(true);
				while(($bupdate = $updates->fetchArray(SQLITE3_ASSOC)) !== false){
					$bupdate["delay"] = max(1, ($bupdate["delay"] - $timeu) * 20);
					$blockUpdates[] = $bupdate;
				}
			}

			$this->blockUpdates->setAll($blockUpdates);
			$this->blockUpdates->save();
		}

		$this->level->setData("time", (int) $this->time);
		$this->level->doSaveRound();
		$this->level->saveData();
		$this->nextSave = microtime(true) + 45;
	}

	public function getName(){
		return $this->name;//return $this->level->getData("name");
	}

	public function useChunk($X, $Z, Player $player){
		if(!isset($this->usedChunks[$X . "." . $Z])){
			$this->usedChunks[$X . "." . $Z] = [];
		}
		$this->usedChunks[$X . "." . $Z][$player->CID] = true;
		if(isset($this->level)){
			$this->level->loadChunk($X, $Z);
		}
	}

	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][$player->CID]);
		}
	}

	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[$X . "." . $Z][$player->CID]);
	}
	
	public function checkCollisionsFor(Entity $e){
		if($e->level->getName() != $this->getName()){
			return false; //not the same world
		}
		foreach($this->entityList as $e1){
			if($e->boundingBox->intersectsWith($e1->boundingBox) && $e1->isCollidable){
				$e->onCollideWith($e1);
				$e1->onCollideWith($e);
			}
		}
	}
	public function isObstructed($e){
		
	}
	
	public function checkSleep(){ //TODO events?
		if(count($this->players) == 0) return false;
		if($this->server->api->time->getPhase($this)  === "night"){ //TODO vanilla
			foreach($this->players as $p){
				if(!$p->isSleeping || $p->sleepingTime < 100){
					return false;
				}
			}
			$this->server->api->time->set("day", $this);
		}
		foreach($this->players as $p){
			$p->stopSleep();
		}
	}
	
	public function checkThings(){
		if(!isset($this->level)){
			return false;
		}
		$now = microtime(true);
		$this->players = $this->server->api->player->getAll($this);
		
		if(count($this->changedCount) > 0){
			arsort($this->changedCount);
			$reorder = false;
			foreach($this->changedCount as $index => $count){
				if($count < 582){//Optimal value, calculated using the relation between minichunks and single packets
					break;
				}
				foreach($this->players as $p){
					unset($p->chunksLoaded[$index]);
				}
				$reorder = true;
				unset($this->changedBlocks[$index]);
			}
			$this->changedCount = [];
			
			if($reorder){
				foreach($this->players as $p){
					$p->orderChunks();
				}
			}
			
			if(count($this->changedBlocks) > 0){
				foreach($this->changedBlocks as $i => $blocks){
					foreach($blocks as $b){
						$x = ($b >> 32) & 0xff;
						$y = ($b >> 24) & 0xff;
						$z = ($b >> 16) & 0xff;
						$id =($b >> 8) & 0xff;
						$meta = $b & 0xff;
						foreach($this->players as $p){
							$p->addBlockUpdateIntoQueue($x, $y, $z, $id, $meta);
						}
					}
					unset($this->changedBlocks[$i]);
				}
				$this->changedBlocks = [];
			}
		}
		
		if($this->nextSave < $now){
			$this->save(false, true, true, true);
		}
	}

	public function isSpawnChunk($X, $Z){
		$spawnX = $this->level->getData("spawnX") >> 4;
		$spawnZ = $this->level->getData("spawnZ") >> 4;
		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	public function getBlockRaw(Vector3 $pos){
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);
		return BlockAPI::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

	public function setBlockRaw(Vector3 $pos, Block $block, $direct = true, $send = true){
		if(($ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata())) === true and $send !== false){
			if($direct === true){
				$this->addBlockToSendQueue($pos->x, $pos->y, $pos->z, $block->id, $block->meta);
			}elseif($direct === false){
				if(!($pos instanceof Position)){
					$pos = new Position($pos->x, $pos->y, $pos->z, $this);
				}
				$block->position($pos);
				$i = ($pos->x >> 4) . ":" . ($pos->y >> 4) . ":" . ($pos->z >> 4);
				if(!isset($this->changedBlocks[$i])){
					$this->changedBlocks[$i] = [];
					$this->changedCount[$i] = 0;
				}
				$this->changedBlocks[$i][] = ($block->x & 0xff) << 32 | ($block->y & 0xff) << 24 | ($block->z & 0xff) << 16 | ($block->getID() & 0xff) << 8 | ($block->meta & 0xff);
				++$this->changedCount[$i];
			}
		}
		return $ret;
	}
	public function fastSetBlockUpdateMeta($x, $y, $z, $meta, $updateBlock = false){
		$this->level->setBlockDamage($x, $y, $z, $meta);
		$id = $this->level->getBlockID($x, $y, $z);
		$this->addBlockToSendQueue($x, $y, $z, $id, $meta);
		if($updateBlock){
			$this->updateNeighborsAt($x, $y, $z, $id);
		}
	}
	
	public function updateNeighborsAt($x, $y, $z, $oldID){
		$block = $this->level->getBlockID($x - 1, $y, $z);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x - 1, $y, $z, $x, $y, $z, $oldID);
		$block = $this->level->getBlockID($x + 1, $y, $z);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x + 1, $y, $z, $x, $y, $z, $oldID);
		
		$block = $this->level->getBlockID($x, $y - 1, $z);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x, $y - 1, $z, $x, $y, $z, $oldID);
		$block = $this->level->getBlockID($x, $y + 1, $z);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x, $y + 1, $z, $x, $y, $z, $oldID);
		
		$block = $this->level->getBlockID($x, $y, $z - 1);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x, $y, $z - 1, $x, $y, $z, $oldID);
		$block = $this->level->getBlockID($x, $y, $z + 1);
		if($block) StaticBlock::getBlock($block)::neighborChanged($this, $x, $y, $z + 1, $x, $y, $z, $oldID);
	}
	
	public function fastSetBlockUpdate($x, $y, $z, $id, $meta, $updateBlocksAround = false, $tiles = false){
		$oldID = $this->level->getBlockID($x, $y, $z);
		
		$this->level->setBlock($x, $y, $z, $id, $meta);
		
		if($tiles){ //TODO rewrite
			$this->server->api->tile->invalidateAll($this, $x, $y, $z);
		}
		if($updateBlocksAround){
			self::updateNeighborsAt($x, $y, $z, $oldID);
		}
		$this->addBlockToSendQueue($x, $y, $z, $id, $meta);
	}
	
	public function onTick(PocketMinecraftServer $server, $currentTime){
		if(!$this->stopTime) ++$this->time;
		for($cX = 0; $cX < 16; ++$cX){
			for($cZ = 0; $cZ < 16; ++$cZ){
				$index = $this->level->getIndex($cX, $cZ);
				if(!isset($this->level->chunks[$index]) || $this->level->chunks[$index] === false) continue;
				for($c = 0; $c <= 20; ++$c){
					$xyz = mt_rand(0, 0xffffffff) >> 2;
					$x = $xyz & 0xf;
					$z = ($xyz >> 8) & 0xf;
					$y = ($xyz >> 16) & 0x7f;
					$id = $this->level->fastGetBlockID($cX, $y >> 4, $cZ, $x, $y & 0xf, $z, $index);
					if(isset(self::$randomUpdateBlocks[$id])){
						$cl = StaticBlock::$prealloc[$id];
						$cl::onRandomTick($this, ($cX << 4) + $x, $y, $z + ($cZ << 4));
					}
				}
			}
		}
		$this->totalMobsAmount = 0;
		$post = [];
		
		foreach($this->entityList as $k => $e){
			if(!($e instanceof Entity)){
				unset($this->entityList[$k]);
				unset($this->server->entities[$k]);
				//TODO try to remove from $entityListPositioned?
				continue;
			}
			
			$dd = CORRECT_ENTITY_CLASSES[$e->class] ?? false;
			if($dd === false || ($dd !== true && !isset($dd[$e->type]))){
				ConsoleAPI::warn("Entity $k has invalid entity! {$e->class} {$e->type}");
				$e->close();
				
				unset($this->entityList[$k]);
				unset($this->server->entities[$k]);
				
				$curChunkX = (int)$e->x >> 4;
				$curChunkZ = (int)$e->z >> 4;
				$index = "$curChunkX $curChunkZ";
				
				if(isset($this->entityListPositioned[$index][$k])){
					unset($this->entityListPositioned[$index][$k]);
				}
				
				continue;
			}
			$curChunkX = (int)$e->x >> 4;
			$curChunkZ = (int)$e->z >> 4;
			if($e->class === ENTITY_MOB && !$e->isPlayer()){
				++$this->totalMobsAmount;
			}
			
			$e->update($currentTime);
			if(!$e->isPlayer()) $post[] = $k;
			
			if($e instanceof Entity){
				$newChunkX = (int)$e->x >> 4;
				$newChunkZ = (int)$e->z >> 4;
				if($e->chunkX != $newChunkX || $e->chunkZ != $newChunkZ){
					$oldIndex = "{$e->chunkX} {$e->chunkZ}";
					unset($this->entityListPositioned[$oldIndex][$e->eid]);
					
					if($e->level == $this){
						$e->chunkX = $newChunkX;
						$e->chunkZ = $newChunkZ;
						$newIndex = "$newChunkX $newChunkZ";
						$this->entityListPositioned[$newIndex][$e->eid] = $e->eid;
					}
				}
				if($e->level != $this && isset($this->entityListPositioned["$curChunkX $curChunkZ"])){
					unset($this->entityListPositioned["$curChunkX $curChunkZ"][$e->eid]);
				}elseif($curChunkX != $newChunkX || $curChunkZ != $newChunkZ){
					$index = "$curChunkX $curChunkZ"; //while creating index like $curChunkX << 32 | $curChunkZ is faster, placing it inside list is slow
					$newIndex = "$newChunkX $newChunkZ";
					unset($this->entityListPositioned[$index][$e->eid]);
					$this->entityListPositioned[$newIndex][$e->eid] = $e->eid; //set to e->eid to avoid possible memory leaks
				}
				
				if($e->searchForClosestPlayers){
					$e->handlePrePlayerSearcher();
					
					foreach($this->players as $player){
						$dist = ($e->x - $player->entity->x)*($e->x - $player->entity->x) + ($e->y - $player->entity->y)*($e->y - $player->entity->y) + ($e->z - $player->entity->z)*($e->z - $player->entity->z);
						$e->handlePlayerSearcher($player, $dist);
					}
				}
				
				
				
			}elseif(isset($this->entityListPositioned["$curChunkX $curChunkZ"])){
				unset($this->entityListPositioned["$curChunkX $curChunkZ"][$k]);
			}
		}
		
		$this->checkSleep();
		
		if($server->ticks % 100 === 0){
			$this->mobSpawner->handle();
		}
		
		
		foreach($this->players as $player){
			foreach($post as $eid){
				$e = $this->entityList[$eid] ?? false;
				if(!($e instanceof Entity)){
					continue;
				}
				$player->addEntityMovementUpdateToQueue($e);
			}
			$player->sendEntityMovementUpdateQueue();
			
			foreach($this->queuedBlockUpdates as $ind => $update){
				$x = $update[0];
				$y = $update[1];
				$z = $update[2];
				$idmeta = $this->level->getBlock($x, $y, $z);
				$id = $idmeta[0];
				$meta = $idmeta[1];
				$player->addBlockUpdateIntoQueue($x, $y, $z, $id, $meta);
			}
			$player->sendBlockUpdateQueue();
		}
		
		$this->queuedBlockUpdates = [];
	}
	
	public function isBoundingBoxOnFire(AxisAlignedBB $bb){
		$minX = floor($bb->minX);
		$maxX = floor($bb->maxX + 1);
		$minY = floor($bb->minY);
		$maxY = floor($bb->maxY + 1);
		$minZ = floor($bb->minZ);
		$maxZ = floor($bb->maxZ + 1);
		
		for($x = $minX; $x < $maxX; ++$x){
			for($y = $minY; $y < $maxY; ++$y){
				for($z = $minZ; $z < $maxZ; ++$z){
					$blockAt = $this->level->getBlockID($x, $y, $z);
					if($blockAt == FIRE || $blockAt == STILL_LAVA || $blockAt == LAVA) return true;
				}
			}
		}
		
		return false;
	}
	public function getEntitiesInAABBOfType(AxisAlignedBB $bb, $class){
		$minChunkX = ((int)($bb->minX)) >> 4;
		$minChunkZ = ((int)($bb->minZ)) >> 4;
		$maxChunkX = ((int)($bb->maxX)) >> 4;
		$maxChunkZ = ((int)($bb->maxZ)) >> 4;
		$ents = [];
		for($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX){
			for($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ){
				$ind = "$chunkX $chunkZ";
				foreach($this->entityListPositioned[$ind] ?? [] as $ind2 => $entid){
					if(isset($this->entityList[$entid]) && $this->entityList[$entid] instanceof Entity && $this->entityList[$entid]->class === $class && $this->entityList[$entid]->boundingBox->intersectsWith($bb)){
						$ents[$entid] = $this->entityList[$entid];
					}elseif(!isset($this->entityList[$entid])){
						ConsoleAPI::debug("Removing entity from level array at index $ind/$ind2: $entid");
						unset($this->entityListPositioned[$ind][$ind2]);
					}
				}
			}
		}
		return $ents;
	}
	/**
	 * @param AxisAlignedBB $bb
	 * @return Entity[]
	 */
	public function getEntitiesInAABB(AxisAlignedBB $bb){
		$minChunkX = ((int)($bb->minX)) >> 4;
		$minChunkZ = ((int)($bb->minZ)) >> 4;
		$maxChunkX = ((int)($bb->maxX)) >> 4;
		$maxChunkZ = ((int)($bb->maxZ)) >> 4;
		$ents = [];
		//TODO also index by chunkY?
		for($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX){
			for($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ){
				$ind = "$chunkX $chunkZ";
				foreach($this->entityListPositioned[$ind] ?? [] as $ind2 => $entid){
					if(isset($this->entityList[$entid]) && $this->entityList[$entid] instanceof Entity && $this->entityList[$entid]->boundingBox->intersectsWith($bb)){
						$ents[$entid] = $this->entityList[$entid];
					}elseif(!isset($this->entityList[$entid])){
						ConsoleAPI::debug("Removing entity from level array at index $ind/$ind2: $entid");
						unset($this->entityListPositioned[$ind][$ind2]);
					}
				}
			}
		}
		return $ents;
	}
	
	public function addBlockToSendQueue($x, $y, $z, $id, $meta){
		if(!$this->forceDisableBlockQueue){
			$this->queuedBlockUpdates["$x $y $z"] = [$x, $y, $z, $id, $meta];
		}
	}
	
	public function setBlock(Vector3 $pos, Block $block, $update = true, $tiles = false, $direct = false){
		if(!isset($this->level) or (($pos instanceof Position) and $pos->level !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}
		$oldID = $this->level->getBlockID($pos->x, $pos->y, $pos->z);
		$ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata());
		if($ret === true){ 
			if(!($pos instanceof Position)){
				$pos = new Position($pos->x, $pos->y, $pos->z, $this);
			}
			$block->position($pos);
			if($direct === true){
				$this->addBlockToSendQueue($pos->x, $pos->y, $pos->z, $block->id, $block->meta);
			}else{
				$i = ($pos->x >> 4) . ":" . ($pos->y >> 4) . ":" . ($pos->z >> 4);
				if(!isset($this->changedBlocks[$i])){
					$this->changedBlocks[$i] = [];
					$this->changedCount[$i] = 0;
				}
				$this->changedBlocks[$i][] = ($block->x & 0xff) << 32 | ($block->y & 0xff) << 24 | ($block->z & 0xff) << 16 | ($block->getID() & 0xff) << 8 | ($block->meta & 0xff);
				++$this->changedCount[$i];
			}
			
			if($tiles === true){
				$this->server->api->tile->invalidateAll($this, $pos->x, $pos->y, $pos->z);
			}
			
			if($update === true){
				$this->updateNeighborsAt($pos->x, $pos->y, $pos->z, $oldID);
			}
			
		}
		return $ret;
	}

	public function getMiniChunk($X, $Z, $Y){
		if(!isset($this->level)){
			return false;
		}
		return $this->level->getMiniChunk($X, $Z, $Y);
	}

	public function setMiniChunk($X, $Z, $Y, $data){
		if(!isset($this->level)){
			return false;
		}
		$this->changedCount[$X . ":" . $Y . ":" . $Z] = 4096;
		return $this->level->setMiniChunk($X, $Z, $Y, $data);
	}

	public function loadChunk($X, $Z){
		if(!isset($this->level)){
			return false;
		}
		return $this->level->loadChunk($X, $Z);
	}

	public function unloadChunk($X, $Z, $force = false){
		if(!isset($this->level)){
			return false;
		}

		if($force !== true and $this->isSpawnChunk($X, $Z)){
			return false;
		}
		return $this->level->unloadChunk($X, $Z, $this->server->saveEnabled);
	}

	public function getOrderedChunk($X, $Z, $Yndex){
		if(!isset($this->level)){
			return false;
		}

		$raw = [];
		for($Y = 0; $Y < 8; ++$Y){
			if(($Yndex & (1 << $Y)) > 0){
				$raw[$Y] = $this->level->getMiniChunk($X, $Z, $Y);
			}
		}

		$ordered = "";
		$flag = chr($Yndex);
		for($j = 0; $j < 256; ++$j){
			$ordered .= $flag;
			foreach($raw as $mini){
				$ordered .= substr($mini, $j << 5, 24); //16 + 8
			}
		}
		return $ordered;
	}

	public function getOrderedMiniChunk($X, $Z, $Y){
		if(!isset($this->level)){
			return false;
		}
		$raw = $this->level->getMiniChunk($X, $Z, $Y);
		$ordered = "";
		$flag = chr(1 << $Y);
		for($j = 0; $j < 256; ++$j){
			$ordered .= $flag . substr($raw, $j << 5, 24); //16 + 8
		}
		return $ordered;
	}

	public function getSafeSpawn($spawn = false){
		if($spawn === false){
			$spawn = $this->getSpawn();
		}
		if($spawn instanceof Vector3){
			$x = (int) round($spawn->x);
			$fy = $y = (int) round($spawn->y);
			$z = (int) round($spawn->z);
			if($x < 0 || $x > 255 || $z < 0 || $z > 255){
				return new Position($x, 128, $z, $this);
			}
			for(; $y > 0; --$y){
				$v = new Vector3($x, $y, $z);
				$b = $this->getBlock($v->getSide(0));
				if($b === false){
					return $spawn;
				}elseif(!($b instanceof AirBlock)){
					break;
				}
			}
			if($y == 0){
				return new Position($x, $fy, $z, $this); //force tp to default if pos is empty
			}
			for(; $y < 128; ++$y){
				$v = new Vector3($x, $y, $z);
				if($this->getBlock($v->getSide(1)) instanceof AirBlock){
					if($this->getBlock($v) instanceof AirBlock){
						return new Position($x, $y, $z, $this);
					}
				}else{
					++$y;
				}
			}
			return new Position($x, $y, $z, $this);
		}
		return false;
	}

	public function getSpawn(){
		if(!isset($this->level)){
			return false;
		}
		return new Position($this->level->getData("spawnX"), $this->level->getData("spawnY"), $this->level->getData("spawnZ"), $this);
	}
	
	/**
	 * @param number $x
	 * @param number $y
	 * @param number $z
	 * @param boolean $positionfy assign coordinates to block or not
	 * @return GenericBlock | false if failed
	 */
	
	public function getBlockWithoutVector($x, $y, $z, $positionfy = true){
		$b = $this->level->getBlock((int)$x, (int)$y, (int)$z);
		return BlockAPI::get($b[0], $b[1], $positionfy ? new Position($x, $y, $z, $this) : false);
	}
	
	/**
	 * Recommended to use {@link getBlockWithoutVector()} if you dont have the vector
	 * @param Vector3 $pos
	 * @return Block|false if failed
	 */
	public function getBlock(Vector3 $pos){
		if(!isset($this->level) or ($pos instanceof Position) and $pos->level !== $this){
			return false;
		}
		return $this->getBlockWithoutVector($pos->x, $pos->y, $pos->z);
	}

	public function setSpawn(Vector3 $pos){
		if(!isset($this->level)){
			return false;
		}
		$this->level->setData("spawnX", $pos->x);
		$this->level->setData("spawnY", $pos->y);
		$this->level->setData("spawnZ", $pos->z);
	}

	public function getTime(){
		return (int) ($this->time);
	}

	public function setTime($time){
		$this->startTime = $this->time = (int) $time;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	public function checkTime(){
		if(!isset($this->level)){
			return false;
		}
		$now = microtime(true);
		if($this->stopTime){
			$time = $this->startTime;
		}else{
			$time = $this->startTime + ($now - $this->startCheck) * 20;
		}
		if($this->server->api->dhandle("time.change", ["level" => $this, "time" => $time]) !== false){ //send time to player every 5 ticks
			$this->time = $time;
			$pk = new SetTimePacket;
			$pk->time = (int) $this->time;
			$pk->started = $this->stopTime == false;
			$this->server->api->player->broadcastPacket($this->players, $pk);
		}else{
			$this->time -= 20 * 13;
		}
	}
	
	public function isTimeStopped(){
		return $this->stopTime;
	}
	
	public function stopTime(){
		$this->stopTime = true;
		$this->startCheck = 0;
		$this->checkTime();
	}

	public function startTime(){
		$this->stopTime = false;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	public function getSeed(){
		if(!isset($this->level)){
			return false;
		}
		return (int) $this->level->getData("seed");
	}

	public function setSeed($seed){
		if(!isset($this->level)){
			return false;
		}
		$this->level->setData("seed", (int) $seed);
	}

	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
		if(!isset($this->level)){
			return false;
		}
		return $this->server->api->block->scheduleBlockUpdate($pos, $delay, $type);
	}
}

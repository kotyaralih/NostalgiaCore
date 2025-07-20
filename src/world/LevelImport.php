<?php

class LevelImport{

	private $path;

	public function __construct($path){
		$this->path = $path;
	}

	public function import(){
		if(file_exists($this->path . "tileEntities.dat")){ //OldPM
			$level = unserialize(file_get_contents($this->path . "level.dat"));
			console("[INFO] Importing OldPM level \"" . $level["LevelName"] . "\" to PMF format");
			$entities = new Config($this->path . "entities.yml", CONFIG_YAML, unserialize(file_get_contents($this->path . "entities.dat")));
			$entities->save();
			$tiles = new Config($this->path . "tiles.yml", CONFIG_YAML, unserialize(file_get_contents($this->path . "tileEntities.dat")));
			$tiles->save();
		}elseif(file_exists($this->path . "chunks.dat") and file_exists($this->path . "level.dat")){ //Pocket
			$nbt = new NBT();
			$nbt->load(substr(file_get_contents($this->path . "level.dat"), 8));
			$level = array_shift($nbt->tree);
			if($level["LevelName"] == ""){
				$level["LevelName"] = "world" . time();
			}
			console("[INFO] Importing Pocket level \"" . $level["LevelName"] . "\" to PMF format");
			unset($level["Player"]);
			$nbt->load(substr(file_get_contents($this->path . "entities.dat"), 12));
			$entities = array_shift($nbt->tree);
			if(!isset($entities["TileEntities"])){
				$entities["TileEntities"] = [];
			}
			$tiles = $entities["TileEntities"];
			$entities = $entities["Entities"];
			$entities = new Config($this->path . "entities.yml", CONFIG_YAML, $entities);
			$entities->save();
			$tiles = new Config($this->path . "tiles.yml", CONFIG_YAML, $tiles);
			$tiles->save();
		}else{
			return false;
		}

		$pmf = new PMFLevel($this->path . "level.pmf", [
			"name" => $level["LevelName"],
			"seed" => $level["RandomSeed"],
			"time" => $level["Time"],
			"spawnX" => $level["SpawnX"],
			"spawnY" => $level["SpawnY"],
			"spawnZ" => $level["SpawnZ"],
			"extra" => "",
			"width" => 16,
			"height" => 8
		]);
		$chunks = new PocketChunkParser();
		$chunks->loadFile($this->path . "chunks.dat");
		$chunks->loadMap();
		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$chunk = [
					0 => "",
					1 => "",
					2 => "",
					3 => "",
					4 => "",
					5 => "",
					6 => "",
					7 => ""
				];
				$pmf->markHasBlocklight($X, $Z);
				
				for($z = 0; $z < 16; ++$z){
					for($x = 0; $x < 16; ++$x){
						$block = $chunks->getChunkColumn($X, $Z, $x, $z, 0);
						$meta = $chunks->getChunkColumn($X, $Z, $x, $z, 1);
						$blocklight = $chunks->getChunkColumn($X, $Z, $x, $z, 3);
						for($Y = 0; $Y < 8; ++$Y){
							$chunk[$Y] .= substr($block, $Y << 4, 16);
							$chunk[$Y] .= substr($meta, $Y << 3, 8);
							$chunk[$Y] .= substr($blocklight, $Y << 3, 8);
						}
					}
				}
				foreach($chunk as $Y => $data){
					$pmf->setMiniChunk($X, $Z, $Y, $data);
				}
				$pmf->saveChunk($X, $Z);
			}
			console("[NOTICE] Importing level " . ceil(($Z + 1) / 0.16) . "%");
		}
		$pmf->writeNCData();
		
		$chunks = null;
		if(file_exists($this->path . "level.dat")) @unlink($this->path . "level.dat");
		if(file_exists($this->path . "level.dat_old")) @unlink($this->path . "level.dat_old");
		if(file_exists($this->path . "player.dat")) @unlink($this->path . "player.dat");
		if(file_exists($this->path . "entities.dat")) @unlink($this->path . "entities.dat");
		if(file_exists($this->path . "chunks.dat")) @unlink($this->path . "chunks.dat");
		if(file_exists($this->path . "chunks.dat.gz")) @unlink($this->path . "chunks.dat.gz");
		if(file_exists($this->path . "tiles.dat")) @unlink($this->path . "tiles.dat");
		unset($chunks, $level, $entities, $tiles, $nbt);
		return true;
	}

}
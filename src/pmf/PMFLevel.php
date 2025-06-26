<?php

define("PMF_CURRENT_LEVEL_VERSION", 0x00);

class PMFLevel extends PMF{
	const NC_DATA_VERSION = 1;
	public $isLoaded = true;
	private $levelData = [];
	public $hasLight = "";
	public $locationTable = [];
	private $log = 4; //must be 4 or else rip world
	private $payloadOffset = 0;
	public $chunks = [];
	public $chunkChange = [];
	/**
	 * @var Level
	 */
	public $level;
	public function __construct($file, $blank = false){
		if(is_array($blank)){
			$this->create($file, 0);
			$this->levelData = $blank;
			$this->createBlank();
			$this->isLoaded = true;
		}else{
			if($this->load($file) !== false){
				$this->parseInfo();
				if($this->parseLevel() === false){
					$this->isLoaded = false;
				}else{
					$this->isLoaded = true;
				}
			}else{
				$this->isLoaded = false;
			}
		}
	}

	private function createBlank(){
		$this->saveData(false);
		$this->locationTable = [];
		$cnt = $this->levelData["width"] * $this->levelData["width"];
		$dirname = dirname($this->file) . "/chunks/";
		if(!is_dir($dirname)){
			@mkdir($dirname , 0755);
		}
		
		$this->hasLight = str_repeat("\x00", 16*16);
		for($index = 0; $index < $cnt; ++$index){
			$this->chunks[$index] = false;
			$this->chunkChange[$index] = false;
			$this->locationTable[$index] = [
				0 => 0,
			];
			$this->write(Utils::writeShort(0));
			$X = $Z = null;
			$this->getXZ($index, $X, $Z);
			@file_put_contents($this->getChunkPath($X, $Z), gzdeflate("", PMF_LEVEL_DEFLATE_LEVEL));
		}
		if(!file_exists(dirname($this->file) . "/entities.yml")){
			$entities = new Config(dirname($this->file) . "/entities.yml", CONFIG_YAML);
			$entities->save();
		}
		if(!file_exists(dirname($this->file) . "/tiles.yml")){
			$tiles = new Config(dirname($this->file) . "/tiles.yml", CONFIG_YAML);
			$tiles->save();
		}
	}

	public function saveData($locationTable = true){
		$this->levelData["version"] = PMF_CURRENT_LEVEL_VERSION;
		@ftruncate($this->fp, 5);
		$this->seek(5);
		$this->write(chr($this->levelData["version"]));
		$this->write(Utils::writeShort(strlen($this->levelData["name"])) . $this->levelData["name"]);
		$this->write(Utils::writeInt($this->levelData["seed"]));
		$this->write(Utils::writeInt($this->levelData["time"]));
		$this->write(Utils::writeFloat($this->levelData["spawnX"]));
		$this->write(Utils::writeFloat($this->levelData["spawnY"]));
		$this->write(Utils::writeFloat($this->levelData["spawnZ"]));
		$this->write(chr($this->levelData["width"]));
		$this->write(chr($this->levelData["height"]));
		$extra = gzdeflate($this->levelData["extra"], PMF_LEVEL_DEFLATE_LEVEL);
		$this->write(Utils::writeShort(strlen($extra)) . $extra);
		$this->payloadOffset = ftell($this->fp);

		if($locationTable !== false){
			$this->writeLocationTable();
		}
		
		$this->writeNCData();
	}
	
	public function writeNCData(){
		$dir = dirname($this->file);
		try{
			$file = fopen("$dir/ncleveldata.pmf", "wb");
			fwrite($file, chr(self::NC_DATA_VERSION), 1);
			fwrite($file, $this->hasLight, 16*16);
		}finally{
			fclose($file);		
		}
	}

	public function readNCData(){
		$this->hasLight = str_repeat("\x00", 16*16);
		
		$dir = dirname($this->file);
		$path = "$dir/ncleveldata.pmf";
		if(!is_file($path)){
			ConsoleAPI::notice("No NC Level data found! Old world?");
			return;
		}
		try{
			$file = fopen($path, "rb");
			$ncdv = ord(fread($file, 1));
			$v = self::NC_DATA_VERSION;
			if($ncdv !== $v){
				ConsoleAPI::notice("NC level data version on server($v) does not match the world($ncdv)! Creating NC level data backup.");
				$newpath = "$path.bak.".microtime(true);
				$f = copy($path, $newpath);
				if($f === false) ConsoleAPI::error("NC level data backup creation failed.");
				else ConsoleAPI::notice("NC level data backup created. (path: $newpath)");
			}
			$this->hasLight = fread($file, 16*16);
		}finally{
			fclose($file);
		}
	}
	
	/**
	 * Checks was skylight generated for chunk
	 * @param int $X - chunk x
	 * @param int $Z - chunk z
	 */
	public function hasSkylight($X, $Z){
		if($X < 0 || $X > 15 || $Z < 0 || $Z > 15) return false;
		$index = self::getIndex($X, $Z);
		return (ord($this->hasLight[$index]) & 2) > 0;
	}
	/**
	 * Checks was blocklight generated for chunk
	 * @param int $X - chunk x
	 * @param int $Z - chunk z
	 */
	public function hasBlocklight($X, $Z){
		if($X < 0 || $X > 15 || $Z < 0 || $Z > 15) return false;
		$index = self::getIndex($X, $Z);
		return (ord($this->hasLight[$index]) & 1) > 0;
	}
	
	public function markHasBlocklight($X, $Z){
		if($X < 0 || $X > 15 || $Z < 0 || $Z > 15) return false;
		$index = self::getIndex($X, $Z);
		$o = ord($this->hasLight[$index]);
		$this->hasLight[$index] = chr($o | 1);
		return true;
	}
	public function markHasSkylight($X, $Z){
		if($X < 0 || $X > 15 || $Z < 0 || $Z > 15) return false;
		$index = self::getIndex($X, $Z);
		$o = ord($this->hasLight[$index]);
		$this->hasLight[$index] = chr($o | 2);
		return true;
	}
	
	private function writeLocationTable(){
		$cnt = pow($this->levelData["width"], 2);
		@ftruncate($this->fp, $this->payloadOffset);
		$this->seek($this->payloadOffset);
		for($index = 0; $index < $cnt; ++$index){
			$this->write(Utils::writeShort($this->locationTable[$index][0]));
		}

		$this->backupLocTable();
	}
	
	public function backupLocTable(){
		$dir = dirname($this->file);
		if(is_file("$dir/loctable.pmf")){
			$val = copy("$dir/loctable.pmf", "$dir/loctable.pmf.old");
			if($val === false) ConsoleAPI::warn("Failed to backup loctable data!");
		}
		
		$file = fopen("$dir/loctable.pmf", "wb");
		try{
			$cnt = pow($this->levelData["width"], 2);
			for($index = 0; $index < $cnt; ++$index){
				fwrite($file, Utils::writeShort($this->locationTable[$index][0]), 2);
			}
		}finally{
			fclose($file);
		}
		
	}

	public function getXZ($index, &$X = null, &$Z = null){
		$X = $index >> 4;
		$Z = $index & 0xf;
		return [$X, $Z];
	}

	private function getChunkPath($X, $Z){
		return dirname($this->file) . "/chunks/" . $Z . "." . $X . ".pmc";
	}

	protected function parseLevel(){
		if($this->getType() !== 0x00){
			return false;
		}
		$this->seek(5);
		$this->levelData["version"] = ord($this->read(1));
		if($this->levelData["version"] > PMF_CURRENT_LEVEL_VERSION){
			return false;
		}
		$this->levelData["name"] = $this->read(Utils::readShort($this->read(2), false));
		$this->levelData["seed"] = Utils::readInt($this->read(4));
		$this->levelData["time"] = Utils::readInt($this->read(4));
		$this->levelData["spawnX"] = Utils::readFloat($this->read(4));
		$this->levelData["spawnY"] = Utils::readFloat($this->read(4));
		$this->levelData["spawnZ"] = Utils::readFloat($this->read(4));
		$this->levelData["width"] = ord($this->read(1));
		$this->levelData["height"] = ord($this->read(1));
		if(($this->levelData["width"] !== 16 and $this->levelData["width"] !== 32) or $this->levelData["height"] !== 8){
			return false;
		}
		$lastseek = ftell($this->fp);
		if(($len = $this->read(2)) === false or ($this->levelData["extra"] = @gzinflate($this->read(Utils::readShort($len, false)))) === false){ //Corruption protection
			console("[NOTICE] Empty/corrupt location table detected, forcing recovery");
			fseek($this->fp, $lastseek);
			$c = gzdeflate("");
			$this->write(Utils::writeShort(strlen($c)) . $c);
			$this->payloadOffset = ftell($this->fp);
			$this->levelData["extra"] = "";
			$cnt = pow($this->levelData["width"], 2);
			for($index = 0; $index < $cnt; ++$index){
				$this->write("\x00\xFF"); //Force index recreation
			}
			fseek($this->fp, $this->payloadOffset);
		}else{
			$this->payloadOffset = ftell($this->fp);
		}
		$this->readNCData();
		return $this->readLocationTable();
	}

	private function readLocationTable(){
		$this->locationTable = [];
		$cnt = pow($this->levelData["width"], 2);
		$this->seek($this->payloadOffset);
		for($index = 0; $index < $cnt; ++$index){
			$this->chunks[$index] = false;
			$this->chunkChange[$index] = false;
			$this->locationTable[$index] = [
				0 => Utils::readShort($this->read(2)), //16 bit flags
			];
		}
		return true;
	}

	public function getData($index){
		if(!isset($this->levelData[$index])){
			return false;
		}
		return ($this->levelData[$index]);
	}

	public function setData($index, $data){
		if(!isset($this->levelData[$index])){
			return false;
		}
		$this->levelData[$index] = $data;
		return true;
	}

	public function close(){
		$chunks = null;
		unset($chunks, $chunkChange, $locationTable);
		parent::close();
	}

	public function unloadChunk($X, $Z, $save = true){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}elseif($save !== false){
			$this->saveChunk($X, $Z);
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index] = null;
		$this->chunkChange[$index] = null;
		unset($this->chunks[$index], $this->chunkChange[$index]);
		return true;
	}
	
	public function isChunkLoaded($X, $Z){
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) or $this->chunks[$index] === false){
			return false;
		}
		return true;
	}

	public static function getIndex($X, $Z){
		return ((int) $Z << 4) + (int) $X; //statically 4, setting it to something else would destroy everything
	}

	public function saveChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunkChange[$index]) or $this->chunkChange[$index][-1] === false){//No changes in chunk
			return true;
		}

		$chunk = @gzopen($this->getChunkPath($X, $Z), "wb" . PMF_LEVEL_DEFLATE_LEVEL);
		$bitmap = 0;
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			if($this->chunks[$index][$Y] !== false and ((isset($this->chunkChange[$index][$Y]) and $this->chunkChange[$index][$Y] === 0) or !$this->isMiniChunkEmpty($X, $Z, $Y))){
				gzwrite($chunk, $this->chunks[$index][$Y]);
				$bitmap |= 1 << $Y;
			}else{
				$this->chunks[$index][$Y] = false;
			}
			$this->chunkChange[$index][$Y] = 0;
		}
		$this->chunkChange[$index][-1] = false;
		$this->locationTable[$index][0] = $bitmap;
		$this->seek($this->payloadOffset + ($index << 1));
		$this->write(Utils::writeShort($this->locationTable[$index][0]));
		return true;
	}

	protected function isMiniChunkEmpty($X, $Z, $Y){
		$index = self::getIndex($X, $Z);
		if($this->chunks[$index][$Y] !== false){
			if(substr_count($this->chunks[$index][$Y], "\x00") < 8192){
				return false;
			}
		}
		return true;
	}

	public function getMiniChunk($X, $Z, $Y){
		if($this->loadChunk($X, $Z) === false){
			return str_repeat("\x00", 8192);
		}
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index][$Y]) or $this->chunks[$index][$Y] === false){
			return str_repeat("\x00", 8192);
		}
		return $this->chunks[$index][$Y];
	}

	public $checkLight = [];
	public function loadChunk($X, $Z){
		$index = self::getIndex($X, $Z);

		if($this->isChunkLoaded($X, $Z)){
			return true;
		}elseif(!isset($this->locationTable[$index])){
			return false;
		}

		$info = $this->locationTable[$index];
		$this->seek($info[0]);

		$chunk = @gzopen($this->getChunkPath($X, $Z), "rb");
		if($chunk === false){
			return false;
		}
		$this->chunks[$index] = [];
		$this->chunkChange[$index] = [-1 => false];
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			$t = 1 << $Y;
			if(($info[0] & $t) === $t){
				//TODO do something with skylight
				// 4096 + 2048 + 2048, Block Data, Meta, Light
				if(strlen($this->chunks[$index][$Y] = gzread($chunk, 8192)) < 8192){
					console("[NOTICE] Empty corrupt chunk detected [$X,$Z,:$Y], recovering contents", true, true, 2);
					$this->fillMiniChunk($X, $Z, $Y);
				}
			}else{
				$this->chunks[$index][$Y] = false;
			}
		}
		$this->checkLight[$index] = [$X, $Z];
		
		@gzclose($chunk);
		return true;
	}
	
	public function forceLightUpdatesIfNeeded(){
		if(!PocketMinecraftServer::$ENABLE_LIGHT_UPDATES) {
			$this->checkLight = [];
			return;
		}
		foreach($this->checkLight as $index => [$X, $Z]){
			$update = false;
			if(!$this->hasBlocklight($X, $Z)){
				ConsoleAPI::notice("Chunk $X $Z (Level: {$this->level->getName()} has no blocklight! Forcing light update(it might take a while).");
				$this->level->updateLight(0, $X*16, 0, $Z*16, $X*16+15, 127, $Z*16+15);
				$update = true;
				$this->markHasBlocklight($X, $Z);
			}
			
			if($update) while($this->level->updateLights());
			unset($this->checkLight[$index]);
		}
		
	}

	protected function fillMiniChunk($X, $Z, $Y){
		if($this->isChunkLoaded($X, $Z) === false){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index][$Y] = str_repeat("\x00", 8192);
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->locationTable[$index][0] |= 1 << $Y;
		return true;
	}

	public function setMiniChunk($X, $Z, $Y, $data){
		if($this->isChunkLoaded($X, $Z) === false){
			$this->loadChunk($X, $Z);
		}
		if(strlen($data) !== 8192){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index][$Y] = (string) $data;
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->locationTable[$index][0] |= 1 << $Y;
		return true;
	}
	/**
	 * This method is faster, but may cause a lot of problems with unchecked values
	 * @param integer $chunkX chunk(0-16)
	 * @param integer $chunkY chunk(0-8)
	 * @param integer $chunkZ chunk(0-16)
	 * @param integer $blockX block(0-16)
	 * @param integer $blockY block(0-16)
	 * @param integer $blockZ block(0-16)
	 * @param integer $index chunk index
	 * @return number
	 */
	public function fastGetBlockID($chunkX, $chunkY, $chunkZ, $blockX, $blockY, $blockZ, $index){
		return ($this->chunks[$index][$chunkY] === false) ? 0 : ord($this->chunks[$index][$chunkY][$blockY + ($blockX << 5) + ($blockZ << 9)]);
	}
	
	public function getBlockID($x, $y, $z){
		if($y > 127 || $y < 0){
			return 0;
		}
		
		if($x < 0 || $x > 255 || $z < 0 || $z > 255){
			return INVISIBLE_BEDROCK;
		}
		
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false || ($this->chunks[$index][$Y] === false)){
			return 0;
		}
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		
		$b = ord($this->chunks[$index][$Y][($aY + ($aX << 5) + ($aZ << 9))]);
		
		return $b;
	}

	public function setBlockID($x, $y, $z, $block){
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127){
			return false;
		}
		
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		
		$index = self::getIndex($X, $Z);
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		$bind = (int) ($aY + ($aX << 5) + ($aZ << 9));
		if($this->chunks[$index][$Y][$bind] == chr($block)){
			return false; //no changes done
		}else{
			$this->chunks[$index][$Y][$bind] = chr($block);
			if($block > 0) StaticBlock::getBlock($block)::onPlace($this->level, $x, $y, $z);
			$this->level->updateLight(0, $x, $y, $z, $x, $y, $z);
		}
		
		if(!isset($this->chunkChange[$index][$Y])){
			$this->chunkChange[$index][$Y] = 1;
		}else{
			++$this->chunkChange[$index][$Y];
		}
		$this->chunkChange[$index][-1] = true;
		return true;
	}

	public function getBlockLight($x, $y, $z){
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127 || !PocketMinecraftServer::$ENABLE_LIGHT_UPDATES) return 0;
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false || ($this->chunks[$index][$Y] === false)) return 0;
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		$m = ord($this->chunks[$index][$Y][(int) (($aY >> 1) + 24 + ($aX << 5) + ($aZ << 9))]);
		return $y & 1 ? $m >> 4 : $m & 0x0F;
	}
	
	public function setBlockLight($x, $y, $z, $value){
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127 || !PocketMinecraftServer::$ENABLE_LIGHT_UPDATES) return false;
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$value &= 0x0F;
		
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false) return false;
		}elseif($this->chunks[$index][$Y] === false){
			$this->fillMiniChunk($X, $Z, $Y);
		}
		
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		$mindex = (int) (($aY >> 1) + 24 + ($aX << 5) + ($aZ << 9));
		$old_m = ord($this->chunks[$index][$Y][$mindex]);
		if(($y & 1) === 0) $m = ($old_m & 0xF0) | $value;
		else $m = ($value << 4) | ($old_m & 0x0F);
		
		if($old_m != $m){
			$this->chunks[$index][$Y][$mindex] = chr($m);
			
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
			}
			$this->chunkChange[$index][-1] = true;
			return true;
		}
		return false;
	}
	
	public function getBlockDamage($x, $y, $z){
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127){
			return 0;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false || ($this->chunks[$index][$Y] === false)){
			return 0;
		}
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		$m = ord($this->chunks[$index][$Y][(int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9))]);
		return $y & 1 ? $m >> 4 : $m & 0x0F;
	}

	public function setBlockDamage($x, $y, $z, $damage){
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$damage &= 0x0F;
		
		$index = self::getIndex($X, $Z);
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		$mindex = (int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9));
		$old_m = ord($this->chunks[$index][$Y][$mindex]);
		if(($y & 1) === 0){
			$m = ($old_m & 0xF0) | $damage;
		}else{
			$m = ($damage << 4) | ($old_m & 0x0F);
		}

		if($old_m != $m){
			$this->chunks[$index][$Y][$mindex] = chr($m);
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
			}
			$this->chunkChange[$index][-1] = true;
			return true;
		}
		return false;
	}

	public function getBlock($x, $y, $z){
		if($y < 0 || $y > 127){
			return [AIR, 0];
		}
		if($x < 0 || $x > 255 || $z < 0 || $z > 255){
			return [INVISIBLE_BEDROCK, 0];
		}
		
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return [AIR, 0];
			}
		}
		if($this->chunks[$index][$Y] === false){
			return [AIR, 0];
		}
		$aX = $x & 0xf;
		$aZ = $z & 0xf;
		$aY = $y & 0xf;
		
		$b = ord($this->chunks[$index][$Y][($aY + ($aX << 5) + ($aZ << 9))]);
		
		$m = ord($this->chunks[$index][$Y][(($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9))]);
		$m = ($y & 1) ? $m >> 4 : $m & 0xf;
		
		return [$b, $m];
	}

	public function setBlock($x, $y, $z, $block, $meta = 0){
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		$meta &= 0x0F;
		if($x < 0 || $x > 255 || $z < 0 || $z > 255 || $y < 0 || $y > 127){
			return false;
		}
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) || $this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return false;
			}
		}elseif($this->chunks[$index][$Y] === false){
			$this->fillMiniChunk($X, $Z, $Y);
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$bindex = (int) ($aY + ($aX << 5) + ($aZ << 9));
		$mindex = (int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9));
		$old_b = ord($this->chunks[$index][$Y][$bindex]);
		$old_m = ord($this->chunks[$index][$Y][$mindex]);
		
		$m = ($y & 1) ? (($meta << 4) | ($old_m & 0x0F)) : (($old_m & 0xF0) | $meta);

		if($old_b !== $block or $old_m !== $m){
			$this->chunks[$index][$Y][$bindex] = chr($block);
			$this->chunks[$index][$Y][$mindex] = chr($m);
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
			}
			$this->chunkChange[$index][-1] = true;
			
			if($block > 0) StaticBlock::getBlock($block)::onPlace($this->level, $x, $y, $z);
			$this->level->updateLight(0, $x, $y, $z, $x, $y, $z);
			return true;
		}
		return false;
	}

	public function doSaveRound(){
		foreach($this->chunks as $index => $chunk){
			$this->getXZ($index, $X, $Z);
			$this->saveChunk($X, $Z);
		}
	}

}

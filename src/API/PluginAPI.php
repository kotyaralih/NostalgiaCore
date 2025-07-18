<?php

class PluginAPI extends stdClass{

	private $server;
	public $plugins = []; //specially for DevTools
	private $randomNonce;

	public function __construct(){
		$this->server = ServerAPI::request();
		$this->randomNonce = Utils::getRandomBytes(16, false);
		$this->server->api->console->register("plugins", "", [$this, "commandHandler"]);
		$this->server->api->console->register("version", "", [$this, "commandHandler"]);
		$this->server->api->console->cmdWhitelist("version");
		if($this->server->extraprops->get("query-plugins")){ //allow players to also view plugins ingame, since query makes names public
			$this->server->api->console->cmdWhitelist("plugins");
		}
		$this->server->api->console->alias("pl", "plugins");
		$this->server->api->console->alias("ver", "version");
		$this->server->api->console->alias("about", "version");
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "plugins":
				$output = "Plugins (" . count($this->plugins) . "): ";
				foreach($this->getList() as $plugin){
					$output .= $plugin["name"] . " v" . $plugin["version"] . ", ";
				}
				$output = $output === "Plugins (0): " ? "No plugins installed.\n" : substr($output, 0, -2) . "\n";
				break;
			case "version":
				$output = "This server is running NostalgiaCore version " . MAJOR_VERSION . "\nCODENAME: 「 " . CODENAME . " 」\n(Implementing API version #" . CURRENT_API_VERSION . " for Minecraft: PE " . CURRENT_MINECRAFT_VERSION . ")";
				if(GIT_COMMIT !== str_repeat("00", 20)){
					$output .= " (git " . GIT_COMMIT . ")";
				}
				$output .= "\n";
				break;
		}
		return $output;
	}

	public function getList(){
		$list = [];
		foreach($this->plugins as $p){
			$list[] = $p[1];
		}
		return $list;
	}

	public function __destruct(){
		if(!isset($this->plugins) || !is_array($this->plugins)){
			ConsoleAPI::warn("PluginAPI::\$plugins is null (destructor called twice?)");
			return;
		}
		foreach($this->plugins as $p){
			if(method_exists($p[0], "__destruct")) $p[0]->__destruct();
		}
		unset($this->plugins);
	}

	public function getAll(){
		return $this->plugins;
	}

	public function createConfig(Plugin $plugin, $default = []){
		$p = $this->get($plugin);
		if($p === false){
			return false;
		}
		$path = $this->configPath($plugin);
		$cnf = new Config($path . "config.yml", CONFIG_YAML, $default);
		$cnf->save();
		return $path;
	}

	public function get($identifier){
		if($identifier instanceof Plugin){
			foreach($this->plugins as $p){
				if($p[0] === $identifier){
					return $p;
				}
			}
			return false;
		}
		if(isset($this->plugins[$identifier])){
			return $this->plugins[$identifier];
		}
		return false;
	}

	public function configPath(Plugin $plugin){
		$p = $this->get($plugin);
		$identifier = $this->getIdentifier($p[1]["name"], $p[1]["author"]);
		if($p === false){
			return false;
		}
		$path = $this->pluginsPath() . $p[1]["name"] . DIRECTORY_SEPARATOR;
		$this->plugins[$identifier][1]["path"] = $path;
		if(!file_exists($path)){
			mkdir($path, 0777);
		}
		return $path;
	}

	public function pluginsPath(){
		$path = join(DIRECTORY_SEPARATOR, [DATA_PATH . "plugins", ""]);
		if(!file_exists($path)){
			mkdir($path, 0777);
		}

		return $path;
	}

	public function readYAML($file){
		return yaml_parse(preg_replace("#^([ ]*)([a-zA-Z_]{1}[^\:]*)\:#m", "$1\"$2\":", file_get_contents($file)));
	}

	public function writeYAML($file, $data){
		return file_put_contents($file, yaml_emit($data, YAML_UTF8_ENCODING));
	}

	public function init(){
		$this->server->event("server.start", [$this, "initAll"]);
		$this->loadAll();
	}

	private function loadAll(){
		$pharCnt = 0;
		$dir = dir($this->pluginsPath());
		while(false !== ($file = $dir->read())){
			if($file[0] !== "."){
				$ext = strtolower(substr($file, -3));
				$ext2 = strtolower(substr($file, -4));
				if($ext === "php" or $ext === "pmf"){
					$this->load($dir->path . $file);
				}
				if($ext2 === "phar"){
					++$pharCnt;
					$pluginInfo = []; //TODO: A PluginInfo class?
					$filePath = $this->pluginsPath().$file;
					$p = new Phar($this->pluginsPath().$file, 0);
					foreach (new RecursiveIteratorIterator($p) as $file) {
						$name = $file->getFileName();
						$content = file_get_contents($file->getPathName());
						if($name === "plugin.cfg"){
							$pluginInfo = PharUtils::readMainConfig($content);
							break;
						}
					}
					if(count($pluginInfo) == 0){
						ConsoleAPI::error("Failed to load PHAR plugin from $file: pluginInfo has no information(PHAR was made for PMMP 1.4+?)");
						continue;
					}
					console("[INFO] Loading PHAR plugin \"".FORMAT_GREEN.$pluginInfo["name"].FORMAT_RESET."\" ".FORMAT_AQUA.$pluginInfo["version"].FORMAT_RESET." by ".FORMAT_AQUA.$pluginInfo["author"].FORMAT_RESET);
					
					$aver = CURRENT_API_VERSION;
					if(!in_array((string) CURRENT_API_VERSION, $pluginInfo["api"])){ 
						if(is_array($pluginInfo)) $s = implode(",",$pluginInfo["api"]);
						else $s = $pluginInfo["api"];
						console("[WARNING] API is not the same as Core, might cause bugs({$s} != {$aver})");
					}
					
					$phr = "phar://$filePath/";
					include($phr."/src/".$pluginInfo["classLoader"]);
					$class = $pluginInfo["CLClass"];
					$loader = new $class();
					$loader->loadAll($phr);
					
					$pluginName = PharUtils::getNameSpaceClass($pluginInfo["mainFile"]);
					include($phr."/src/".$pluginInfo["mainFile"]);
					$plugin = new $pluginName($this->server->api, false);
					if(!($plugin instanceof Plugin)){
						console("[ERROR] Plugin \"" . $pluginInfo["name"] . "\" doesn't use the Plugin Interface");
						$plugin->__destruct();
						unset($plugin);
						continue;
					}
					$identifier = $this->getIdentifier($pluginInfo["name"], $pluginInfo["author"]);
					$this->plugins[$identifier] = [$plugin, $pluginInfo];
				}
			}
		}
	}

	public function load($file){
		if(is_link($file) or is_dir($file) or !file_exists($file)){
			console("[ERROR] " . basename($file) . " is not a file");
			return false;
		}
		if(strtolower(substr($file, -3)) === "pmf"){
			$pmf = new PMFPlugin($file);
			$info = $pmf->getPluginInfo();
		}else{
			$content = file_get_contents($file);
			$info = strstr($content, "*/", true);
			$content = str_repeat(PHP_EOL, substr_count($info, "\n")) . substr(strstr($content, "*/"), 2);
			if(preg_match_all('#([a-zA-Z0-9\-_]*)=([^\r\n]*)#u', $info, $matches) == 0){ //false or 0 matches
				console("[ERROR] Failed parsing of " . basename($file));
				return false;
			}
			$info = [];
			foreach($matches[1] as $k => $i){
				$v = $matches[2][$k];
				switch(strtolower($v)){
					case "on":
					case "true":
					case "yes":
						$v = true;
						break;
					case "off":
					case "false":
					case "no":
						$v = false;
						break;
				}
				$info[$i] = $v;
			}
			$info["code"] = $content;
			$info["class"] = trim(strtolower($info["class"]));
		}
		if(!isset($info["name"]) or !isset($info["version"]) or !isset($info["class"]) or !isset($info["author"])){
			console("[ERROR] Failed parsing of " . basename($file));
			return false;
		}
		console("[INFO] Loading plugin \"" . FORMAT_GREEN . $info["name"] . FORMAT_RESET . "\" " . FORMAT_AQUA . $info["version"] . FORMAT_RESET . " by " . FORMAT_AQUA . $info["author"] . FORMAT_RESET);
		if($info["class"] !== "none" and class_exists($info["class"])){
			console("[ERROR] Failed loading plugin: class already exists");
			return false;
		}
		if(((!isset($pmf) and (include $file) === false) or (isset($pmf) and eval($info["code"]) === false)) and $info["class"] !== "none" and !class_exists($info["class"])){
			console("[ERROR] Failed loading {$info['name']}: evaluation error");
			return false;
		}

		$className = $info["class"];
		$apiversion = array_map("floatval", explode(",", (string) $info["apiversion"]));
		if(!in_array((string) CURRENT_API_VERSION, $apiversion)){
			console("[WARNING] Plugin \"" . $info["name"] . "\" may not be compatible with the API (" . $info["apiversion"] . " != " . CURRENT_API_VERSION . ")! It can crash or corrupt the server!");
		}

		$identifier = $this->getIdentifier($info["name"], $info["author"]);

		if($info["class"] !== "none"){
			$object = new $className($this->server->api, false);
			if(!($object instanceof Plugin)){
				console("[ERROR] Plugin \"" . $info["name"] . "\" doesn't use the Plugin Interface");
				if(method_exists($object, "__destruct")){
					$object->__destruct();
				}
				$object = null;
				unset($object);
			}else{
				$this->plugins[$identifier] = [$object, $info];
			}
		}else{
			$this->plugins[$identifier] = [new DummyPlugin($this->server->api, false), $info];
		}
		return true;
	}

	public function getIdentifier($name, $author){
		return sha1(trim(strtolower($name)), true) ^ sha1(trim(strtolower($author)), true) ^ sha1($this->randomNonce, true);
	}

	public function initAll(){
		console("[INFO] Starting plugins...");
		$names = [];
		$versions = [];
		foreach($this->plugins as $p){
			$names[] = $p[1]["name"];
			$versions[] = $p[1]["version"];
		}
		
		foreach($this->plugins as $p){
			if($p[0] instanceof OtherPluginRequirement){
				foreach($p[0]->getRequiredPlugins() as $required){
					if(in_array($required->pluginName, $names)){
						if(!in_array($required->version, $versions) && $required->version !== false){
							console("[WARNING] Plugin \"" . $required->pluginName . "\" needed by \"" . $p[1]["name"] . "\" is incorrect version.");
						}
					}else{
						console("[ERROR] Plugin \"" . $required->pluginName . "\" needed by \"" . $p[1]["name"] . "\" is not found.");
						ServerAPI::request()->close();

					}
				}
			}
			$p[0]->init(); //ARGHHH!!! Plugin loading randomly fails!!
		}
	}

	private function fillDefaults($default, &$yaml){
		foreach($default as $k => $v){
			if(is_array($v)){
				if(!isset($yaml[$k]) or !is_array($yaml[$k])){
					$yaml[$k] = [];
				}
				$this->fillDefaults($v, $yaml[$k]);
			}elseif(!isset($yaml[$k])){
				$yaml[$k] = $v;
			}
		}
	}
}

class RequiredPluginEntry{ //Use this as object of requirements array

	public $pluginName;
	public $version;

	public function __construct($name, $version = false){
		$this->pluginName = $name;
		$this->version = $version;
	}
}

 

<?php

class ConsoleAPI{
	private $loop; //why no Thread::kill in pthreads3. Making it static causes crashes of other threads on some servers(hi nper)
	private $server, $event, $help, $cmds, $alias;
	public $last;
	function __construct(){
		$this->help = [];
		$this->cmds = [];
		$this->alias = [];
		$this->server = ServerAPI::request();
		$this->last = microtime(true);
	}

	public function init(){
		$this->server->schedule(2, [$this, "handle"], [], true);
		if(!defined("NO_THREADS")){
			$this->loop = new ConsoleLoop();
		}
		$this->register("help", "[page|command name]", [$this, "defaultCommands"]);
		$this->register("status", "", [$this, "defaultCommands"]);
		$this->register("difficulty", "<0|1|2|3>", [$this, "defaultCommands"]);
		$this->register("stop", "", [$this, "defaultCommands"]);
		$this->register("defaultgamemode", "<mode>", [$this, "defaultCommands"]);

		$this->server->api->console->alias("tps", "status");
  
		$this->cmdWhitelist("help");
		$this->cmdWhitelist("status");
		$this->cmdWhitelist("?");
	}
	
	/**
	 * Whitelists a CMD so everyone can issue it - Even non OPs.
	 * @param string $cmd Command to Whitelist
	 */
	public function cmdWhitelist($cmd){
		$this->server->api->ban->cmdWhitelist[strtolower(trim($cmd))] = true;
	}
	
	public function register($cmd, $help, $callback){
		if(!is_callable($callback)){
			return false;
		}
		$cmd = strtolower(trim($cmd));
		$this->cmds[$cmd] = $callback;
		$this->help[$cmd] = $help;
		ksort($this->help, SORT_NATURAL | SORT_FLAG_CASE);
	}

	function __destruct(){
		$this->server->deleteEvent($this->event);
		if(!defined("NO_THREADS")){
			$this->loop->stop();
			$this->loop->notify();
			usleep(50000);
		}
	}

	public function alias($alias, $cmd){
		$this->alias[strtolower(trim($alias))] = trim($cmd);
		return true;
	}

	public function handle($time){
		if(defined("NO_THREADS")){
			return;
		}
		$line = $this->loop->line;
		if($line !== false){
			$line = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", trim($line));
			$this->loop->line = false;
			$output = $this->run($line, "console");
			if($output != ""){
				$mes = explode("\n", trim($output));
				foreach($mes as $m){
					console("[CMD] " . $m);
				}
			}
		}else{
			$this->loop->notify();
		}
	}

	public function run($line = "", $issuer = "console", $alias = false){
		if($line != ""){
			$output = "";
			$end = strpos($line, " ");
			if($end === false){
				$end = strlen($line);
			}
			$cmd = strtolower(substr($line, 0, $end));
			$params = substr($line, $end + 1);
			if(isset($this->alias[$cmd])){
				return $this->run($this->alias[$cmd] . ($params !== "" ? " " . $params : ""), $issuer, $cmd);
			}

			if(preg_match_all('#@([@a-z]{1,})#', $params, $matches, PREG_OFFSET_CAPTURE) > 0){
				$offsetshift = 0;
				foreach($matches[1] as $selector){
					if($selector[0][0] === "@"){ //Escape!
						$params = substr_replace($params, $selector[0], $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
						--$offsetshift;
						continue;
					}
					switch(strtolower($selector[0])){
						case "u":
						case "player":
						case "username":
							$p = ($issuer instanceof Player) ? $issuer->username : $issuer;
							$params = substr_replace($params, $p, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
							$offsetshift -= strlen($selector[0]) - strlen($p) + 1;
							break;
						case "w":
						case "world":
							$p = ($issuer instanceof Player) ? $issuer->level->getName() : $this->server->api->level->getDefault()->getName();
							$params = substr_replace($params, $p, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
							$offsetshift -= strlen($selector[0]) - strlen($p) + 1;
							break;
						case "a":
						case "all":
							if($issuer instanceof Player){
								if($this->server->api->ban->isOp($issuer->username)){
									$output = "";
									foreach($this->server->api->player->getAll() as $p){
										$output .= $this->run($cmd . " " . substr_replace($params, $p->username, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1), $issuer, $alias);
									}
								}else{
									$issuer->sendChat("You don't have permissions to use this command.\n");
								}
							}else{
								$output = "";
								foreach($this->server->api->player->getAll() as $p){
									$output .= $this->run($cmd . " " . substr_replace($params, $p->username, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1), $issuer, $alias);
								}
							}
							return $output;
						case "r":
						case "random":
							$l = [];
							foreach($this->server->api->player->getAll() as $p){
								if($p !== $issuer){
									$l[] = $p;
								}
							}
							if(count($l) === 0){
								return;
							}

							$p = $l[mt_rand(0, count($l) - 1)]->username;
							$params = substr_replace($params, $p, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
							$offsetshift -= strlen($selector[0]) - strlen($p) + 1;
							break;
					}
				}
			}
			$params = explode(" ", $params);
			if(count($params) === 1 and $params[0] === ""){
				$params = [];
			}

			if(($d1 = $this->server->api->dhandle("console.command." . $cmd, ["cmd" => $cmd, "parameters" => $params, "issuer" => $issuer, "alias" => $alias])) === false
				or ($d2 = $this->server->api->dhandle("console.command", ["cmd" => $cmd, "parameters" => $params, "issuer" => $issuer, "alias" => $alias])) === false){
				if(in_array(strtolower($cmd), array_keys($this->cmds))){
					$output = "You don't have permissions to use this command.\n";
				}else{
					$output = "Command doesn't exist! Use /help\n";
				}
			}elseif($d1 !== true and (!isset($d2) or $d2 !== true)){
				if(isset($this->cmds[$cmd]) and is_callable($this->cmds[$cmd])){
					$output = @call_user_func($this->cmds[$cmd], $cmd, $params, $issuer, $alias);
				}elseif($this->server->api->dhandle("console.command.unknown", ["cmd" => $cmd, "params" => $params, "issuer" => $issuer, "alias" => $alias]) !== false){
					$output = $this->defaultCommands($cmd, $params, $issuer, $alias);
				}
			}

			if($output != "" and ($issuer instanceof Player)){
				$issuer->sendChat(trim($output));
			}
			return $output;
		}
	}

	public function defaultCommands($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "defaultgamemode":
				$gms = [
					"0" => SURVIVAL,
					"survival" => SURVIVAL,
					"s" => SURVIVAL,
					"1" => CREATIVE,
					"creative" => CREATIVE,
					"c" => CREATIVE,
					"2" => ADVENTURE,
					"adventure" => ADVENTURE,
					"a" => ADVENTURE,
					"3" => VIEW,
					"view" => VIEW,
					"viewer" => VIEW,
					"spectator" => VIEW,
					"v" => VIEW,
				];
				if(!isset($params[0]) or !isset($gms[strtolower($params[0])])){
					return "Usage: /$cmd <mode>";
				}
				$this->server->api->setProperty("gamemode", $gms[strtolower($params[0])]);
				$output .= "Default Gamemode is now " . strtoupper($this->server->getGamemode()) . ".\n";
				break;
			case "status":
				$info = $this->server->debugInfo();
				if(!($issuer instanceof Player) && $issuer === "console"){
					$lightUpdates = 0;
					foreach($this->server->api->level->levels as $level){
						$lightUpdates += count($level->lightUpdates);
					}
					return "TPS: {$info["tps"]}, Memory usage: {$info["memory_usage"]} (Peak {$info["memory_peak_usage"]}), Entities: {$info["entities"]}, Events: {$info["events"]}, Handlers: {$info["handlers"]}, Actions: {$info["actions"]}, Garbage: {$info["garbage"]} Light updates: {$lightUpdates}";
				}
				return "TPS: {$info["tps"]}, Memory usage: {$info["memory_usage"]} (Peak {$info["memory_peak_usage"]})";
			case "stop":
				$this->loop->stop = true;
				$this->server->close();
				return "Stopping the server\n";
			case "difficulty":
				$s = trim(array_shift($params));
				if($s === "" or (((int) $s) > 3 and ((int) $s) < 0)){
					$output .= "Usage: /difficulty <0|1|2|3>\n";
					break;
				}
				$this->server->api->setProperty("difficulty", (int) $s);
				return "Difficulty changed to " . $this->server->difficulty . "\n";
			case "?":
			case "help":
				if(isset($params[0]) and !is_numeric($params[0])){
					$c = trim(strtolower($params[0]));
					if(isset($this->help[$c]) or isset($this->alias[$c])){
						$c = isset($this->help[$c]) ? $c : $this->alias[$c];
						if($this->server->api->dhandle("console.command." . $c, ["cmd" => $c, "parameters" => [], "issuer" => $issuer, "alias" => false]) === false or $this->server->api->dhandle("console.command", ["cmd" => $c, "parameters" => [], "issuer" => $issuer, "alias" => false]) === false){
							break;
						}
						$output .= "Usage: /$c " . $this->help[$c] . "\n";
						break;
					}
				}
				$cmds = [];
				foreach($this->help as $c => $h){
					if(isset($this->server->api->ban->cmdWhitelist[$c]) || !($issuer instanceof Player) || $this->server->api->ban->isOp($issuer)){
						$cmds[$c] = $h;
					}
				}

				$max = ceil(count($cmds) / 5);
				$page = (int) (isset($params[0]) ? min($max, max(1, intval($params[0]))) : 1);
				$output .= FORMAT_RED . "-" . FORMAT_RESET . " Showing help page $page of $max (/help <page>) " . FORMAT_RED . "-" . FORMAT_RESET . "\n";
				$current = 1;
				foreach($cmds as $c => $h){
					$curpage = (int) ceil($current / 5);
					if($curpage === $page){
						$output .= "/$c " . $h . "\n";
					}elseif($curpage > $page){
						break;
					}
					++$current;
				}
				break;
			default:
				$output .= "Command doesn't exist! Use /help\n";
				break;
		}
		return $output;
	}

	public static function debug($msg){
		console("[DEBUG] ".$msg, true, true, 2);
	}
	public static function info($msg){
		console("[INFO] ".$msg);
	}
	public static function notice($msg){
		console("[NOTICE] ".$msg);
	}
	public static function warn($msg){
		console("[WARNING] ".$msg);
	}
	public static function error($msg){
		console("[ERROR] ".$msg);
	}
}

class ConsoleLoop extends Thread{

	public $line;
	public $stop;
	public $base;
	public $ev;
	public $fp;
	public function __construct(){
		$this->line = false;
		$this->stop = false;
		$this->start();
	}

	public function stop(){
		$this->stop = true;
	}

	public function run(){
		if(!extension_loaded("readline")){
			$this->fp = fopen("php://stdin", "r");
		}

		while(!$this->stop){
			$this->line = $this->readLine();
			if($this->stop) break;
			$this->synchronized(function($t) {
				$this->wait();
				$this->line = false;
			}, $this);
		}

		if(!extension_loaded("readline")){
			@fclose($this->fp);
		}

		exit(0);
	}

	private function readLine(){
		if($this->fp){
			do{
				if($this->stop){
					ConsoleAPI::notice("Force stopping console thread!");
					return "";
				}
				$read = [$this->fp];
				$changed = stream_select($read, $write, $except, 1);
			}while($changed <= 0);
			$line = trim(fgets($this->fp));
		}else{
			$line = trim(readline(""));
			if($line != ""){
				readline_add_history($line);
			}
		}
		return $line;
	}
}

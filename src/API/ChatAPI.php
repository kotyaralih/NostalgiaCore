<?php

class ChatAPI{

	private $server;
	public $lastTells = [];
	function __construct(){
		$this->server = ServerAPI::request();
	}

	public function init(){
		$this->server->api->console->register("tell", "<player> <private message ...>", [$this, "commandHandler"]);
		$this->server->api->console->register("reply", "<private message ...>", [$this, "commandHandler"]);
		$this->server->api->console->register("me", "<action ...>", [$this, "commandHandler"]);
		$this->server->api->console->register("say", "<message ...>", [$this, "commandHandler"]);
		$this->server->api->console->cmdWhitelist("tell");
		$this->server->api->console->cmdWhitelist("reply");
		$this->server->api->console->cmdWhitelist("me");
		$this->server->api->console->alias("msg", "tell");
		$this->server->api->console->alias("r", "reply");
	}

	/**
	 * @param string $cmd
	 * @param array $params
	 * @param string $issuer
	 * @param string $alias
	 *
	 * @return string
	 */
	public function commandHandler($cmd, $params, $issuer, $alias){
		switch($cmd){
			case "say":
				$s = implode(" ", $params);
				if(trim($s) == ""){
					return "Usage: /say <message>";
				}
				$sender = ($issuer instanceof Player) ? "Server" : ucfirst($issuer);
				if(Utils::hasEmoji($s)) return "Your message contains illegal characters!";
				$this->server->api->chat->broadcast("[$sender] " . $s);
				break;
			case "me":
				$s = implode(" ", $params);
				if(trim($s) == ""){
					return "Usage: /me <message>";
				}
				if(!($issuer instanceof Player)){
					if($issuer === "rcon"){
						$sender = "Rcon";
					}else{
						$sender = ucfirst($issuer);
					}
				}else{
					$sender = $issuer->username;
				}
				$msg = implode(" ", $params);
				if(Utils::hasEmoji($msg)) return "Your message contains illegal characters!";
				$this->broadcast("* $sender $msg");
				break;
			case "tell":
				if(!isset($params[0]) or !isset($params[1])) return "Usage: /$cmd <player> <message>\n";
				
				if(!($issuer instanceof Player)) $sender = ucfirst($issuer);
				else $sender = $issuer->username;
				
				$n = array_shift($params);
				$target = $this->server->api->player->get($n);
				if($target instanceof Player){
					$target = $target->username;
				}else{
					$target = strtolower($n);
					if($target === "server" || $target === "console" || $target === "rcon"){
						$target = "Console";
					}else{
						return "$target is offline.";
					}
				}
				if(strtolower($target) === strtolower($sender)) return "You can't send message to yourself.";
				
				$mes = implode(" ", $params);
				if(Utils::hasEmoji($mes)) return "Your message contains illegal characters!";
				
				if($target !== "Console" && $target !== "Rcon") $this->sendTo(false, "$sender whispers to you: $mes", $target);
				if($target === "Console" || $sender === "Console") console("[INFO] $sender whispers to $target: $mes");
				
				
				$this->lastTells[strtolower($target)] = strtolower($sender);
				$this->lastTells[strtolower($sender)] = strtolower($target);
				
				return "You're whispering to $target: $mes";
			case "reply":
				if(!($issuer instanceof Player)) $sender = ucfirst($issuer);
				else $sender = $issuer->username;
				
				if(!isset($this->lastTells[strtolower($sender)])) return "You have no one to reply to.";
				$target = $this->lastTells[strtolower($sender)];
				if($target !== "server" && $target !== "console" && $target !== "rcon"){
					if(!($this->server->api->player->get($target) instanceof Player)){
						return "$target is offline.";
					}
				}
				$mes = implode(" ", $params);
				if(Utils::hasEmoji($mes)) return "Your message contains illegal characters!";
				
				if($target !== "Console" && $target !== "Rcon") $this->sendTo(false, "$sender whispers to you: $mes", $target);
				if($target === "Console" || $sender === "Console") console("[INFO] $sender whispers to $target: $mes");
				
				$this->lastTells[strtolower($target)] = strtolower($sender);
				$this->lastTells[strtolower($sender)] = strtolower($target);
				
				return "You're whispering to $target: $mes";
		}
	}

	/**
	 * @param string $message
	 */
	public function broadcast($message){
		$this->send(false, $message);
		$this->server->send2Discord($message);
	}

	/**
	 * @param mixed $owner Can be either Player object or string username. Boolean false for broadcast.
	 * @param string $text
	 * @param $whitelist
	 * @param $blacklist
	 */
	public function send($owner, $text, $whitelist = false, $blacklist = false){
		$message = [
			"player" => $owner,
			"message" => $text,
		];
		if($owner !== false){
			if($owner instanceof Player){
				if($whitelist === false){
					console("[INFO] <" . $owner->username . "> " . $text);
				}
			}else{
				if($whitelist === false){
					console("[INFO] <" . $owner . "> " . $text);
				}
			}
		}else{
			if($whitelist === false){
				console("[INFO] $text");
			}
			$message["player"] = "";
		}
		$container = new Container($message, $whitelist, $blacklist);
		$this->server->handle("server.chat", $container);
	}

	/**
	 * @param string $owner
	 * @param string $text
	 * @param mixed $player Can be either Player object or string username. Boolean false for broadcast.
	 */
	public function sendTo($owner, $text, $player){
		$this->send($owner, $text, [$player]);
	}
}

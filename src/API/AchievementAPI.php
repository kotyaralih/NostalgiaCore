<?php

class AchievementAPI{
	
	public static $achievements = [
		/*"openInventory" => array(
		 "name" => "Taking Inventory",
		 "requires" => array(),
		 ),*/
		"mineWood" => [
			"name" => "Getting Wood",
			"requires" => [
				//"openInventory",
			],
		],
		"buildWorkBench" => [
			"name" => "Benchmarking",
			"requires" => [
				"mineWood",
			],
		],
		"buildPickaxe" => [
			"name" => "Time to Mine!",
			"requires" => [
				"buildWorkBench",
			],
		],
		"buildFurnace" => [
			"name" => "Hot Topic",
			"requires" => [
				"buildPickaxe",
			],
		],
		"acquireIron" => [
			"name" => "Acquire hardware",
			"requires" => [
				"buildFurnace",
			],
		],
		"buildHoe" => [
			"name" => "Time to Farm!",
			"requires" => [
				"buildWorkBench",
			],
		],
		"makeBread" => [
			"name" => "Bake Bread",
			"requires" => [
				"buildHoe",
			],
		],
		"bakeCake" => [
			"name" => "The Lie",
			"requires" => [
				"buildHoe",
			],
		],
		"buildBetterPickaxe" => [
			"name" => "Getting an Upgrade",
			"requires" => [
				"buildPickaxe",
			],
		],
		"buildSword" => [
			"name" => "Time to Strike!",
			"requires" => [
				"buildWorkBench",
			],
		],
		"diamond" => [
			"name" => "DIAMONDS!",
			"requires" => [
				"acquireIron",
			],
		],
		"leather" => [
			"name" => "Cow Tipper",
			"requires" => [
				"buildSword",
			],
		],
		
	];
	/** @var PocketMinecraftServer */
	private $server;
	
	function __construct(){
		$this->server = ServerAPI::request();
	}
	
	/**
	 * Add an achievement
	 * @param string $achievementId
	 * @param string $achievementName display name
	 * @param array $requires a list of achievement ids, optional 
	 * @return boolean true if the achievement was added. false if the achievement with this id already exists.
	 */
	public static function addAchievement($achievementId, $achievementName, array $requires = []){
		if(!isset(self::$achievements[$achievementId])){
			self::$achievements[$achievementId] = [
				"name" => $achievementName,
				"requires" => $requires,
			];
			return true;
		}
		return false;
	}
	
	/**
	 * Gives the achievement to player
	 * @param Player $player
	 * @param string $achievementId
	 * @return boolean
	 */
	public static function grantAchievement(Player $player, $achievementId){
		if(isset(self::$achievements[$achievementId]) and !self::hasAchievement($player, $achievementId)){
			foreach(self::$achievements[$achievementId]["requires"] as $requerimentId){
				if(!self::hasAchievement($player, $requerimentId)){
					return false;
				}
			}
			if(ServerAPI::request()->api->dhandle("achievement.grant", ["player" => $player, "achievementId" => $achievementId]) !== false){
				$player->achievements[$achievementId] = true;
				self::broadcastAchievement($player, $achievementId);
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @param string $achievementId
	 * @return boolean
	 */
	public static function hasAchievement(Player $player, $achievementId){
		if(!isset(self::$achievements[$achievementId]) or !isset($player->achievements)){
			$player->achievements = [];
			return false;
		}
		
		if(!isset($player->achievements[$achievementId]) or !$player->achievements[$achievementId]){
			return false;
		}
		return true;
	}
	
	/**
	 * @param Player $player
	 * @param string $achievementId
	 * @return boolean
	 */
	public static function broadcastAchievement(Player $player, $achievementId){
		if(isset(self::$achievements[$achievementId])){
			$result = ServerAPI::request()->api->dhandle("achievement.broadcast", ["player" => $player, "achievementId" => $achievementId]);
			if($result !== false and $result !== true){
				if(ServerAPI::request()->api->getProperty("announce-player-achievements")){
					ServerAPI::request()->api->chat->broadcast($player->username . " has just earned the achievement [" . self::$achievements[$achievementId]["name"] . "]");
				}else{
					$player->sendChat("You have just earned the achievement [" . self::$achievements[$achievementId]["name"] . "]");
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Removes achievement from player.
	 * @param Player $player
	 * @param string $achievementId
	 */
	public static function removeAchievement(Player $player, $achievementId){
		if(self::hasAchievement($player, $achievementId)){
			$player->achievements[$achievementId] = false;
		}
	}
	
	public function viewAchievements($cmd, $params, $issuer, $alias)
	{
		if(!isset($params[0])){
			if(!($issuer instanceof Player)){
				return "Please enter a nickname.";
			}else{
				$data = $issuer->achievements;
			}
		}elseif(!($issuer instanceof Player) || $this->server->api->ban->isOp($issuer->iusername)){
			$player = $this->server->api->player->get($params[0]);
			$data = $player instanceof Player ? $player->data : $this->server->api->player->getOffline(trim($params[0]), false);
		}else{
			return false;
		}
		
		if(!$data){
			return "Player is not found.";
		}
		
		if($data instanceof Config){
			$achs = $data->get("achievements");
		}else{
			$achs = $data;
		}
		
		if(count($achs) <= 0){
			return "Player {$params[0]} unlocked 0 achievements";
		}
		$output = "Unlocked Achievements(".count($achs)."/".count(self::$achievements)."): ";
		foreach($achs as $achievement => $unlocked){
			if($unlocked && isset(self::$achievements[$achievement])){
				$info = self::$achievements[$achievement];
				$output .= "{$info["name"]}, ";
			}
		}
		return substr($output, 0, - 2);
	}
	
	public function init(){
		$this->server->api->console->register("ach", "<player>", [$this, "viewAchievements"]);
		$this->server->api->console->alias("getplayerachievements", "ach");
		$this->server->api->console->cmdWhitelist("ach");
	}
}

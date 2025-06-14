<?php

class Material
{
	public static $air, $dirt, $wood, $stone, $metal, $water, $lava, $leaves;
	public static $plant, $replaceable_plant, $sponge, $cloth, $fire, $sand;
	public static $decoration, $glass, $explosive, $coral, $ice, $topSnow;
	public static $snow, $cactus, $clay, $vegetable, $portal, $cake, $web;
	
	public $alwaysDestroyable, $flammable, $translucent, $replaceable;
	
	public $blocksLight, $blocksMotion, $isLiquid, $isSolid;
	public function __construct(){
		$this->alwaysDestroyable = true;
		$this->flammable = false;
		$this->translucent = false;
		$this->replaceable = false;

		$this->blocksLight = true;
		$this->blocksMotion = true;
		$this->isLiquid = false;
		$this->isSolid = true;
	}
	
	public function isSolidBlocking(){
		if($this->translucent) return false;
		return $this->blocksMotion;
	}
	public function letsWaterThrough(){
		if($this->isLiquid) return false;
		return !$this->isSolid;
	}
	
	public static function init(){
		self::$air = new GasMaterial();
		self::$dirt = new Material();
		self::$wood = new Material();
		self::$wood->flammable = true;
		self::$stone = new Material();
		self::$stone->alwaysDestroyable = false;
		self::$metal = new Material();
		self::$metal->alwaysDestroyable = false;
		self::$water = new LiquidMaterial();
		self::$lava = new LiquidMaterial();
		self::$leaves = new Material();
		self::$leaves->flammable = true;
		self::$leaves->translucent = true;
		self::$plant = new DecorationMaterial();
		self::$replaceable_plant = new DecorationMaterial();
		self::$replaceable_plant->replaceable = true;
		self::$replaceable_plant->flammable = true;
		self::$sponge = new Material();
		self::$cloth = new Material();
		self::$cloth->flammable = true;
		self::$fire = new GasMaterial();
		self::$fire->replaceable = true;
		self::$sand = new Material();
		self::$decoration = new DecorationMaterial();
		self::$glass = new Material();
		self::$glass->translucent = true;
		self::$explosive = new Material();
		self::$explosive->flammable = true;
		self::$explosive->translucent = true;
		self::$coral = new Material();
		self::$ice = new Material();
		self::$ice->translucent = true;
		self::$topSnow = new DecorationMaterial();
		self::$topSnow->translucent = true;
		self::$topSnow->replaceable = true;
		self::$topSnow->alwaysDestroyable = false;
		self::$snow = new Material();
		self::$snow->alwaysDestroyable = false;
		self::$cactus = new Material();
		self::$cactus->translucent = true;
		self::$clay = new Material();
		self::$vegetable = new Material();
		self::$portal = new Material();
		self::$cake = new Material();
		self::$web = new Material();
		self::$web->alwaysDestroyable = false;
		self::$web->blocksMotion = false;
	}
}


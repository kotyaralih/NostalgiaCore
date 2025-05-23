<?php

class DyeItem extends Item{
	
	public static $names = array(
		0 => "Ink Sac",
		1 => "Rose Red",
		2 => "Cactus Green",
		3 => "Cocoa Beans",
		4 => "Lapis Lazuli",
		5 => "Purple Dye",
		6 => "Cyan Dye",
		7 => "Light Gray Dye",
		8 => "Gray Dye",
		9 => "Pink Dye",
		10 => "Lime Dye",
		11 => "Dandelion Yellow",
		12 => "Light Blue Dye",
		13 => "Magenta Dye",
		14 => "Orange Dye",
		15 => "Bone Meal",
	);
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(DYE, $meta & 0xf, $count, "Dye");
		
		$this->name = self::$names[$this->meta];
	}
}

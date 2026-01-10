<?php

interface Breedable{
	public function isFood($id); //original name
	
	public function spawnChild($mate);
	
	public function isInLove();
}
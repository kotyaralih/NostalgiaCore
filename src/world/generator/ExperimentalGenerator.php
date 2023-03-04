<?php
/***REM_START***/
require_once("NewLevelGenerator.php");
/***REM_END***/
/**
 * @author Genisys & PocketMine
 * Thank You <3
 */
class ExperimentalGenerator implements NewLevelGenerator{
	
	private $populators = array();
	private $level;
	private $random;
	private $worldHeight = 65;
	private $waterHeight = 63;
	private $noiseHills;
	private $noisePatches;
	private $noisePatchesSmall;
	private $noiseBase;
	
	
	private static $GAUSSIAN_KERNEL = null;
	private static $SMOOTH_SIZE = 2;
	
	public function __construct(array $options = array()){
		ExperimentalGenerator::generateKernel();
	}
	
	private static function generateKernel(){
		ExperimentalGenerator::$GAUSSIAN_KERNEL = [];
		
		$bellSize = 1 / ExperimentalGenerator::$SMOOTH_SIZE;
		$bellHeight = 2 * ExperimentalGenerator::$SMOOTH_SIZE;
		
		for($sx = -ExperimentalGenerator::$SMOOTH_SIZE; $sx <= ExperimentalGenerator::$SMOOTH_SIZE; ++$sx){
			ExperimentalGenerator::$GAUSSIAN_KERNEL[$sx + ExperimentalGenerator::$SMOOTH_SIZE] = [];
			
			for($sz = -ExperimentalGenerator::$SMOOTH_SIZE; $sz <= ExperimentalGenerator::$SMOOTH_SIZE; ++$sz){
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				ExperimentalGenerator::$GAUSSIAN_KERNEL[$sx + ExperimentalGenerator::$SMOOTH_SIZE][$sz + ExperimentalGenerator::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}
	
	public function getSettings(){
		return array();
	}
	
	public function init(Level $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseHills = new NoiseGeneratorPerlin($this->random, 10);
		$this->noisePatches = new NoiseGeneratorSimplex($this->random, 2);
		$this->noisePatchesSmall = new NoiseGeneratorSimplex($this->random, 2);
		$this->noiseBase = new NoiseGeneratorPerlin($this->random, 4);
		
		$ores = new OrePopulator();
		$ores->setOreTypes(array(
			new OreType(new CoalOreBlock(), 20, 16, 0, 128),
			new OreType(New IronOreBlock(), 20, 8, 0, 64),
			new OreType(new RedstoneOreBlock(), 8, 7, 0, 16),
			new OreType(new LapisOreBlock(), 1, 6, 0, 32),
			new OreType(new GoldOreBlock(), 2, 8, 0, 32),
			new OreType(new DiamondOreBlock(), 1, 7, 0, 16),
			new OreType(new EmeraldOreBlock(), 1, 7, 0, 16), //TODO vanilla
			
			new OreType(new DirtBlock(), 20, 32, 0, 128),
			new OreType(new GravelBlock(), 10, 16, 0, 128),
			new OreType(new StoneBlock(1), 12, 16, 0, 128),
			new OreType(new StoneBlock(3), 12, 16, 0, 128),
			new OreType(new StoneBlock(5), 12, 16, 0, 128),
		));
		$this->populators[] = $ores;
		
		$trees = new TreePopulator();
		$trees->setBaseAmount(3);
		$trees->setRandomAmount(0);
		$this->populators[] = $trees;
		
		$tallGrass = new TallGrassPopulator();
		$tallGrass->setBaseAmount(5);
		$tallGrass->setRandomAmount(0);
		//$this->populators[] = $tallGrass;
	}
	
	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		$noiseArray = ExperimentalGenerator::getFastNoise3D($this->noiseBase, 16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);
		
		
		for($chunkY = 0; $chunkY < 8; ++$chunkY){
			$chunk = "";
			$startY = $chunkY << 4;
			$endY = $startY + 16;
			for($z = 0; $z < 16; ++$z){
				for($x = 0; $x < 16; ++$x){
					$minSum = 0;
					$maxSum = 0;
					$weightSum = 0;
					for($sx = -ExperimentalGenerator::$SMOOTH_SIZE; $sx <= ExperimentalGenerator::$SMOOTH_SIZE; ++$sx){
						for($sz = -ExperimentalGenerator::$SMOOTH_SIZE; $sz <= ExperimentalGenerator::$SMOOTH_SIZE; ++$sz){
							$weight = ExperimentalGenerator::$GAUSSIAN_KERNEL[$sx + ExperimentalGenerator::$SMOOTH_SIZE][$sz + ExperimentalGenerator::$SMOOTH_SIZE];
							
							/*if($sx === 0 and $sz === 0){
							 $adjacent = $biome;
							 }else{
							 $index = Level::chunkHash($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
							 if(isset($biomeCache[$index])){
							 $adjacent = $biomeCache[$index];
							 }else{
							 $biomeCache[$index] = $adjacent = $this->pickBiome($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
							 }
							 }*/
							
							$minSum += (63 - 1) * $weight; //TODO biomes
							$maxSum += 127 * $weight;
							
							$weightSum += $weight;
						}
					}
					
					$minSum /= $weightSum;
					$maxSum /= $weightSum;
					
					for($y = $startY; $y < $endY; ++$y){
						if($y === 0){
							$chunk .= "\x07";
							continue;
						}
						$noiseAdjustment = 2 * (($maxSum - $y) / ($maxSum - $minSum)) - 1;
						$caveLevel = $minSum - 10;
						$distAboveCaveLevel = max(0, $y - $caveLevel); // must be positive
						
						$noiseAdjustment = min($noiseAdjustment, 0.4 + ($distAboveCaveLevel / 10));
						$noiseValue = $noiseArray[$x][$z][$y] + $noiseAdjustment;
						
						if($noiseValue > 0){
							$chunk .= "\x01";
						}else{
							$chunk .= "\x00";
						}
					}
					$chunk .= "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
				}
			}
			$this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $chunk);
		}
		
	}
	
	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}
	public static function getFastNoise3D(NoiseGenerator $noise, $xSize, $ySize, $zSize, $xSamplingRate, $ySamplingRate, $zSamplingRate, $x, $y, $z){
		$noiseArray = array_fill(0, $xSize + 1, array_fill(0, $zSize + 1, []));
		
		for($xx = 0; $xx <= $xSize; $xx += $xSamplingRate){
			for($zz = 0; $zz <= $zSize; $zz += $zSamplingRate){
				for($yy = 0; $yy <= $ySize; $yy += $ySamplingRate){
					$noiseArray[$xx][$zz][$yy] = $noise->noise3D(($x + $xx) / 32, ($y + $yy) / 32, ($z + $zz) / 32, 2, 1/4, true);
				}
			}
		}
		
		for($xx = 0; $xx < $xSize; ++$xx){
			for($zz = 0; $zz < $zSize; ++$zz){
				for($yy = 0; $yy < $ySize; ++$yy){
					if($xx % $xSamplingRate !== 0 or $zz % $zSamplingRate !== 0 or $yy % $ySamplingRate !== 0){
						$nx = (int) ($xx / $xSamplingRate) * $xSamplingRate;
						$ny = (int) ($yy / $ySamplingRate) * $ySamplingRate;
						$nz = (int) ($zz / $zSamplingRate) * $zSamplingRate;
						
						$nnx = $nx + $xSamplingRate;
						$nny = $ny + $ySamplingRate;
						$nnz = $nz + $zSamplingRate;
						
						$dx1 = (($nnx - $xx) / ($nnx - $nx));
						$dx2 = (($xx - $nx) / ($nnx - $nx));
						$dy1 = (($nny - $yy) / ($nny - $ny));
						$dy2 = (($yy - $ny) / ($nny - $ny));
						
						$noiseArray[$xx][$zz][$yy] = (($nnz - $zz) / ($nnz - $nz)) * (
							$dy1 * (
								$dx1 * $noiseArray[$nx][$nz][$ny] + $dx2 * $noiseArray[$nnx][$nz][$ny]
								) + $dy2 * (
									$dx1 * $noiseArray[$nx][$nz][$nny] + $dx2 * $noiseArray[$nnx][$nz][$nny]
									)
							) + (($zz - $nz) / ($nnz - $nz)) * (
								$dy1 * (
									$dx1 * $noiseArray[$nx][$nnz][$ny] + $dx2 * $noiseArray[$nnx][$nnz][$ny]
									) + $dy2 * (
										$dx1 * $noiseArray[$nx][$nnz][$nny] + $dx2 * $noiseArray[$nnx][$nnz][$nny]
										)
								);
					}
				}
			}
		}
		
		return $noiseArray;
	}
	public function getSpawn(){
		return $this->level->getSafeSpawn(new Vector3(127.5, 128, 127.5));
	}
	public function populateLevel()
	{}
	
	
}
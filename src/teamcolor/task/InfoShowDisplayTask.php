<?php

namespace teamcolor\task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use teamcolor\Main;

/*コピーしてきただけなのでぐちゃぐちゃ
LiveXYZを参考に作成中
*/
class ShowDisplayTask extends Task{

	/** @var Player */
	private $player;
	private $mode;

	public function __construct(Player $player, string $mode = "popup"){
		$this->player = $player;
		$this->mode = $mode;
	}

	public function onRun(int $currentTick) : void{

		assert(!$this->player->isClosed());
		$location = "Location: " . TextFormat::GREEN . "(" . Utils::getFormattedCoords($this->player->getX(), $this->player->getY(), $this->player->getZ()) . ")" . TextFormat::WHITE . "\n";
		$world = "World: " . TextFormat::GREEN . $this->player->getLevel()->getName() . TextFormat::WHITE . "\n";
		$direction = "Direction: " . TextFormat::GREEN . Utils::getCompassDirection($this->player->getYaw()) . " (" . $this->player->getYaw() . ")" . TextFormat::WHITE . "\n";
		
		switch($this->mode){
			case "tip":
				$this->player->sendTip($location . $world . $direction);
				break;
			case "popup":
				$this->player->sendPopup($location . $world . $direction);
				break;
			default:
				break;
		}
	}
}
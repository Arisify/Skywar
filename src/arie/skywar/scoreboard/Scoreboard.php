<?php
declare(strict_types=1);

namespace arie\skywar\scoreboard;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class Scoreboard{
	use SingletonTrait;

	/** @var array */
	private array $scoreboards = [];

	public function create(Player $player, string $objectiveName, string $displayName, string $criteriaName = "dummy", int $sortOrder = 0, string $displaySlot = "sidebar") : void{
		if (isset($this->scoreboards[$player->getName()])) {
			$this->remove($player);
		}
		$pk = SetDisplayObjectivePacket::create(
			$displaySlot,
			$objectiveName,
			$displayName,
			$criteriaName,
			$sortOrder
		);
		$player->getNetworkSession()->sendDataPacket($pk);
		$this->scoreboards[$player->getName()] = $objectiveName;
	}

	public function getObjectiveName(Player $player) : ?string{
		return $this->scoreboards[$player->getName()] ?? null;
	}

	public function remove(Player $player) : void{
		$objectiveName = $this->getObjectiveName($player);
		$pk = RemoveObjectivePacket::create($objectiveName);
		$player->getNetworkSession()->sendDataPacket($pk);
		unset($this->scoreboards[$player->getName()]);
	}

	public function setLine(PLayer $player, int $score, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER) : bool{
		assert($score < 16 && $score > 0, "Line must be greater than 0 and smaller than 16");
		if (isset($this->scoreboards[$player->getName()])) {
			return false;
		}
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->getObjectiveName($player);
		$entry->type = $type;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;

		$pk = SetScorePacket::create(SetScorePacket::TYPE_CHANGE, [$entry]);
		$player->getNetworkSession()->sendDataPacket($pk);
		return true;
	}

	public function onPlayerLeft(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		unset($this->scoreboards[$player->getName()]);
	}
}
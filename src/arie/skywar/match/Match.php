<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 * @author Arisify
 * @link   https://github.com/Arisify
 * @license https://opensource.org/licenses/MIT MIT License
 *
 * \    /\
 *  )  ( ') ᵐᵉᵒʷˢ
 * (  /  )
 *  \(__)|
 *
*/
declare(strict_types=1);

namespace arie\skywar\match;

use arie\skywar\language\Language;
use arie\skywar\Skywar;
use dktapps\pmforms\MenuForm;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

class Match{

	//Game states
	public const STATE_UNAVAILABLE = -1;
	public const STATE_WAITING = 0; //Lobby, vote, etc
	public const STATE_COUNTDOWN = 1; //Count down
	public const STATE_OPEN_CAGE = 2; //Wait for cage to open
	public const STATE_IN_GAME = 3; //Game started
	public const STATE_RESTART = 4; //Restart the game, tp all to lobby



	private array $players = [];
	private int $player_amount = 0;
	private int $state;
	private \SplFixedArray $slots;

	public function __construct(
		private Skywar $plugin
	){
		//TODO
		$this->slots = new \SplFixedArray($data["slot"]);
	}

	public function join(Player $player, int $team = 0) : bool{
		if ($this->state = self::STATE_OPEN_CAGE || ($this->players_amount == $this->slots->getSize())) {
			return false;
		}

		$this->players[$player->getName()] = $player;

		if ($this->getMatchManager()->isSavePlayerData()) {
			$this->players_old_data[$player->getName()] = \SplFixedArray::fromArray([ //CPU USAGE BRUH
				$player->getInventory()->getContents(),
				$player->getArmorInventory()->getContents(),
				$player->getCursorInventory()->getContents(),
				$player->getOffHandInventory()->getContents(),
				$player->getEffects()->all(),
				$player->getGamemode(),
				$player->isFlying(),
				$player->getAllowFlight(),
				$player->getPosition(),
				$player->getHealth(),
				$player->isOnFire(),
				$player->getHungerManager()->getFood(),
				$player->getHungerManager()->getExhaustion(),
				$player->getHungerManager()->getSaturation(),
				$player->getXpManager()->getXpLevel(),
				$player->getXpManager()->getXpProgress()
			]);
		}
		$player->setGamemode(GameMode::ADVENTURE());
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getOffHandInventory()->clearAll();
		$player->getEffects()->clear();
		$player->setFlying(false);
		$player->setAllowFlight(false);
		$player->setHealth($player->getMaxHealth());
		$hungerManager = $player->getHungerManager();
		$hungerManager->setFood($hungerManager->getMaxFood());
		$hungerManager->setExhaustion(0);
		$hungerManager->setSaturation(0);
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$this->giveHotbarItems($player, self::PLAYER_WAITING);
		$player->teleport($this->waiting_lobby);
		++$this->player_amount;
		return true;
	}

	public function left(Player $player, string $message = "", int $state = self::PLAYER_WAITING) : bool{
		if (!isset($this->players[$player->getName()])) {
			return false;
		}

		if ($this->getArenaManager()->isSavePlayerData()) {
			$player_old_data = $this->players_old_data[$player->getName()];
			$player->setGamemode($player_old_data[5]);
			$player->getInventory()->setContents($player_old_data[0]);
			$player->getArmorInventory()->setContents($player_old_data[1]);
			$player->getCursorInventory()->setContents($player_old_data[2]);
			$player->getOffHandInventory()->setContents($player_old_data[3]);
			foreach ($player_old_data[4] as $effect) {
				$player->getEffects()->add($effect);
			}
			$player->setFlying($player_old_data[6]);
			$player->setAllowFlight($player_old_data[7]);
			$player->teleport($player_old_data[9]);
			$player->setHealth($player_old_data[10]);

			$hungerManager = $player->getHungerManager();
			$hungerManager->setFood($player_old_data[11]);
			$hungerManager->setExhaustion($player_old_data[12]);
			$hungerManager->setSaturation($player_old_data[13]);

			$player->getXpManager()->setXpAndProgress($player_old_data[14], $player_old_data[15]);
		} else {
			$player->setGamemode(GameMode::ADVENTURE());
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->getCursorInventory()->clearAll();
			$player->getOffHandInventory()->clearAll();
			$player->getEffects()->clear();
			$player->setFlying(false);
			$player->setAllowFlight(false);
			$player->setHealth($player->getMaxHealth());
			$hungerManager = $player->getHungerManager();
			$hungerManager->setFood($hungerManager->getMaxFood());
			$hungerManager->setExhaustion(0);
			$hungerManager->setSaturation(0);
			$player->getXpManager()->setXpAndProgress(0, 0.0);
			$player->teleport($this->lobby->add(0.5, 0, 0.5));
		}

		unset($this->players[$player->getName()], $this->spectators[$player->getName()], $this->players_old_data[$player->getName()]);
		--$this->player_amount;
		return true;
	}

	public function getMatchManager() : ?MatchManager{
		return $this->plugin->getMatchManager();
	}

	/**
	 * @return Skywar
	 */
	public function getPlugin() : Skywar{
		return $this->plugin;
	}
}
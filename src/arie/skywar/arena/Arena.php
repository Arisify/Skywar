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

namespace arie\skywar\arena;

use arie\skywar\language\LanguageManager;
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

class Arena{

	//Player states
	public const PLAYER_WAITING = 0;
	public const PLAYER_COUNTDOWN = 1;
	public const PLAYER_IN_GAME = 2;
	public const PLAYER_SPECTATOR = 3;

	public const BROADCAST_MESSAGE = 0;
	public const BROADCAST_TITLE = 1;
	public const BROADCAST_POPUP = 2;
	public const BROADCAST_TIP = 3;

	public const BROADCAST_PLAYER = 0;
	public const BROADCAST_SPECTATOR = 1;
	public const BROADCAST_ALL = 2;

	/** @var int */
	public int $game_state = self::STATE_UNAVAILABLE;

	private bool $hasCage = false;

	private array $players = [];
	private int $player_amount = 0;
	private array $spectators = [];
	private Position $lobby;
	private Position $waiting_lobby;
	private World $world;
	private array $players_old_data = [];
	private \SplFixedArray $slots;
	private array $map_data = [];
	/** @var ArenaSchedule */
	private ArenaSchedule $schedule;

	public function __construct(
		private Skywar $plugin,
		private int    $id,
		private string $name,
		array          $data = []
	){
		$worldManager = $this->plugin->getServer()->getWorldManager();
		$this->lobby = $this->plugin->getArenaManager()->getDefaultLobby();
		$this->waiting_lobby = $this->plugin->getArenaManager()->getWaitingPosition();
		$this->slots = new \SplFixedArray($data["slot"]);
		$this->schedule = new ArenaSchedule($this);
	}

	public function getName() : string{
		return $this->name;
	}

	public function getId() : int{
		return $this->id;
	}

	public function isEnabled() : bool{
		return $this->game_state !== self::STATE_UNAVAILABLE;
	}

	public function getLanguage() : ?LanguageManager{
		return $this->plugin->getLanguage();
	}

	public function getArenaManager() : ?ArenaManager{
		return $this->plugin->getArenaManager();
	}

	public function broadcastMessage(string $message, string $subMessage, int $broadcast_type = self::BROADCAST_TITLE) : bool{
		return false;
	}




	//Called when the last 15' countdown
	public function prepareMap() : bool{
		$world = $this->plugin->getArenaManager()->makeMap($this);
		if ($world === null) {
			foreach ($this->players as $player) {
				$this->left($player);
			}
			return false;
		}
		$this->world = $world;
		//$this->slots = \SplFixedArray::fromArray($this->maps[]); Todo: fix the logic
		return true;
	}

	public function poststart() : bool{
		if (!$this->isEnabled() && count($this->players) > $this->slots->getSize()) { //This is the wrong logic, my brain cannot handle it now...
			return false;
		}
		$slot = -1;
		foreach ($this->players as $player) {
			$pos = $this->slots[++$slot];
			$player->teleport($pos->add(0.5, 0, 0.5));
			if ($this->hasCage()) {
				$this->addCage($pos, VanillaBlocks::GLASS());
			}
		}
		return true;
	}

	public function start() : bool{
		foreach ($this->players as $player) {
			if ($this->hasCage()) {
				$this->addCage($player->getPosition(), VanillaBlocks::AIR());
			}
			$player->setGamemode(GameMode::SURVIVAL());
		}
		return true;
	}

	public function canRestart() : bool{
		return $this->player_amount < 2;
	}

	public function restart() : bool{

		return true;
	}


	public function getPlayerAmount() : int{
		return $this->player_amount;
	}

	public function hasCage() : bool{
		return $this->hasCage;
	}

	private function addCage(Position $pos, $block) : void{
		$offsets = [
			[0, 2, 0], [1, 1, 0], [0, 1, 1], [0, 1, -1], [-1, 1, 0],
			[0, -1, 0], [1, 0, 0], [-1, 0, 0], [0, 0, 1], [0, 0, -1]
		];
		foreach ($offsets as $offset) {
			$opos = $pos->add($offset[0], $offset[1], $offset[2]);
			if ($this->world->getBlock($opos)->getId() === BlockLegacyIds::AIR) {
				continue;
			}
			$this->world->setBlock($opos, $block);
		}
	}

	public function getWorld() : ?World{
		return $this->world;
	}

	public function getMaxSlot() : int{
		return $this->slots->count();
	}

	public function getPlayers() : array{
		return $this->players;
	}

	public function addSpectator(Player $player) : void{
		$this->spectators[$player->getName()] = $player;
		unset($this->players[$player->getName()]);
		$player->setGamemode(GameMode::SPECTATOR());
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getOffHandInventory()->clearAll();
		$player->getEffects()->clear();
		$player->setFlying(true);
		$player->setAllowFlight(true);
		$player->setHealth($player->getMaxHealth());
		$this->giveHotbarItems($player, self::PLAYER_SPECTATOR);
	}

	public function giveHotbarItems(Player $player, int $state = 0) : void{
		switch ($state) {
			case self::PLAYER_SPECTATOR:
				$player->getInventory()->setItem(0, $this->getItemWithStringTag(VanillaItems::COMPASS()->setCustomName("Options"), "options"));
				$player->getInventory()->setItem(8, $this->getItemWithStringTag(VanillaBlocks::BED()->asItem()->setCustomName("Return to lobby"), "lobby"));
				break;
			case self::PLAYER_WAITING:
				$player->getInventory()->setItem(0, $this->getItemWithStringTag(VanillaItems::FEATHER()->setCustomName("Kit"), "kits"));
				$player->getInventory()->setItem(1, $this->getItemWithStringTag(VanillaItems::BOOK()->setCustomName("Vote for maps!"), "maps"));
				$player->getInventory()->setItem(8, $this->getItemWithStringTag(VanillaBlocks::BED()->asItem()->setCustomName("Return to lobby"), "lobby"));
				break;
		}
	}

	private function getItemWithStringTag(Item $item, string $string) : Item{
		$item->getNamedTag()->setString("sw-items", $string);
		return $item;
	}

	public function reschedule() : void{
		$this->plugin->getArenaManager()->reloadWorld($this->world);
		$this->players = [];
		$this->spectators = [];
		$this->players_old_data = [];
	}

	public function onDamage(EntityDamageEvent $event) : void{
		$player = $event->getEntity();
		if (!$player instanceof Player) {
			return;
		}
		if (!isset($this->players[$player->getName()])) {
			return;
		}
		if ($this->game_state === self::STATE_IN_GAME) {
			if ($player->getHealth() <= 0) {
				$drops = $player->getDrops();
				foreach ($drops as $drop) {
					$this->world->dropItem($player->getLocation(), $drop);
				}
				$this->world->dropExperience($player->getLocation(), $player->getXpDropAmount());
				$player->getXpManager()->setXpAndProgress(0, 0.0);
				$this->addSpectator($player);
			}
		} else {
			$event->cancel();
		}
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		if (isset($this->players[$player->getName()]) || isset($this->spectators[$player->getName()])) {
			unset($this->players[$player->getName()], $this->spectators[$player->getName()], $this->players_old_data[$player->getName()]);
		}
	}

	public function onExhaust(PlayerExhaustEvent $event) : void{
		$player = $event->getPlayer();
		if ($this->game_state !== self::STATE_IN_GAME && isset($this->players[$player->getName()])) {
			$event->cancel();
		}
	}

	public function onMove(PlayerMoveEvent $event) : void{
		$player = $event->getPlayer();
		if ($this->game_state === self::STATE_COUNTDOWN && isset($this->players[$player->getName()])) {
			$event->cancel();
		}
	}

	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) : void{
		$player = $event->getPlayer();
		$cmd = $event->getMessage();
		print_r($cmd);
		if ($cmd === "/kill") {
			$player->sendMessage("You cannot use this command while in game!");
			$event->cancel();
		}
	}

	public function onItemUse(PlayerItemUseEvent $event) : void{
		$player = $event->getPlayer();
		if (isset($this->players[$player->getName()])) {
			$item = $event->getItem();
			switch ($item->getNamedTag()->getTag("arie-items")) {
				case "options":
					break;
				case "lobby":
					$player->teleport($this->lobby);
					break;
				case "kits":
					$this->sendKitUI($player);
					break;
				case "maps":
					$player->sendForm($this->getMapVotingForm());
					break;
				default:
					return;
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		if (isset($this->players[$player->getName()])) {
			$block = $event->getBlock();
			if ($this->game_state !== self::STATE_IN_GAME) {
				$event->cancel();
			}
			$isBan = match ($block->getId()) {
				BlockLegacyIds::BED_BLOCK, BlockLegacyIds::ENDER_CHEST, BlockLegacyIds::DISPENSER, BlockLegacyIds::DROPPER => true,
				default => false,
			};
			if ($isBan) {
				$event->cancel();
			}
		}
	}

	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		if ($player instanceof Player) {
			if (isset($this->players[$player->getName()])) {
				$this->left($player);
			}
			if (isset($this->spectators[$player->getName()])) {
				$this->left($player);
			}
		}
	}

	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) : void{
		$player = $event->getPlayer();
		$cmd = $event->getMessage();
		if ($cmd === "/kill") {
			$player->sendMessage("You cannot use this command while in game!");
			$event->cancel();
		}

		if ($cmd === "/pl") {
			$player->sendMessage("You cannot use this command while in game!");
			$event->cancel();
		}
	}

	public function onWorldUnload(WorldUnloadEvent $event) : void{
		if ($event->getWorld()->getFolderName() === $this->world->getFolderName()) {
			$this->reschedule();
		}
	}

	public function getMostRatedMap() : string{
		// $this->slots = \SplFixedArray::fromArray($this->plugin->getArenaManager()->getMaps()[]); Todo: fix the logic
		return array_key_first(array_count_values($this->maps)); //Performance drops?
	}

	private function getMapVotingForm(Player $player) : ?MenuForm{
		return new MenuForm(
			"SKYWAR",
			"Tap any button to vote for your map",
			array_map(static fn($v) => $v["name"], $this->plugin->getArenaManager()->getMaps()),
			function(Player $player, int $selected) : void{
				$this->maps[$player->getName()] = $this->plugin->getArenaManager()->getMaps()[$selected]["name"];
			},
		);
	}
}

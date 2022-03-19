<?php
declare(strict_types=1);

namespace arie\skywar\arena;

use pocketmine\world\Position;
use pocketmine\world\World;

use pocketmine\world\WorldManager;
use arie\skywar\Skywar;

class ArenaManager{
	private array $arenas = [];

	protected int $default_countdown_time = 45;
	protected int $default_opencage_time = 15;
	protected int $default_game_time = 20 * 60;
	protected int $default_restart_time = 15;
	protected int $default_force_time = 15;

	private int $arena_limit = -1; //-1 means no limit

	private bool $savePlayerData;

	private ?Position $lobby_pos = null;
	private ?Position $waiting_pos = null;

	public array $maps = [];
	private string $map_path;

	public array $commandsBanLists;
	private bool $archiveMap;

	public function __construct(private Skywar $plugin) {
		$this->map_path = $this->plugin->getDataFolder() . "maps/";
		$this->commandsBanLists = (array) $this->plugin->getConfig()->get("skywar.commands.banned", []);
		$this->savePlayerData = (bool) $this->plugin->getConfig()->get("skywar.settings-save_player_inventory", true);
		$this->archiveMap = (bool) $this->plugin->getConfig()->get("skywar.settings-archive_map", true);
		$this->arena_limit = (int) $this->plugin->getConfig()->get("skywar.settings-arena_limit", -1);

		$this->default_countdown_time = $this->plugin->getConfig()->get("skywar.time-countdown", 45);
		$this->default_opencage_time = $this->plugin->getConfig()->get("skywar.time-opencage", 15);
		$this->default_game_time = $this->plugin->getConfig()->get("skywar.time-game", 20*60);
		$this->default_restart_time = $this->plugin->getConfig()->get("skywar.time-restart", 15);
		$this->default_force_time = $this->plugin->getConfig()->get("skywar.time-force", 15);

		if ($this->archiveMap) {
			$maps = glob($this->map_path, GLOB_ONLYDIR);

		}

		foreach ($maps as $map) {
			$map_path = $this->map_path . $map . "zip";
			$data_path = $this->map_path . $map . "json";
			if (($this->archiveMap && !is_file($map_path)) || !is_file($data_path)) {
				$this->plugin->getLogger()->notice("Maps file is corrupted or missing");
				continue;
			}
			try {
				$data = json_decode($data_path, false, 512, JSON_THROW_ON_ERROR);
			} catch (\JsonException $e) {
				$this->plugin->getLogger()->notice("Maps file is corrupted or missing");
				continue;
			}
			$this->maps[$map] = \SplFixedArray::fromArray($data);
		}
	}

	public function setLobbyLocation(Position $position) : bool{
		$this->lobby_pos = $position;
		return true;
	}

    public function setWaitingLocation(Position $position) : bool{
        $this->waiting_pos = $position;
        return true;
    }

	public function getLobbyPosition(World $world) : ?Position{
		return ($this->lobby_pos ?? $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
	}

	public function getWaitingPosition() : ?Position{
		return ($this->waiting_pos ?? $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation())->add(0.5, 0, 0.5);
	}

	public function saveWorld(World $world) : bool{
		$worldPath = $this->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName();
		$zipPath = $this->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $world->getFolderName() . ".zip";

		$zip = new \ZipArchive();
		if (file_exists($zipPath)) {
			$this->plugin->getLogger()->notice("File already exists, proceed to overwrite: $zipPath");
			unlink($zipPath);
		}
		if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
			$dir = opendir($worldPath);

			while($file = readdir($dir)) {
				if(is_file($worldPath . $file)) {
					$zip->addFile($worldPath . $file, $file);
				}
			}
			$zip ->close();
			return true;
		}
		$zip ->close();
		return false;
	}

	public function reloadMap(string $filePath) : bool{
		if (pathinfo($filePath, PATHINFO_EXTENSION) !== "zip") {
			$this->plugin->getLogger()->notice("File is not a zip, proceed to cancel: $filePath");
			return false;
		}
		$zipArchive = new \ZipArchive();
		if ($zipArchive->open($filePath)) {
			$zipArchive->extractTo($this->plugin->getServer()->getDataPath() . "worlds");
			$zipArchive->close();
			return true;
		}
		return false;
	}

	public function reloadWorld(World $world, bool $justSave = false) : ?World{
		$worldManager = $this->plugin->getServer()->getWorldManager();
		$folderName = $world->getFolderName();
		$zipPath = $this->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $folderName . ".zip";

		if (!$this->reloadMap($zipPath)) {
			return null;
		}

		if ($justSave) {
			return null;
		}
		return $worldManager->loadWorld($folderName) ? $worldManager->getWorldByName($folderName) : null;
	}

	public function makeMap(Arena $arena) : ?World{
		$world = $arena->getWorld();
		$map = $arena->getMostRatedMap();
		//$fromPath = $this->plugin->getDataFolder() . "maps" . DIRECTORY_SEPARATOR . $map . ".zip";
		//$toPath   = $world->getServer()->getDataPath() . "worlds/";

		//$this->reloadMap($toPath);
		//if (rename($toPath . $map, $toPath . "arie-" . $arena->getId())) {
		//	return $this->worldManager->getWorldByName("arie-" . $arena->getId());
		//}
		//unlink($toPath . $map);
		return null;
	}

	public function removeArena(string $name) : bool{
		if (isset($this->arenas[$name])) {
			unset($this->arenas[$name]);
			return true;
		}
		return false;
	}

	public function getArena(string $name) : ?Arena{
		return $this->arenas[$name] ?? null;
	}

	public function getAllArena() : array{
		return $this->arenas;
	}

	public function getAvailableArena() : ?Arena{
		$arena = null;
		$arenas = [];
		$i = -1;
		foreach($this->arenas as $a) {
			if (!$a->isEnabled()) {
				continue;
			}
			if ($arena === null || count($a->getPlayers()) < $a->getMaxSlot()) {
				$arenas[++$i] = $arena;
			}
		}
		return $i > -1 ? $arenas[mt_rand(0, $i)] : null;
	}

	/**
	 * @return array
	 */
	public function getMapsList() : array{
		return $this->maps;
	}

	/**
	 * @return array|mixed
	 */
	public function getCommandsBanLists() : mixed {
		return $this->commandsBanLists;
	}

	/**
	 * @return bool|mixed
	 */
	public function isSavePlayerData() : mixed {
		return $this->savePlayerData;
	}

	/**
	 * @return int
	 */
	public function getDefaultCountdownTime() : int {
		return $this->default_countdown_time;
	}

	/**
	 * @param int $default_countdown_time
	 */
	public function setDefaultCountdownTime(int $default_countdown_time) : void {
		$this->default_countdown_time = $default_countdown_time;
	}

	/**
	 * @return int
	 */
	public function getDefaultOpencageTime() : int {
		return $this->default_opencage_time;
	}

	/**
	 * @param int $default_opencage_time
	 */
	public function setDefaultOpencageTime(int $default_opencage_time) : void {
		$this->default_opencage_time = $default_opencage_time;
	}

	/**
	 * @return float|int
	 */
	public function getDefaultGameTime() : float|int {
		return $this->default_game_time;
	}

	/**
	 * @param float|int $default_game_time
	 */
	public function setDefaultGameTime(float|int $default_game_time) : void {
		$this->default_game_time = $default_game_time;
	}

	/**
	 * @return int
	 */
	public function getDefaultRestartTime() : int {
		return $this->default_restart_time;
	}

	/**
	 * @param int $default_restart_time
	 */
	public function setDefaultRestartTime(int $default_restart_time) : void {
		$this->default_restart_time = $default_restart_time;
	}

	/**
	 * @return int|mixed
	 */
	public function getDefaultForceTime() : mixed{
		return $this->default_force_time;
	}

	/**
	 * @param int|mixed $default_force_time
	 */
	public function setDefaultForceTime(mixed $default_force_time) : void{
		$this->default_force_time = $default_force_time;
	}

	/**
	 * @return bool|int|mixed
	 */
	public function getArenaLimit() : int{
		return $this->arena_limit;
	}

	/**
	 * @param bool|int|mixed $arena_limit
	 */
	public function setArenaLimit(int $arena_limit) : void{
		$this->arena_limit = $arena_limit;
	}
}
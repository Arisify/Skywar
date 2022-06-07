<?php
/**
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

use pocketmine\plugin\PluginBase;

class MatchManager{
	private array $matches= [];
	private array $data;


	public int $match_limit;


	public function __construct(
		private PluginBase $plugin,
	){
		$config = $this->plugin->getConfig();
		$this->default_countdown_time =  $config->getNested("settings.time-countdown", 45);
		$this->default_opencage_time = $config->getNested("settings.time-opencage", 15);
		$this->default_game_time = $config->getNested("settings.time-game", 20*60);
		$this->default_restart_time = $config->getNested("settings.time-restart", 15);
		$this->default_boost_time = $config->getNested("settings.time-boost", 15);

		$this->match_limit = (int) $config->getNested("settings.match-limit", -1);
		$this->data = [
			"match" => [
				"crafting" => (bool) $config->getNested("settings.match-crafting", true),
				"kill_counter" => (bool) $config->getNested("settings.match-kill_counter", true),
				"save_player_data" => (bool) $config->getNested("settings.match-save_player_data", true),
				"command_black_list" => (bool) $config->getNested("settings.match-command_blacklist", true),
			]
		];
		$this->broadcast_winner = (bool) $config->getNested("settings.match-broadcast_winner", true);
		$this->command_blacklist = (array) (array) $this->plugin->getConfig()->get("skywar.commands.banned", null);
	}

	public function createMatch(string $id, array $data = []) : bool{
		$this->matches[$id] = $data;
		return true;
	}

	public function getDefaultMatchInfo() : array{
		return [
			"time" => [
				"countdown" => $this->default_countdown_time,
				"opencage" => $this->default_opencage_time,
				"game" => $this->default_game_time,
				"restart" => $this->default_restart_time,
				"boost" => $this->default_boost_time
			],
			"match" => [
				"lifes" =>  $this->default_lifes,
				""
			]
		];
	}

	/**
	 * @return PluginBase
	 */
	public function getPlugin() : PluginBase{
		return $this->plugin;
	}

	/**
	 * @return array
	 */
	public function getCommandBlacklist() : array{
		return $this->command_blacklist;
	}
}
<?php
declare(strict_types=1);

namespace arie\skywar\match;

use pocketmine\utils\Config;

class MatchInfo{
	public array $command_blacklist = [];
	public bool $broadcast_winner;
	public bool $crafting_allowed;
	public bool $kdr_applied;
	public bool $save_player_data;

	public int $life = 1;
	public int $health = 20;

	public int $countdown_time;
	public int $opencage_time;
	public int $game_time;
	public int $restart_time;
	public int $boost_time;
	public string $map = "";

	public static function create(
		int $countdown_time, int $opencage_time, int $game_time, int $restart_time, int $boost_time,
		array $command_blacklist, bool $broadcast_winner, bool $crafting_allowed, bool $kdr_applied,
		bool $save_player_data, int $life, int $health, string $map
	) : self{
		$match_info = new self;

		$match_info->countdown_time = $countdown_time;
		$match_info->opencage_time = $opencage_time;
		$match_info->game_time = $game_time;
		$match_info->restart_time = $restart_time;
		$match_info->boost_time = $boost_time;

		$match_info->command_blacklist = $command_blacklist;
		$match_info->broadcast_winner = $broadcast_winner;
		$match_info->crafting_allowed = $crafting_allowed;
		$match_info->kdr_applied = $kdr_applied;
		$match_info->save_player_data = $save_player_data;
		$match_info->life = $life;
		$match_info->health = $health;
		$match_info->map = "";
		return $match_info;
	}

	/**
	 * @return array
	 */
	public function getCommandBlacklist() : array{
		return $this->command_blacklist;
	}

	public static function fromArray(array $data) : MatchInfo{
		//To-do
	}

	public function toArray() : array{
		return [
			"time" => [
				"countdown" => $this->countdown_time,
				"opencage" => $this->opencage_time,
				"game" => $this->opencage_time,
				"restart" => $this->restart_time,
				"boost" => $this->boost_time
			],
			"command_black_list" => $this->command_blacklist,
			"kdr_applied" => $this->kdr_applied,
			""
		];
	}

	public static function fromConfig(Config $config) : MatchInfo{

	}

	public static function default(Match $match) : array{
		return $match->getMatchManager()->getDefaultMatchInfo();
	}
}
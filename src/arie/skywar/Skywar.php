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

namespace arie\skywar;

use arie\language\LanguageManager;
use arie\language\LanguageTag;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use arie\scoreboard\Scoreboard;
use arie\skywar\match\MatchManager;
use arie\yamlcomments\YamlComments;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;

use skymin\CommandLib\CmdManager;

final class Skywar extends PluginBase implements Listener{
	private static Skywar $instance;

	private MatchManager $match_manager;
	private LanguageManager $language_manager;
	private Scoreboard $scoreboard;
	private YamlComments $yamlcomments;

	public function onLoad() : void{
		self::$instance = $this;
		foreach ($this->getResources() as $resource) {
			$this->saveResource($resource->getFilename());
		}
		$this->language_manager = new LanguageManager($this, "language", "en-US", 0.1, blacklists: ["vi*.yml"]);

		//$this->match_manager = new MatchManager($this);
		//$this->scoreboard = Scoreboard::getInstance();
		$this->yamlcomments = new YamlComments($this->getConfig());
	}

	public static function getInstance() : self{
		return self::$instance;
	}

	public function onEnable() : void{
		CmdManager::register($this);
		$this->getServer()->getCommandMap()->register("skywar", new SkywarCommand($this));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function getSkywarManagerUI() : ?MenuForm{
		return new MenuForm(
			$this->language_manager->getMessage("form.manager.title"),
			$this->language_manager->getMessage("form.manager.text"),
			[
				new MenuOption($this->language_manager->getMessage("form.manager.button-settings"), new FormIcon("textures/ui/settings_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language_manager->getMessage("form.manager.button-arenalist"), new FormIcon("textures/ui/storageIconColor.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language_manager->getMessage("form.manager.button-maplist"), new FormIcon("textures/ui/world_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language_manager->getMessage("form.manager.button-language"), new FormIcon("textures/ui/language_glyph_color.png", FormIcon::IMAGE_TYPE_PATH)),
			],
			function(Player $player, int $selected) : void{
				switch ($selected) {
					case 0:
						$player->sendForm($this->getArenaSettingsUI());
						break;
					case 1:
						break;
					case 2:
						$player->sendForm($this->getMapSettingsUI());
						break;
					case 3:
						$player->sendForm($this->getLanguageUI());
						break;
					default:
						$player->sendForm($this->getSkywarManagerUI());
				}
			}
		);
	}

	public function getMapSettingsUI() : ?MenuForm{
		return new MenuForm(
			$this->language_manager->getMessage("form.maps.title"),
			$this->language_manager->getMessage("form.maps.text"),
			[
				new MenuOption($this->language_manager->getMessage("form.button.back"), new FormIcon("textures/ui/arrow_left.png", FormIcon::IMAGE_TYPE_PATH))
			],
			function(Player $player, int $selected) : void{
				$player->sendForm($this->getSkywarManagerUI());
			}
		);
	}

	public function getArenaSettingsUI() : ?CustomForm{
		return new CustomForm(
			$this->language_manager->getMessage("form.settings.title"),
			[
				new Label("text", $this->language_manager->getMessage("form.settings.text")),
				new Input("countdown-time", $this->language_manager->getMessage("form.settings.input1"), "", (string) $this->match_manager->getDefaultCountdownTime()),
				new Input("opencage-time", $this->language_manager->getMessage("form.settings.input2"), "", (string) $this->match_manager->getDefaultOpencageTime()),
				new Input("game-time", $this->language_manager->getMessage("form.settings.input3"), "", (string) $this->match_manager->getDefaultGameTime()),
				new Input("restart-time", $this->language_manager->getMessage("form.settings.input4"), "", (string) $this->match_manager->getDefaultRestartTime()),
				new Input("force-time", $this->language_manager->getMessage("form.settings.input5"), "", (string) $this->match_manager->getDefaultForceTime()),
				new Input("arena-limit", $this->language_manager->getMessage("form.settings.input6"), "-1", (string) $this->match_manager->getArenaLimit()),
			],
			function(Player $submitter, CustomFormResponse $response) : void{
				$this->match_manager->setDefaultCountdownTime((int) ($response->getString("countdown-time") ?? $this->match_manager->getDefaultCountdownTime()));
				$this->match_manager->setDefaultOpencageTime((int) ($response->getString("opencage-time") ?? $this->match_manager->getDefaultOpencageTime()));
				$this->match_manager->setDefaultGameTime((int) ($response->getString("game-time") ?? $this->match_manager->getDefaultGameTime()));
				$this->match_manager->setDefaultRestartTime((int) ($response->getString("restart-time") ?? $this->match_manager->getDefaultRestartTime()));
				$this->match_manager->setDefaultForceTime((int) ($response->getString("force-time") ?? $this->match_manager->getDefaultForceTime()));
				$this->match_manager->setArenaLimit((int) ($response->getString("arena-limit") ?? $this->match_manager->getArenaLimit()));
			}
		);
	}

	public function getLanguageUI() : ?CustomForm{
		$languageKeys = array_keys($this->language_manager->getLanguageList());
		$language = $this->language_manager->getLanguage();
		return new CustomForm(
			$this->language_manager->getMessage("form.language.title"),
			[
				new Label("text", $this->language_manager->getMessage("form.language.text", ["{LANGUAGE_NAME}" => $language->getName(), "{LANGUAGE_ID}" => $language->getId()])),
				new Dropdown("language", $this->language_manager->getMessage("form.language.dropdown"), $languageKeys, array_search($this->language_manager->getCurrent(), $languageKeys, true))
			],
			function(Player $submitter, CustomFormResponse $response) use ($language, $languageKeys) : void{
				$new_lang_id = $languageKeys[$response->getInt("language")];
				$result = $this->language_manager->setLanguage($new_lang_id);
				if ($result) {
					$submitter->sendMessage($this->language_manager->getMessage(LanguageTag::LANGUAGE_SET,
						[
							"{LANGUAGE_NAME}" => $language->getName(),
							"{LANGUAGE_ID}" => $new_lang_id,
							"{LANGUAGE_VER}" => $language->getVersion()
						]
					));
				}
			}
		);
	}

	/**
	 * @throws \JsonException
	 */
	public function onDisable() : void{
		//$this->getConfig()->setNested("settings.time.countdown", $this->match_manager->getDefaultCountdownTime());
		//$this->getConfig()->setNested("settings.time.opencage", $this->match_manager->getDefaultOpencageTime());
		//$this->getConfig()->setNested("settings.time.game", $this->match_manager->getDefaultGameTime());
		//$this->getConfig()->setNested("settings.time.restart", $this->match_manager->getDefaultRestartTime());
		//$this->getConfig()->setNested("settings.time.force", $this->match_manager->getDefaultForceTime());
		//$this->getConfig()->setNested("settings-arena_limit", $this->match_manager->getArenaLimit());

		$this->getConfig()->set("language", $this->language_manager->getCurrent());

		$this->saveConfig();
		$this->yamlcomments->emitComments();
	}

	/**
	 * @return MatchManager|null
	 */
	public function getMatchManager() : ?MatchManager{
		return $this->match_manager;
	}

	/**
	 * @return LanguageManager
	 */
	public function getLanguageManager() : LanguageManager{
		return $this->language_manager;
	}
}

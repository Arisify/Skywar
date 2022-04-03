<?php
declare(strict_types=1);

namespace arie\skywar;

use arie\skywar\arena\ArenaManager;
use arie\skywar\language\LanguageManager;
use arie\skywar\scoreboard\Scoreboard;
use arie\yamlcomments\YamlComments;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

final class Skywar extends PluginBase implements Listener{
	private static Skywar $instance;

	private ArenaManager $arena_manager;
	private LanguageManager $language;
	private Scoreboard $scoreboard;
	private YamlComments $yamlcomments;

	public function onLoad() : void{
		self::$instance = $this;
		foreach ($this->getResources() as $resource) {
			$this->saveResource($resource->getFilename());
		}

		$this->language = new LanguageManager($this);
		$this->arena_manager = new ArenaManager($this);
		$this->scoreboard = Scoreboard::getInstance();
		$this->yamlcomments = new YamlComments($this->getConfig());
	}

	public static function getInstance() : self{
		return self::$instance;
	}

	public function onEnable() : void{
		$this->getLogger()->info(sprintf($this->language->getMessage('language.set'), $this->language->getLanguageName($this->language->getLanguageId()), $this->language->getLanguageId(), $this->language->getLanguageVersion()));
		$this->getServer()->getCommandMap()->register("skywars", new SkywarCommands($this));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function getSkywarManagerUI() : ?MenuForm{
		return new MenuForm(
			$this->language->getMessage("form.manager.title"),
			$this->language->getMessage("form.manager.text"),
			[
				new MenuOption($this->language->getMessage("form.manager.button-settings"), new FormIcon("textures/ui/settings_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-arenalist"), new FormIcon("textures/ui/storageIconColor.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-maplist"), new FormIcon("textures/ui/world_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-language"), new FormIcon("textures/ui/language_glyph_color.png", FormIcon::IMAGE_TYPE_PATH)),
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
			$this->language->getMessage("form.maps.title"),
			$this->language->getMessage("form.maps.text"),
			[
				new MenuOption($this->language->getMessage("form.button.back"), new FormIcon("textures/ui/arrow_left.png", FormIcon::IMAGE_TYPE_PATH))
			],
			function(Player $player, int $selected) : void{
				$player->sendForm($this->getSkywarManagerUI());
			}
		);
	}

	public function getArenaSettingsUI() : ?CustomForm{
		return new CustomForm(
			$this->language->getMessage("form.settings.title"),
			[
				new Label("text", $this->language->getMessage("form.settings.text")),
				new Input("countdown-time", $this->language->getMessage("form.settings.input1"), "", (string) $this->arena_manager->getDefaultCountdownTime()),
				new Input("opencage-time", $this->language->getMessage("form.settings.input2"), "", (string) $this->arena_manager->getDefaultOpencageTime()),
				new Input("game-time", $this->language->getMessage("form.settings.input3"), "", (string) $this->arena_manager->getDefaultGameTime()),
				new Input("restart-time", $this->language->getMessage("form.settings.input4"), "", (string) $this->arena_manager->getDefaultRestartTime()),
				new Input("force-time", $this->language->getMessage("form.settings.input5"), "", (string) $this->arena_manager->getDefaultForceTime()),
				new Input("arena-limit", $this->language->getMessage("form.settings.input6"), "-1", (string) $this->arena_manager->getArenaLimit()),
			],
			function(Player $submitter, CustomFormResponse $response) : void{
				$this->arena_manager->setDefaultCountdownTime((int) ($response->getString("countdown-time") ?? $this->arena_manager->getDefaultCountdownTime()));
				$this->arena_manager->setDefaultOpencageTime((int) ($response->getString("opencage-time") ?? $this->arena_manager->getDefaultOpencageTime()));
				$this->arena_manager->setDefaultGameTime((int) ($response->getString("game-time") ?? $this->arena_manager->getDefaultGameTime()));
				$this->arena_manager->setDefaultRestartTime((int) ($response->getString("restart-time") ?? $this->arena_manager->getDefaultRestartTime()));
				$this->arena_manager->setDefaultForceTime((int) ($response->getString("force-time") ?? $this->arena_manager->getDefaultForceTime()));
				$this->arena_manager->setArenaLimit((int) ($response->getString("arena-limit") ?? $this->arena_manager->getArenaLimit()));
			}
		);
	}

	public function getLanguageUI() : ?CustomForm{
		$languageKeys = array_keys($this->language->getLanguageList());
		return new CustomForm(
			$this->language->getMessage("form.language.title"),
			[
				new Label("text", $this->language->getMessage("form.language.text", ["{LANGUAGE_NAME}" => $this->language->getLanguageName($this->language->getLanguageId()), "{LANGUAGE_ID}" => $this->language->getLanguageId()])),
				new Dropdown("language", $this->language->getMessage("form.language.dropdown"), $languageKeys, array_search($this->language->getLanguageId(), $languageKeys, true))
			],
			function(Player $submitter, CustomFormResponse $response) use ($languageKeys) : void{
				$new_lang_id = $languageKeys[$response->getInt("language")];
				if ($this->language->getLanguageId() !== $new_lang_id) {
					$this->language->setLanguage($new_lang_id);
				}
			}
		);
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

	/**
	 * @throws \JsonException
	 */
	public function onDisable() : void{
		$this->getConfig()->setNested("settings.time.countdown", $this->arena_manager->getDefaultCountdownTime());
		$this->getConfig()->setNested("settings.time.opencage", $this->arena_manager->getDefaultOpencageTime());
		$this->getConfig()->setNested("settings.time.game", $this->arena_manager->getDefaultGameTime());
		$this->getConfig()->setNested("settings.time.restart", $this->arena_manager->getDefaultRestartTime());
		$this->getConfig()->setNested("settings.time.force", $this->arena_manager->getDefaultForceTime());
		$this->getConfig()->setNested("settings-arena_limit", $this->arena_manager->getArenaLimit());

		$this->getConfig()->setNested("language.default", $this->language->getLanguageId());

		$this->saveConfig();
		$this->yamlcomments->emitComments();
	}

	/**
	 * @return ArenaManager|null
	 */
	public function getArenaManager() : ?ArenaManager{
		return $this->arena_manager;
	}

	public function getLanguage() : ?LanguageManager{
		return $this->language;
	}
}

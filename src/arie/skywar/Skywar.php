<?php
declare(strict_types=1);

namespace arie\skywar;

use arie\yamlcomments\YamlComments;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use arie\skywar\arena\ArenaManager;
use arie\skywar\language\LanguageManager;
use arie\skywar\scoreboard\Scoreboard;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;

final class Skywar extends PluginBase implements Listener{
	protected static Skywar $instance;

	protected ArenaManager $arena_manager;
	protected LanguageManager $language;
	protected Scoreboard $scoreboard;
	private YamlComments $yamlcomments;

	public function onLoad() : void {
		self::$instance = $this;
		foreach ($this->getResources() as $resource) {
			$this->saveResource($resource->getFilename());
		}

		$this->language = new LanguageManager($this);
		$this->arena_manager = new ArenaManager($this);
		$this->scoreboard = Scoreboard::getInstance();
		$this->yamlcomments = new YamlComments($this->getConfig());
	}

	public static function getInstance() : self {
		return self::$instance;
	}

	public function onEnable() : void {
		$this->getLogger()->info("Nothing here!");
		$this->getServer()->getCommandMap()->register("skywars", new SkywarCommands($this));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function getArenaManagerUI() : ?MenuForm{
		return new MenuForm(
			$this->language->getMessage("form.manager.title"),
			$this->language->getMessage("form.manager.text"),
			[
				new MenuOption($this->language->getMessage("form.manager.button-settings"), new FormIcon("textures/ui/settings_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-arenalist"), new FormIcon("textures/ui/storageIconColor.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-maplist"), new FormIcon("textures/ui/world_glyph_color_2x.png", FormIcon::IMAGE_TYPE_PATH)),
				new MenuOption($this->language->getMessage("form.manager.button-language"), new FormIcon("textures/ui/language_glyph_color.png", FormIcon::IMAGE_TYPE_PATH))
			],
			function (Player $player, int $selected) : void {
				switch ($selected) {
					case 0:
						$player->sendForm($this->getArenaSettingsUI());
						break;
					case 1:
						//$player->sendForm();
						break;
					case 2:
						break;
					case 3:
						$player->sendForm($this->getLanguageUI());
						break;
				}
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
			],
			function(Player $submitter, CustomFormResponse $response) : void{
				$this->arena_manager->setDefaultCountdownTime((int) ($response->getString("countdown-time") ?? $this->arena_manager->getDefaultCountdownTime()));
				$this->arena_manager->setDefaultOpencageTime((int) ($response->getString("opencage-time") ?? $this->arena_manager->getDefaultOpencageTime()));
				$this->arena_manager->setDefaultGameTime((int) ($response->getString("game-time") ?? $this->arena_manager->getDefaultGameTime()));
				$this->arena_manager->setDefaultRestartTime((int) ($response->getString("restart-time") ?? $this->arena_manager->getDefaultRestartTime()));
				$this->arena_manager->setDefaultForceTime((int) ($response->getString("force-time") ?? $this->arena_manager->getDefaultForceTime()));
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
					$this->language->reMap($new_lang_id);
				}
			}
		);
	}

	public function onDisable() : void {
		$this->getConfig()->set("skywar.time-countdown", $this->arena_manager->getDefaultCountdownTime());
		$this->getConfig()->set("skywar.time-opencage", $this->arena_manager->getDefaultOpencageTime());
		$this->getConfig()->set("skywar.time-game", $this->arena_manager->getDefaultGameTime());
		$this->getConfig()->set("skywar.time-restart", $this->arena_manager->getDefaultRestartTime());
		$this->getConfig()->set("skywar.time-force", $this->arena_manager->getDefaultForceTime());

		$this->getConfig()->set("language", $this->language->getLanguageId());
		
		$this->saveConfig();
		$this->yamlcomments->saveDoc();
	}

	/**
	 * @return ArenaManager|null
	 */
	public function getArenaManager() : ?ArenaManager {
		return $this->arena_manager;
	}

	public function getLanguage() : ?LanguageManager {
		return $this->language;
	}
}

<?php
declare(strict_types=1);

namespace arie\skywar\language;

use arie\skywar\Skywar;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class LanguageManager{
	use SingletonTrait;

	protected array $messages;
	protected string $language_id = self::DEFAULT_LANGUAGE;
	protected array $languages = [];

	public const DEFAULT_LANGUAGE = "en-US";
	protected const SUPPORTED_LANGUAGES = [
		"en-US",
		"vi-VN"
	];

	private string $filePath;

	public function __construct(private Skywar $plugin){
		$this->filePath = $this->plugin->getDataFolder() . "langs" . DIRECTORY_SEPARATOR;

		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		foreach (self::SUPPORTED_LANGUAGES as $lang) {
			$this->plugin->saveResource("langs" . DIRECTORY_SEPARATOR . $lang . ".yml");
		}

		foreach (glob($this->filePath . "*.yml") as $lang) {
			$this->languages[basename($lang, ".yml")] = yaml_parse_file($lang)["LANG_NAME"] ?? "Unknown";
		}
		$this->reMap($this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE));
	}

	public function reMap(string $language_id = self::DEFAULT_LANGUAGE) : bool{
		if (!is_file($this->filePath . $language_id . ".yml")) {
			$this->plugin->getLogger()->notice("The language you are using ($language_id) is not exist, using default language!");
			$this->language_id = self::DEFAULT_LANGUAGE;
			return false;
		}
		$this->language_id = $language_id;

		if (!in_array($language_id, self::SUPPORTED_LANGUAGES, true)){
			$this->plugin->getLogger()->notice("The language you are using ($language_id) is not currently supported, which can cause translations to be missing.");
			$this->messages = array_map(static fn(string $message) : string => TextFormat::colorize($message), array_merge($this->getRawLanguageData($language_id), $this->getRawLanguageData()));
			return true;
		}
		$this->messages = array_map(static fn(string $message) : string => TextFormat::colorize($message), $this->getRawLanguageData($language_id));
		return true;
	}

	public function getMessage(string $message, array $replacements = []) : string{
		return isset($this->messages[$message]) ? strtr($this->messages[$message], $replacements) : $this->getRawLanguageData()[$message] ?? "";
	}

	/**
	 * @param string $language_id
	 * @return array|null
	 */
	public function getRawLanguageData(string $language_id = self::DEFAULT_LANGUAGE) : ?array{
		return yaml_parse_file($this->filePath . $language_id . ".yml");
	}



	/**
	 * @return Skywar
	 */
	public function getPlugin() : Skywar{
		return $this->plugin;
	}

	/**
	 * @return array
	 */
	public function getLanguageList() : array{
		return $this->languages;
	}

	/**
	 * @return string
	 */
	public function getLanguageId() : string{
		return $this->language_id;
	}

	public function getLanguageName(string $language_id = self::DEFAULT_LANGUAGE) : string{
		return $this->languages[$language_id] ?? "Unknown";
	}
}
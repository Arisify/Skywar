<?php
declare(strict_types=1);

namespace arie\skywar\language;

use arie\skywar\Skywar;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class LanguageManager{
	use SingletonTrait;

	protected array $messages;
	protected array $console_messages = [];
	protected string $language_id = self::DEFAULT_LANGUAGE;
	protected array $languages = [];

	public const DEFAULT_LANGUAGE = "en-US";
	protected const SUPPORTED_LANGUAGES = [
		"en-US",
		"vi-VN"
	];

	private string $filePath;
	private float $language_version;

	public function __construct(private Skywar $plugin){
		$this->filePath = $this->plugin->getDataFolder() . "langs/";
		$this->language_version = $this->plugin->getDescription()->getMap()["versions"]["language"];
		$resource = $this->plugin->getResource("langs/console.yml");
		$this->console_messages = yaml_parse(stream_get_contents($resource));
		fclose($resource);

		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf($this->getConsoleMessage('error.dir-not-found'), $concurrentDirectory));
		}
		foreach (self::SUPPORTED_LANGUAGES as $lang) {
			$this->plugin->saveResource("langs/" . $lang . ".yml");
		}

		foreach (glob($this->filePath . "*.yml") as $lang) {
			$this->languages[basename($lang, ".yml")] = yaml_parse_file($lang)["LANG_NAME"] ?? "Unknown";
		}
		$this->remap($this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE));
	}

	public function remap(string $language_id = self::DEFAULT_LANGUAGE) : bool{
		if (!is_file($this->filePath . $language_id . ".yml")) {
			$this->plugin->getLogger()->notice(sprintf($this->getConsoleMessage('language.not-exist'), $language_id, self::DEFAULT_LANGUAGE));
			$this->language_id = self::DEFAULT_LANGUAGE;
			return false;
		}
		$this->language_id = $language_id;

		if (!in_array($language_id, self::SUPPORTED_LANGUAGES, true)) {
			$this->plugin->getLogger()->notice(sprintf($this->getConsoleMessage('language.unknown'), $language_id));
			$this->messages = array_map(static fn(string $message) : string => TextFormat::colorize($message), array_merge($this->getRawLanguageData($language_id), $this->getRawLanguageData()));
		} else {
			$this->messages = array_map(static fn(string $message) : string => TextFormat::colorize($message), $this->getRawLanguageData($language_id));
		}
		$language_version = (string) ($this->messages["LANG_VERSION"] ?? "?");
		if ($language_version !== (string) $this->language_version) {
			$this->plugin->getLogger()->notice(sprintf($this->getConsoleMessage('language.outdated'), $language_version, $this->language_version));
		}
		return true;
	}

	public function getMessage(string $key, array $replacements = []) : string{
		if (!isset($this->messages[$key])) {
			$this->plugin->getLogger()->info(sprintf($this->getConsoleMessage("language.key-not-found"), $key, $this->language_id));
			return "";
		}
		return strtr($this->messages[$key], $replacements);
	}

	public function getConsoleMessage(string $key) : string{
		return $this->console_messages[$key] ?? "";
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
		return $this->languages[$language_id] ?? $this->getConsoleMessage('language.unknown');
	}

	public function getLanguageVersion() : float{
		return $this->language_version;
	}
}

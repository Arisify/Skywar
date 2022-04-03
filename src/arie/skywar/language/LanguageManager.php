<?php
declare(strict_types=1);

namespace arie\skywar\language;

use arie\skywar\Skywar;
use pocketmine\utils\TextFormat;

final class LanguageManager{
	protected string $language_id;
	protected array $language_list = [];
	protected array $raw_language;
	protected array $languages = [];

	public const DEFAULT_LANGUAGE = "en-US";
	protected const SUPPORTED_LANGUAGES = [
		"en-US",
		"vi-VN"
	];

	protected const LANGUAGE_INFO = [
		"LANG_VERSION" => -1.0,
		"LANG_NAME" => 'unknown',
	];

	private string $filePath;
	private float $language_version;

	public function __construct(private Skywar $plugin){
		$this->filePath = $this->plugin->getDataFolder() . "langs/";
		$this->language_version = $this->plugin->getDescription()->getMap()["versions"]["language"];
		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf($this->getMessage('error.dir-not-found'), $concurrentDirectory));
		}

		foreach (self::SUPPORTED_LANGUAGES as $lang) {
			$this->plugin->saveResource("langs/" . $lang . ".yml");
		}

		foreach (glob($this->filePath . "*.yml") as $lang) {
			$data = yaml_parse_file($lang);
			$id = basename($lang, '.yml');
			$language_name = $data["LANG_NAME"] ?? "unknown";
			$this->language_list[$id] = $language_name;
			$this->languages[$id] =  array_map(static fn(string $message) : string => TextFormat::colorize($message), array_diff_key($data, self::LANGUAGE_INFO));
		}
		$language_id = $this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE);
		if (!array_key_exists($language_id, $this->language_list)) {
			$this->plugin->getLogger()->notice(sprintf($this->getMessage('language.default-not-exist'), $language_id, self::DEFAULT_LANGUAGE));
			$language_id = self::DEFAULT_LANGUAGE;
		}
		$this->language_id = $language_id;
	}

	/*public function remap(string $language_id = self::DEFAULT_LANGUAGE) : bool{
		if (isset($this->language_id) && $language_id === $this->language_id) {
			$this->plugin->getLogger()->info(sprintf($this->getMessage('language.already-set'), $this->getLanguageName(), $language_id));
			return false;
		}
		if (!is_file($this->filePath . $language_id . ".yml")) {
			$this->plugin->getLogger()->notice(sprintf($this->getMessage('language.not-exist'), $language_id, $this->language_id ?? self::DEFAULT_LANGUAGE));
			$language_id = $this->language_id ?? self::DEFAULT_LANGUAGE;
		}
		$this->language_id = $language_id;
		$this->languages[$language_id] = yaml_parse_file($this->filePath . $language_id . ".yml"));
		$this->raw_messages ??= array_map(static fn(string $message) : string => TextFormat::colorize($message), $this->getRawLanguageData($language_id));

		$language_version = (float) ($this->messages["LANG_VERSION"] ?? -1);
		if ($language_version < $this->language_version) {
			$this->plugin->getLogger()->notice(sprintf($this->getMessage('language.outdated'), $language_version, $this->language_version));
		}
		return true;
	}*/

	public function getMessage(string $key, array $replacements = []) : ?string{
		if (!isset($this->messages[$key])) {
			if (!isset($this->raw_messages[$key])) {
				$this->plugin->getLogger()->info(sprintf($this->getMessage("language.key-not-found"), $key, $this->language_id));
				return null;
			}
			return empty($replacements) ? $this->raw_messages[$key] : strtr($this->raw_messages[$key], $replacements);
		}
		return empty($replacements) ? $this->messages[$key] : strtr($this->messages[$key], $replacements);
	}

	/**
	 * @param string $language_id
	 * @return array|null
	 */
	public function getRawLanguageData(string $language_id = self::DEFAULT_LANGUAGE) : ?array{
		if (!in_array($language_id, self::SUPPORTED_LANGUAGES)) {
			$language_id = self::DEFAULT_LANGUAGE;
		}
		$resource = $this->plugin->getResource("langs/$language_id.yml");
		if ($resource === null) {
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
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
		return $this->language_list;
	}

	/**
	 * @return string
	 */
	public function getLanguageId() : string{
		return $this->language_id;
	}

	public function getLanguageName(string $language_id = self::DEFAULT_LANGUAGE) : string{
		return $this->language_list[$language_id] ?? $this->raw_messages["language.unknown"] ?? "unknown";
	}

	public function getLanguageVersion() : float{
		return $this->language_version;
	}
}

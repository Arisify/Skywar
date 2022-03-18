<?php
declare(strict_types=1);

namespace arie\skywar\utils;

use arie\skywar\arena\ArenaManager;
use arie\skywar\language\LanguageManager;
use arie\skywar\Skywar;
use pocketmine\utils\Config;
use Webmozart\PathUtil\Path;

class ConfigModifier{
	private string $file;
	protected ArenaManager $arena_manager;
	protected LanguageManager $language;
	private Config $config;
	private array $doc = [];
	private array $inline_doc = [];
	private string $fileType;

	public function __construct(private Skywar $plugin){
		$this->file = Path::join($this->plugin->getDataFolder(), "config.yml");
		$this->fileType = strtolower(Path::getExtension($this->file));
		$this->config = $this->plugin->getConfig();
		$this->arena_manager = $this->plugin->getArenaManager();
		$this->language = $this->plugin->getLanguage();

		$this->loadDoc();
	}

	/**
	 * This function will scan the YAML file for comments and stuff then save it in two different array, one for
	 * documentations above it, one for documentations after it.
	 *
	 * Not recommended for production because lack of RAM eater (Note that this only takes a lot of CPU usage when the
	 * server turn on and off)
	 * @return void
	 */
	public function loadDoc() : void{
		if ($this->fileType === "yml") {
			$lines = file($this->file, FILE_IGNORE_NEW_LINES);
			$key = "";
			$spaces = [];
			$doc = [];
			foreach ($lines as $line) {
				if ($line === '...' || $line === '---') {
					continue;
				}
				$l = ltrim($line);
				$colon_pos = strpos($l, ':');
				if (!isset($l[0])) {
					$doc[] = "";
					continue;
				}
				if ($l[0] === '#') {
					$doc[] = $line;
					continue;
				}

				if ($colon_pos === false) {
					$val = str_replace([' ', '-'], '', $l);
					$sharp_pos = strpos($val, '#');
					if ($sharp_pos !== false) {
						$val = mb_substr($val, 0, $sharp_pos);
						$this->inline_doc[$key . "." . $val] = mb_substr($l, $sharp_pos);
					}

					if (!empty($doc)) {
						$this->doc[$key . "." . $val] = $doc;
						$doc = [];
					}
					continue;
				}
				$space = strlen($line) - strlen($l);

				if ($space === 0) {
					if ($line[0] !== '-') {
						$key = mb_substr($l, 0, $colon_pos);
					} else {
						$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
					}
				} else if ($spaces[$key] < $space) {
					$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
				} else {
					while($spaces[$key] >= $space) {
						$last_dotpos = strrpos($key, '.');
						if ($spaces[$key] === $space) {
							$key = mb_substr($key, 0, $last_dotpos);
							$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
							break; //This will stop the loop from checking for non-exist key...
						}
						$key = mb_substr($key, 0, $last_dotpos);
					}
				}

				$spaces[$key] = $space;

				if (!empty($doc)) {
					$this->doc[$key] = $doc;
					$doc = [];
				}
				$sharp_pos = strpos($l, '#');
				if ($sharp_pos !== false) {
					$this->inline_doc[$key] = mb_substr($l, $sharp_pos);
				}
			}
		}
	}

	public function save() : void{
		$this->config->set("skywar.time-countdown", $this->arena_manager->getDefaultCountdownTime());
		$this->config->set("skywar.time-opencage", $this->arena_manager->getDefaultOpencageTime());
		$this->config->set("skywar.time-game", $this->arena_manager->getDefaultGameTime());
		$this->config->set("skywar.time-restart", $this->arena_manager->getDefaultRestartTime());
		$this->config->set("skywar.time-force", $this->arena_manager->getDefaultForceTime());

		$this->config->set("language", $this->language->getLanguageId());
		$this->config->save();

		$this->saveDoc();
	}

	/**
	 * @param string $key
	 * @return array|null
	 */
	public function getDoc(string $key) : ?array{
		return $this->doc[$key] ?? null;
	}

	public function getDocPara(string $key) : ?string{
		return isset($this->doc[$key]) ? implode(PHP_EOL, $this->doc[$key]) : null;
	}

	public function setDoc(string $key, array $doc = []) : void{
		$this->doc[$key] = $doc;
	}

	public function addDoc(string $key, array $doc = []) : void{
		$this->doc[$key] = array_merge($this->doc[$key] ?? [], $doc);
	}

	/**
	 * @param string $key
	 * @return string|null
	 */
	public function getInlineDoc(string $key) : ?string{
		return $this->inline_doc[$key] ?? null;
	}

	public function setInlineDoc(string $key, string $doc) : void{
		$this->inline_doc[$key] = $doc;
	}

	public function isBlank(string $line) : bool{
		return preg_match('#^\s*$#', $line);
	}

	/**
	 * @return Skywar
	 */
	public function getPlugin() : Skywar{
		return $this->plugin;
	}

	/**
	 * This function will scan the YAML file again for keys and value, then check if there are comments of it in the data
	 * If yes, the data will be parsed with the key and value
	 *
	 * Not recommended for production because lack of RAM eater (Note that this only takes a lot of CPU usage when the
	 * server turn on and off)
	 * @return void
	 */
	private function saveDoc() : void{
		if ($this->fileType === "yml") {
			$lines = file($this->file, FILE_IGNORE_NEW_LINES);
			$key = "";
			$spaces = [];
			$contents = "";
			foreach ($lines as $line) {
				$l = ltrim($line);
				$colon_pos = strpos($l, ':');
				if (!isset($l[0])) {
					continue;
				}

				if ($colon_pos === false) {
					$val = str_replace([' ', '-'], '', $l);
					if (isset($this->doc[$key . "." . $val])) {
						$contents .= implode(PHP_EOL, $this->doc[$key . "." . $val]) . PHP_EOL;
					}
					$contents .= $line . ($this->inline_doc[$key . "." . $val] ?? "") . PHP_EOL;
					continue;
				}
				$space = strlen($line) - strlen($l);
				if ($space === 0) {
					if ($line[0] !== '-') {
						$key = mb_substr($l, 0, $colon_pos);
					} else {
						$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
					}
				} else if ($spaces[$key] < $space) {
					$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
				} else {
					while ($spaces[$key] >= $space) {
						$last_dotpos = strrpos($key, '.');
						if ($spaces[$key] === $space) {
							$key = mb_substr($key, 0, $last_dotpos);
							$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
							break; //This will stop the loop from checking for non-exist key...
						}
						$key = mb_substr($key, 0, $last_dotpos);
					}
				}
				$spaces[$key] = $space;

				if (isset($this->doc[$key])) {
					$contents .= $this->getDocPara($key) . PHP_EOL;
				}
				$contents .= $line . ($this->getInlineDoc($key) ?? "") . PHP_EOL;
			}
			file_put_contents($this->file, $contents);
		}
	}
}

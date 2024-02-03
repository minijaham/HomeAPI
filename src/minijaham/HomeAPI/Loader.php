<?php

declare(strict_types=1);

namespace minijaham\HomeAPI;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\SingletonTrait;

use poggit\libasynql\libasynql;
use poggit\libasynql\DataConnector;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

final class Loader extends PluginBase
{
	use SingletonTrait;

	private static DataConnector $database;
	private static HomeManager $homeManager;

	# Constants for database
	public const HOMES_INIT   = "homes.initialize";
	public const HOMES_SELECT = "homes.select";
	public const HOMES_CREATE = "homes.create";
	public const HOMES_DELETE = "homes.delete";

	public function onLoad() : void 
	{
		self::setInstance($this);
	}

	public function onEnable() : void
	{
		// Settings for libasynql. You can put this in your config as well, but I like to do this cuz fuck configs
		$settings = [
			"type" => "sqlite",
			"sqlite" => ["file" => "sqlite.sql"],
			"worker-limit" => 1
		];

		// These three lines are for initiating the database
		self::$database = libasynql::create(self::getInstance(), $settings, ["sqlite" => "sqlite.sql"]);
		self::$database->executeGeneric(self::HOMES_INIT);
		self::$database->waitAll();

		// Initialize HomeManager
		self::$homeManager = new HomeManager(self::getInstance());
	}

	public function onDisable() : void
	{
		if (isset(self::$database)) {
			self::$database->close();
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		if ($command->getName() === "home") {
			if (!isset($args[0])) {
				var_dump($this->getHomeManager()->getHomes());
				return true;
			}
			switch ($args[0]) {
				case "set":
					if (!isset($args[1])) {
						return false;
					}
					$this->getHomeManager()->createHome($sender, $args[1]);
					var_dump($this->getHomeManager()->getPlayerHome($sender->getUniqueId(), $args[1]));
					break;
				case "list":
					var_dump($this->getHomeManager()->getHomeList($sender->getUniqueId()));
					break;
				case "delete":
					if (!isset($args[1])) {
						return false;
					}
					$this->getHomeManager()->deleteHome($this->getHomeManager()->getPlayerHome($sender->getUniqueId(), $args[1]));
					var_dump($this->getHomeManager()->getPlayerHome($sender->getUniqueId(), $args[1]));
					var_dump($this->getHomeManager()->getHomeList($sender->getUniqueId()));
					break;
				default:
					if (($home = $this->getHomeManager()->getPlayerHome($sender->getUniqueId(), $args[0])) !== null) {
						$home->teleport($sender);
					}
			}
		}
		return true;
	}

	public static function getDatabase() : DataConnector
	{
		return self::$database;
	}

	public static function getHomeManager() : HomeManager 
	{
		return self::$homeManager;
	}
}

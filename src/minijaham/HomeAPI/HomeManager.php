<?php

declare(strict_types=1);

namespace minijaham\HomeAPI;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class HomeManager
{
	use SingletonTrait;

	/** Associative array with UUID as the first key and home name as the second */
	private array $homes = [];

	public function __construct(
		private Loader $plugin
	){
		self::setInstance($this);

		$this->loadHomes();
	}

	/**
	 * Store all home data in $homes property using hashmap structure
	 * 
	 * @return void
	 */
	private function loadHomes() : void
	{
		Loader::getDatabase()->executeSelect(Loader::HOMES_SELECT, [], function (array $rows) : void {
			foreach ($rows as $row) {
				$uuid = Uuid::fromString($row["uuid"])->toString();
				$home_name = $row["home_name"];

				# Store home using UUID and home name as keys
				$this->homes[$uuid][$home_name] = new Home(
					Uuid::fromString($row["uuid"]),
					$row["home_name"],
					$row["world_name"],
					$row["x"],
					$row["y"],
					$row["z"]
				);
			}
		});
	}

	/**
	 * Create a home
	 * 
	 * @param Player $player
	 * @param string $home_name
	 */
	public function createHome(Player $player, string $home_name) : void
	{
		$pos  = $player->getPosition();
		$args = [
			"uuid"       => $player->getUniqueId()->toString(),
			"home_name"  => $home_name,
			"world_name" => $player->getWorld()->getFolderName(),
			"x"          => $pos->getFloorX(),
			"y"          => $pos->getFloorY(),
			"z"          => $pos->getFloorZ()
		];

		Loader::getDatabase()->executeInsert(Loader::HOMES_CREATE, $args);
		
		# Store home using UUID and home name as keys
		$this->homes[$args["uuid"]][$home_name] = new Home(
			$player->getUniqueId(),
			$args["home_name"],
			$args["world_name"],
			$args["x"],
			$args["y"],
			$args["z"]
		);
	}

	/**
	 * Get home by UUID & Home Name
	 * 
	 * @param UuidInterface $uuid
	 * @param string $home_name
	 * @return Home|null
	 */
	public function getPlayerHome(UuidInterface $uuid, string $home_name) : ?Home
	{
		$uuidStr = $uuid->toString();

		# Directly access the home from the hashmap if it exists
		return $this->homes[$uuidStr][$home_name] ?? null;
	}

	/**
	 * Get Home List by UUID
	 * 
	 * @param UuidInterface $uuid
	 * @return array|null
	 */
	public function getHomeList(UuidInterface $uuid) : ?array
	{
		$uuidStr = $uuid->toString();

		# Return all homes for the UUID or null if none exist
		return $this->homes[$uuidStr] ?? null;
	}

	/**
	 * Delete a home
	 * 
	 * @param Home $home
	 * @return void
	 */
	public function deleteHome(Home $home) : void 
	{
		$uuidStr = $home->getUuid()->toString();
		$home_name = $home->getName();

		Loader::getDatabase()->executeChange(Loader::HOMES_DELETE, [
			"uuid" => $uuidStr,
			"home_name" => $home_name
		]);

		# Remove the home from the array if it exists
		if (isset($this->homes[$uuidStr][$home_name])) {
			unset($this->homes[$uuidStr][$home_name]);

			# Remove the UUID key if no more homes exist for the player
			if (empty($this->homes[$uuidStr])) {
				unset($this->homes[$uuidStr]);
			}
		}
	}

	/**
	 * Get a list of all homes registered on the server
	 * 
	 * @return array
	 */
	public function getHomes() : array
	{
		$allHomes = [];
		foreach ($this->homes as $playerHomes) {
			$allHomes = array_merge($allHomes, $playerHomes);
		}

		return $allHomes;
	}
}

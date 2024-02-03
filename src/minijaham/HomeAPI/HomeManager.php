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

	/** @var Home[] */
	private array $homes = [];

	public function __construct(
		private Loader $plugin
	){
		self::setInstance($this);

		$this->loadHomes();
	}

	/**
	 * Store all home data in $homes property
	 * 
	 * @return void
	 */
	private function loadHomes() : void
	{
		Loader::getDatabase()->executeSelect(Loader::HOMES_SELECT, [], function (array $rows) : void {
			foreach ($rows as $row) {
				$this->homes[] = new Home(
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
	 * Example: HomeManager::createSession($player, "homeName1")
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
		
		$this->homes[] = new Home(
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
		foreach ($this->homes as $home) {
			# If the UUID does not match, skip to the next one
			if (!$home->getUuid()->equals($uuid)) {
				continue;
			}
			# If the name does not match, skip to the next one
			if ($home->getName() !== $home_name) {
				continue;
			}
			return $home;
		}
		return null;

		/* 
		 * This is a test version using array_filter
		 * 
		$filteredHomes = array_filter($this->homes, function($home) use ($uuid, $home_name) {
			return $home->getUuid()->equals($uuid) && $home->getName() === $home_name;
		});
		
		return $filteredHomes ? reset($filteredHomes) : null;
		*/
	}

	/**
	 * Get Home List by UUID
	 * 
	 * @param UuidInterface $uuid
	 * @return array|null
	 */
	public function getHomeList(UuidInterface $uuid) : ?array
	{
		$fetched = [];
		foreach ($this->homes as $home) {
			# If the UUID does not match, skip to the next one
			if (!$home->getUuid()->equals($uuid)) {
				continue;
			}
			$fetched[] = $home;
		}

		# If the fetched array is empty, return null. Else, return fetched.
		return empty($fetched) ? null : $fetched;
	}

	/**
	 * Delete a home
	 * 
	 * @param Home $home
	 * @return void
	 */
	public function deleteHome(Home $home) : void 
	{
		Loader::getDatabase()->executeChange(Loader::HOMES_DELETE, [
			"uuid" => $home->getUuid()->toString(),
			"home_name" => $home->getName()
		]);

		unset($this->homes[array_search($home, $this->homes)]);
	}

	/**
	 * Get a list of all homes registered on the server
	 * 
	 * @return array
	 */
	public function getHomes() : array
	{
		return $this->homes;
	}
}
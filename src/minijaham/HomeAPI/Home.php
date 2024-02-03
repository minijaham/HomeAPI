<?php

declare(strict_types=1);

namespace minijaham\HomeAPI;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\world\Position;

use Ramsey\Uuid\UuidInterface;

final class Home
{
	public function __construct(
		private UuidInterface $uuid,
		private string $home_name,
		private string $world_name,
		private int $x,
		private int $y,
		private int $z
	){}

	/**
	 * Get UUID of the owner
	 * 
	 * @return UuidInterface
	 */
	public function getUuid() : UuidInterface
	{
		return $this->uuid;
	}

	/**
	 * This function gets the PocketMine player
	 * 
	 * @return Player|null
	 */
	public function getOwnerPlayer() : ?Player
	{
		return Server::getInstance()->getPlayerByUUID($this->uuid);
	}

	/**
	 * Get home's name
	 * 
	 * @return string
	 */
	public function getName() : string 
	{
		return $this->home_name;
	}

	/**
	 * Get the world of the home
	 * 
	 * @return World|null
	 */
	public function getWorld() : ?World 
	{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->world_name);
	}

	/**
	 * Get the position of the home
	 * 
	 * @return Position|null
	 */
	public function getPosition() : ?Position
	{
		return ($world = $this->getWorld()) === null ? null : (new Position($this->x, $this->y, $this->z, $world));
	}

	/**
	 * Utility function to teleport player directly from the home call
	 * 
	 * @param Player $player
	 * @throws \RuntimeException
	 * @return void
	 */
	public function teleport(Player $player) : void 
	{
		if (($pos = $this->getPosition()) === null) {
			throw new \RuntimeException("The target world is not available for teleport. Perhaps the world isn't loaded?");
		}
		$player->teleport($pos);
	}
}
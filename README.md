# HomeAPI
Simple Home API for PMMP using libasynql

# Usage
## HomeManager.php
```php
# You can call HomeManager instance either from the Loader (Loader::getHomeManager()), or HomeManager itself (HomeManager::getInstance())

# Create a new home
# Usage Example: HomeManager::createHome(Player, "home1")
public function createHome(Player $player, string $home_name) : void {}

# Get a player home
# Usage Example: HomeManager::createHome(UuidInterface, "home1")
public function getPlayerHome(UuidInterface $uuid, string $home_name) : ?Home {}

# Get all homes under a player
# Usage Example: HomeManager::createHome(UuidInterface)
public function getHomeList(UuidInterface $uuid) : ?array {}

# Delete a player's home
# Usage Example: HomeManager::deleteHome(HomeObj)
public function deleteHome(Home $home) : void {}

# Get all homes that are registered on the server
# Usage Example: HomeManager::getHomes() 
public function getHomes() : array {}
```

## Home.php
```php
# Get the owner's UuidInterface
public function getUuid() : UuidInterface {}

# Get the owner player instance (if present)
public function getOwnerPlayer() : ?Player {}

# Get the owner username
public function getName() : string {}

# Get the world where the home is located in
public function getWorld() : ?World {}

# Get the position of the home
public function getPosition() : ?Position {}

# Utility function to teleport player directly from the home instance
public function teleport(Player $player) : void {}
```

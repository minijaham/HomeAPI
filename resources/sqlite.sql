-- #!sqlite
-- # { homes
-- #  { initialize
CREATE TABLE IF NOT EXISTS homes (
	uuid VARCHAR(36),
	home_name VARCHAR(32),
	world_name VARCHAR(32),
	x INT,
	y INT,
	z INT,
	PRIMARY KEY (uuid, home_name)
);
-- #  }

-- #  { select
SELECT *
FROM homes;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :home_name string
-- #      :world_name string
-- #      :x int
-- #      :y int
-- #      :z int
INSERT OR REPLACE INTO homes(uuid, home_name, world_name, x, y, z)
VALUES (:uuid, :home_name, :world_name, :x, :y, :z);
-- #  }

-- #  { delete
-- #      :uuid string
-- #      :home_name string
DELETE FROM homes
WHERE uuid = :uuid AND home_name = :home_name;
-- #  }
-- # }
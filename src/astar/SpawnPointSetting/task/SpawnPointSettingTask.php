<?php

namespace astar\SpawnPointSetting\task;

use astar\SpawnPointSetting\SpawnPointSetting;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;

class SpawnPointSettingTask extends Task
{
    /**
     * @var SpawnPointSetting
     */
    private $plugin;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var Position
     */
	private $position;

	function __construct(SpawnPointSetting $plugin, Player $player, Position $position)
    {
        $this->plugin = $plugin;
		$this->player = $player;
		$this->position = $position;
	}
	public function onRun($currentTick)
    {
        $this->plugin->delayReSpawn($this->player, $this->position);
    }
}
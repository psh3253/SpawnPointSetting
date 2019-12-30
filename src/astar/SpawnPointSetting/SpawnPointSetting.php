<?php

namespace astar\SpawnPointSetting;

use astar\SpawnPointSetting\listener\EventListener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\PluginCommand;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class SpawnPointSetting extends PluginBase implements Listener
{
	/**
	 * @var array
	 */
    public $data = [];

	/**
	 * @var EventListener
	 */
    private $eventListener;

    public function onEnable()
    {

        $this->data = file_exists($this->getDataFolder() . "settings.yml") ? yaml_parse(file_get_contents($this->getDataFolder() . "settings.yml")) : ["spawns" => []];
		$this->eventListener = new EventListener($this);

        $this->registerCommand("스폰", "spawnpointsetting.spawn", "설정된 스폰지점으로 이동합니다", "/스폰");
        $this->registerCommand("스폰설정", "spawnpointsetting.setspawn", "스폰지점을 설정합니다.", "/스폰설정");
        $this->registerCommand("스폰초기화", "spawnpointsetting.spawnclear", "스폰지점을 초기화합니다.", "/스폰초기화");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable()
    {
        file_put_contents($this->getDataFolder() . "settings.yml", yaml_emit($this->data));
    }

    public function registerCommand($name, $permission, $description = "", $usage = "")
    {
        $commandMap = $this->getServer()->getCommandMap();
        $command = new PluginCommand ($name, $this);
        $command->setDescription($description);
        $command->setPermission($permission);
        $command->setUsage($usage);
        $commandMap->register($name, $command);
    }

    public function delayReSpawn(Player $player, Position $position)
    {
        $player->teleport($position);
    }

    public function getSpawn()
    {
        if (!isset ($this->data ["spawns"]) or count($this->data ["spawns"]) == 0)
            return null;
        $rand = mt_rand(0, count($this->data ["spawns"]) - 1);
        $epos = explode(":", $this->data ["spawns"] [$rand]);
        $level = $this->getServer()->getLevelByName($epos [5]);
        if (!$level instanceof Level)
            return null;
        return [
            new Position ((float)$epos [0], (float)$epos [1], (float)$epos [2], $level),
            $epos [3],
            $epos [4]
        ];
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		return $this->eventListener->onCommand($sender, $command, $label, $args);
	}
}

?>

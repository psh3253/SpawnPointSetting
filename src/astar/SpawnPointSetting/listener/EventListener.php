<?php

namespace astar\SpawnPointSetting\listener;

use astar\SpawnPointSetting\SpawnPointSetting;
use astar\SpawnPointSetting\task\SpawnPointSettingTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;

class EventListener implements Listener
{
    /**
     * @var SpawnPointSetting
     */
    private $plugin;

    public $spawn_queue = [];
    public $death_queue = [];

    public function __construct(SpawnPointSetting $plugin)
    {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onReSpawn(PlayerRespawnEvent $event)
    {
        if (isset ( $this->death_queue [$event->getPlayer ()->getName ()] )) {
            $pos = $this->plugin->getSpawn ();
            if ($pos != null)
                $this->plugin->getScheduler ()->scheduleDelayedTask ( new SpawnPointSettingTask ( $this->plugin, $event->getPlayer (), $pos [0] ), 20 );
            unset ( $this->death_queue [$event->getPlayer ()->getName ()] );
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        if (! isset ( $this->death_queue [$event->getEntity ()->getName ()] ))
            $this->death_queue [$event->getEntity ()->getName ()] = 1;
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        $defaultLevel = $this->plugin->getServer ()->getDefaultLevel ();
        if ($event->getPlayer ()->getLevel ()->getName () == $defaultLevel->getName ()) {
            if ($event->getPlayer ()->x == $defaultLevel->getSpawnLocation ()->x)
                if ($event->getPlayer ()->y == $defaultLevel->getSpawnLocation ()->y)
                    if ($event->getPlayer ()->z == $defaultLevel->getSpawnLocation ()->z)
                        return;
        }

        if (! isset ( $this->spawn_queue [$event->getPlayer ()->getName ()] )) {
            $this->spawn_queue [$event->getPlayer ()->getName ()] = 1;
            $pos = $this->plugin->getSpawn ();
            if ($pos != null)
                $event->getPlayer ()->teleport ( $pos [0], $pos [1], $pos [2] );
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§d[ §f스폰 §d] §f인게임 내에서만 가능합니다.");
            return true;
        }
        switch (strtolower($command->getName())) {
            case "스폰":
                $pos = $this->plugin->getSpawn();
                if ($pos != null) {
                    $sender->teleport($pos [0], $pos [1], $pos [2]);
                    $sender->sendMessage("§d[ §f스폰 §d] §f스폰지점으로 이동하였습니다.");
                    return true;
                } else {
                    $sender->sendMessage("§d[ §f스폰 §d] §f설정된 스폰지점이 없습니다.");
                    return true;
                }
                break;
            case "스폰설정":
                $this->plugin->data ["spawns"] [] = $sender->x . ":" . $sender->y . ":" . $sender->z . ":" . $sender->yaw . ":" . $sender->pitch . ":" . $sender->getLevel()->getFolderName();
                $sender->sendMessage("§d[ §f스폰 §d] §f스폰지점이 설정되었습니다.");
                return true;
                break;
            case "스폰초기화":
                $this->plugin->data ["spawns"] = [];
                $sender->sendMessage("§d[ §f스폰 §d] §f스폰지점이 초기화되었습니다.");
                return true;
                break;
        }
        return true;
    }
}
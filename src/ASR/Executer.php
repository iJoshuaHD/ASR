<?php

namespace ASR;

use pocketmine\plugin\Plugin;

use pocketmine\Server;

use pocketmine\scheduler\PluginTask;

class Executer extends PluginTask
{
	private $secs;
	
	public function __construct(ASR $plugin)
	{
		parent::__construct($plugin);

		$this->plugin = $plugin;
		$this->secs = $this->plugin->getConfiguration()["remaining-seconds"];
		$this->fixed = $this->plugin->getConfiguration()["fixed-minutes"];
		$this->fixed_min = "fixed-minutes";
		$this->remaining_min = "remaining-minutes";
		$this->remaining_sec = "remaining-seconds";
		$this->secs_fixed = $this->fixed * 60;
		
	}
	
	public function getServer()
	{
		return Server::getInstance();
	}
	
	public function onRun($currentTick)
	{

		$this->secs--;
		$this->minutes_notfixed = $this->secs / 60;
		$this->secs_notfixed = $this->secs;
		
		$this->plugin->getConfig()->set($this->remaining_min, $this->minutes_notfixed);
		$this->plugin->getConfig()->set($this->remaining_sec, $this->secs_notfixed);
		$this->plugin->getConfig()->save();
		$this->plugin->getConfig()->getAll();

		
		if($this->secs > 60 and $this->secs % 60 == 0)
		{
			$this->getServer()->broadcastMessage("[ASR] Server will restart in " . $this->minutes_notfixed . " minutes.");
		}
		else if($this->secs == 60)
		{
			$this->getServer()->broadcastMessage("[ASR] Server will restart in " . $this->minutes_notfixed . " minute.");
		}
		else if($this->secs == 30)
		{
			$this->getServer()->broadcastMessage("[ASR] Server will restart in " . $this->secs . " seconds.");
		}
		else if($this->secs == 10)
		{
			$this->getServer()->broadcastMessage("[ASR] Server will restart in " . $this->secs . " seconds.");
		}
		else if($this->secs <= 5)
		{
			$this->getServer()->broadcastMessage("[ASR] Server will now restart in " . $this->secs . "!");
			
			if($this->secs == 0)
			{
				$this->getServer()->broadcastMessage("[ASR] Please wait... Server will now restart.");
				
				foreach($this->getServer()->getOnlinePlayers() as $player)
				{
					$player->kick("Server restart");
				}
				
				$this->getServer()->shutdown();
			}
		}
	}
}
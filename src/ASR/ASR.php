<?php

namespace ASR;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class ASR extends PluginBase{
	
	private $config;
	
    public function onEnable()
	{
		$this->getLogger()->info("Loading ASR ...");
		$this->loadConfiguration();
		
		$this->fixed = $this->getConfiguration()["fixed-minutes"];
		
		$this->fixed_min = "fixed-minutes";
		$this->remaining_min = "remaining-minutes";
		$this->remaining_sec = "remaining-seconds";
		$this->remaining_sec_fixed = $this->fixed * 60;
		
		$this->getConfig()->set($this->fixed_min, $this->fixed);
		$this->getConfig()->set($this->remaining_min, $this->fixed);
		$this->getConfig()->set($this->remaining_sec, $this->remaining_sec_fixed);
		$this->getConfig()->save();
		$this->getConfig()->getAll();

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Executer($this), 20);
		$this->getLogger()->info(TextFormat::BLUE ."ASR Enabled!");
	}
	
	public function getConfiguration()
	{
		return $this->config;
	}
	
	public function loadConfiguration()
	{
		if(!(file_exists($this->getDataFolder() . "config.yml")))
		{
			$this->saveDefaultConfig();
		}

		$this->config = $this->getConfig()->getAll();
	}
	
    public function onDisable()
	{
		$this->getConfig()->set($this->fixed_min, $this->fixed);
		$this->getConfig()->set($this->remaining_min, $this->fixed);
		$this->getConfig()->set($this->remaining_sec, $this->remaining_sec_fixed);
		$this->getConfig()->save();
		$this->getConfig()->getAll();
    }
} 
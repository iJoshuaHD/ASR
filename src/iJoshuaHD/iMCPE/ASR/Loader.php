<?php

/*
==================

ASR Plugin by
iJoshuaHD

==================
*/

namespace iJoshuaHD\iMCPE\ASR;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{

	public $count_down = 60; //secs
	public $time_count = array();

    public function onEnable(){
		//Commands
		$this->getCommand("asr")->setExecutor(new Commands($this));
		$this->getCommand("restart")->setExecutor(new Commands($this));
		//Task
		$this->initial_start(2); //its obviously 1 sec but idk why xD
		//Load Config
		$this->loadConfigurations();
	}
    
    public function onDisable(){
		$this->db->closeDatabase();
    }
	
	/***************************
	*==========================*
	*====[ External APIs ]=====*
	*==========================*
	***************************/
	
	public function pluginDisable(){
		return $this->getServer()->getPluginManager()->disablePlugin($this);
	}
	
	public function getDb(){
		return $this->db;
	}
	
	public function setValueTimer($value){
		$this->preferences->set("TimeToRestart", $value);
		$this->preferences->save();
	}
	
	public function getTimer(){
		if(isset($this->time_count['time'])){
			return $this->time_count['time'];
		}else{
			$this->setTimer($this->restart_time, "mins.");
			return $this->time_count['time'];
		}
	}
	
	public function setTimer($time, $offset){
		if(isset($this->time_count['time'])){
			unset($this->time_count['time']);
			$this->time_count['time'] = "$time $offset";
		}else{
			$this->time_count['time'] = "$time $offset";
		}
	}

	/*************************
	*========================*
	*====[ Plugin APIs ]=====*
	*========================*
	*************************/
	
	public function initial_start($timer){
	/*
	 The Reason of this function is to set an allowance on the main timer not to start once the plugin is enabled.
	*/
		if($timer == 1){
			$this->start($this->restart_time + 1);
			return;
		}else{
			$timer--;
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"initial_start" ], [$timer]), 20);
		}
	}
	
	public function start($time_target){
		$time_target--;
		if($time_target == 1) $offset = "min.";
		else $offset = "mins.";
		$this->broadcast("Server will restart in $time_target $offset");
		if($time_target == 1){
			$this->count_down($this->count_down + 1);
			return;
		}
		$this->setTimer($time_target, $offset);
		if($time_target < $this->restart_time){
			$this->db->updateTimestamp();
		}
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"start" ], [$time_target]), 1200);
	}
	
	public function count_down($seconds){
		if($seconds == 1){
			foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->kick("Server Restart");
			}
			$this->getServer()->shutdown();
			return;
		}else{
			$seconds--;
			$this->setTimer($seconds, "secs.");
			if($seconds == 30) $this->broadcast("Server will restart in $seconds seconds.");
			if($seconds == 10) $this->broadcast("Server will restart in $seconds seconds.");
			if($seconds < 6) $this->broadcast("Server will restart in $seconds.");
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"count_down" ], [$seconds]), 20);
		}
	}
	
	/************************
	*=======================*
	*====[ Non - APIs ]=====*
	*=======================*
	************************/
	
	public function broadcast($msg){
		return $this->getServer()->broadcastMessage($this->prefix . " $msg");
	}
	
	public function loadConfigurations(){
		if(!file_exists($this->getDataFolder())){
			$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] It Seems you're new in using ASR.");
			$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] Applying Configurations [...]");
			@mkdir($this->getDataFolder(), 0777, true);
			$this->preferences = new Config($this->getDataFolder() . "config.yml", Config::YAML);
			$this->preferences->set("Version", "2.0.1");
			$this->preferences->set("TimeToRestart", 30);
			$this->preferences->set("Prefix", "[ASR]");
			$this->preferences->set("Logger_DB", false);
			$this->preferences->save();
			$this->getServer()->getLogger()->info(TextFormat::AQUA . "[ASR] Note: Logger is disabled by default.");
			$this->getServer()->getLogger()->info(TextFormat::BLUE . "[ASR] You can Enable it by editing the config.yml");
			$this->getServer()->getLogger()->info(TextFormat::AQUA . "[ASR] If you have enabled Logger, please");
			$this->getServer()->getLogger()->info(TextFormat::BLUE . "[ASR] use the proper MySQL Server info otherwise plugin wont work.");
			$this->getServer()->getLogger()->info(TextFormat::GREEN . "[ASR] Done!");
		}else{
		/*	This would be useful when I make some further updates e.g. Multi Lingual Support. 
			If you are worrying about if there's version 3.0.0 or more, don't worry, I'll deal
			with it :)	*/
			$this->preferences = new Config($this->getDataFolder() . "config.yml", Config::YAML);
			$version = $this->preferences->get("Version");
			$checker = $this->preferences->get("Logger_DB");
			if($version !== "2.0.1" and $version == "2.0.0"){
				$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] It Seems you're using v$version of ASR.");
				$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] Applying Configuration Updates for v2.0.1 [...]");
				$this->preferences->set("Version", "2.0.1");
				$this->preferences->set("Logger_DB", false);
				$this->preferences->save();
				$this->getServer()->getLogger()->info(TextFormat::GREEN . "[ASR] Done!");
			}else{
				if($version !== "2.0.1" and $version !== "2.0.0"){
					$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] It Seems you're using an older version of ASR.");
					$this->getServer()->getLogger()->info(TextFormat::YELLOW . "[ASR] Applying Configuration Updates [...]");
					$this->preferences->set("Version", "2.0.1");
					$this->preferences->set("TimeToRestart", 30);
					$this->preferences->set("Prefix", "[ASR]");
					$this->preferences->set("Logger_DB", false);
					$this->preferences->save();
					$this->getServer()->getLogger()->info(TextFormat::GREEN . "[ASR] Done!");
				}
			}
			if($checker == true){
				$this->getServer()->getLogger()->info(TextFormat::BLUE . "[ASR] You have Logger ENABLED, please use");
				$this->getServer()->getLogger()->info(TextFormat::AQUA . "[ASR] the proper MySQL Server info otherwise plugin wont work.");
			}else{
				$this->getServer()->getLogger()->info(TextFormat::RED . "[ASR] Logger is DISABLED.");
			}
		}
		if(!file_exists($this->getDataFolder() . "connector.yml")){
			$this->saveResource("connector.yml");
		}
		$this->cfg = new Config($this->getDataFolder() . "connector.yml", Config::YAML);
		$this->restart_time = $this->preferences->get("TimeToRestart");
		$this->prefix = $this->preferences->get("Prefix");
		$this->db = new Database($this); //connection with database
		$this->db->loadDatabase();
	}
}

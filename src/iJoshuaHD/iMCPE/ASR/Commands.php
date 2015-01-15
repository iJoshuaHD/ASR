<?php

namespace iJoshuaHD\iMCPE\ASR;

use pocketmine\Player;
use pocketmine\IPlayer;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\utils\TextFormat;

class Commands implements CommandExecutor{

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch(strtolower($command->getName())){
		
			case "asr":
				if(isset($args[0])){
					if(!is_numeric($args[0])){
						$sender->sendMessage("[ASR] Only Numbers is prohibited.");
						return;
					}
					if($args[0] > 60){
						$sender->sendMessage("[ASR] It's not advised the value would be more than 60. If you want to increase it, edit the config.yml instead as this plugin won't allow you to set the value more than the said value because it's not prescribed.");
						$sender->sendMessage("[ASR] Only Numbers is prohibited.");
						return;
					}
					$this->plugin->setValueTimer($args[0]);
					$sender->sendMessage("[ASR] You have set the timer to " . $args[0] . " min/s. The changes will apply after the next server restart.");
				}else{
					$sender->sendMessage("Usage: /asr <value>");
				}
			break;
		
			case "restart":
				$time = $this->plugin->getTimer();
				$sender->sendMessage("[ASR] The server will restart in $time");
			break;
		
		}
		
	}

}

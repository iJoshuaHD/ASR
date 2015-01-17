<?php

namespace iJoshuaHD\iMCPE\ASR;

use pocketmine\utils\TextFormat;

use iJoshuaHD\iMCPE\ASR\Loader;

class Database{

	private $db;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
	}
	
	public function loadDatabase(){
		if($this->plugin->preferences->get("Logger_DB") == true){
			$mysql = $this->plugin->cfg->get("MySQL Details");
			$mysql_hostname = $mysql["host"];
			$mysql_port = $mysql["port"];
			$mysql_user = $mysql["user"];
			$mysql_password = $mysql["password"];
			$mysql_database = $mysql["database"];
		
			$this->db = @new \mysqli($mysql_hostname, $mysql_user, $mysql_password, $mysql_database, $mysql_port);
			$this->db_check = @fsockopen($mysql_hostname, $mysql_port, $errno, $errstr, 5);
			
			$this->plugin->getLogger()->info("Connecting to MySQL Database ...");

			if (!$this->db_check){
				$this->plugin->getLogger()->critical("Cant find MySQL Server running.");
				$this->plugin->getLogger()->info("Disabling the plugin...");
				$this->plugin->pluginDisable();
				fclose($this->db_check);
			}
			else{
				if($this->db->connect_error){
					$this->plugin->getLogger()->critical($this->db->connect_error);
					$this->plugin->getLogger()->info("Disabling the plugin...");
					$this->plugin->pluginDisable();
					fclose($this->db_check);
				}else{
					$this->plugin->getLogger()->info(TextFormat::BLUE ."MySQL Status: " . TextFormat::GREEN . "Connected!");
					
					/* Creating Dependencies if not exist */
			
					$exists_table_asr = $this->db->query("SELECT * FROM asr_logger LIMIT 0");

					if(!$exists_table_asr){
						$this->plugin->getLogger()->critical("ASR Logger table doesnt exist.");
						$this->plugin->getLogger()->info("Generating table ...");
						$sql_123 = "CREATE TABLE IF NOT EXISTS asr_logger(
									port		VARCHAR(5) NOT NULL,
									processid	VARCHAR(30) NOT NULL,
									timestamp	INT(11) NOT NULL
									) ENGINE=INNODB;";
						$res = $this->db->query($sql_123);
						if($res){
							$this->plugin->getLogger()->info(TextFormat::YELLOW ."Successfully created \"asr_logger\" table!");
						}
					}
					$port = $this->plugin->getServer()->getPort();
					$processid = getmypid();
					$result = $this->db->query("SELECT port FROM asr_logger WHERE port='$port'")->fetch_assoc();
					$current = intval(time());
					if(!$result){
						$this->plugin->getLogger()->info(TextFormat::YELLOW ."Adding new processID for PORT: $port ...");
						$temp = $this->db->query("INSERT INTO asr_logger(port, processid, timestamp)VALUES('$port','$processid','$current')");
						if($temp) $this->plugin->getLogger()->info(TextFormat::GREEN ."SUCCESS!");
					}else{
						$this->plugin->getLogger()->info(TextFormat::YELLOW ."Updating new processID for PORT: $port ...");
						$temp = $this->db->query("UPDATE asr_logger SET processid='$processid', timestamp='$current' WHERE port='$port'");
						if($temp) $this->plugin->getLogger()->info(TextFormat::GREEN ."SUCCESS!");
					}
					
					/* Creating Dependencies if not exist */
					
				}
			}
			
		}
	}
	
	public function updateTimestamp(){
		if($this->plugin->preferences->get("Logger_DB") == true){
			$port = $this->plugin->getServer()->getPort();
			$current = intval(time());
			$temp = $this->db->query("UPDATE asr_logger SET timestamp='$current' WHERE port='$port'");
			if($temp) $this->plugin->getLogger()->info(TextFormat::GREEN ."Timestamp has been Updated.");
		}
	}
	
	public function closeDatabase(){
		if($this->plugin->preferences->get("Logger_DB") == true){
			if ($this->db_check){
				if(!$this->db->connect_error) $this->db->close();
			}
		}	
	}
	
	public function getQueryAndInsertID($query){
		if($value = $this->db->query($query)) return $this->db->insert_id;
	}

	public function getQuery($query){
		$result = $this->db->query($query);
		if(!$result){
			return false;
		}else{
			return true;
		}
	}
	
	public function getFetchAssoc($query){
		$result = $this->db->query($query);
		if(!$result){
			return false;
		}else{
			return $result->fetch_assoc();
		}
	}
	
	public function getFetchRow($query){
		$result = $this->db->query($query);
		if(!$result){
			return false;
		}else{
			return $result->fetch_row();
		}
	}
	
	public function getFetchArray($query){
		$result = $this->db->query($query);
		if(!$result){
			return false;
		}else{
			return $result->fetch_array();
		}
	}
	
	public function countNumRows($query){
		$result = $this->db->query($query);
		return $result->num_rows;
	}
}
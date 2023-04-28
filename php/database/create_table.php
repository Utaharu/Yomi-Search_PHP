<?php
/*
	Class - Create_Table
		# Yomi-Search PHP - Databse Table Set
		# - Create Table SQL
*/

class Create_Table{
	private $DB;
	
	function __construct($DataBase_Object){
		$this->DB = $DataBase_Object;
	}
	
	function Category(){
		$sql = "CREATE TABLE {prefix}category 
			(
				id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title VARCHAR(255) BINARY,
				up_id INT UNSIGNED,
				path VARCHAR(100),
				top_list CHAR(1),
				etc_list CHAR(1),
				regist CHAR(1),
				reffer VARCHAR(100),
				sort_name VARCHAR(255) BINARY,
				comment VARCHAR(255) BINARY
			)";
		return $this->DB->query($sql);
	}
	
	function Common_Config(){
		$sql = "CREATE TABLE {prefix}cfg 
			(
				name VARCHAR(20),
				value VARCHAR(255)
			)";		
		return $this->DB->query($sql);
	}
	
	function Register_Config(){
		$sql = "CREATE TABLE {prefix}cfg_reg 
			(
				name VARCHAR(20),
				value VARCHAR(255)
			)";
		return $this->DB->query($sql);
	}
	
	function Counter(){
		$sql = "CREATE TABLE {prefix}counter 
			(
				counter INT UNSIGNED
			)";
		return $this->DB->query($sql);
	}
	
	function Counter_Log(){
		$sql = "CREATE TABLE {prefix}counter_log 
			(
				ip VARCHAR(40),
				time BIGINT UNSIGNED
			)";
		return $this->DB->query($sql);
	}
	
	function Key(){
		$sql = "CREATE TABLE {prefix}key 
			(
				word VARCHAR(50),
				time INT UNSIGNED,
				ip VARCHAR(40)
			)";
		return $this->DB->query($sql);
	}
	
	function Key_Rank(){
		$sql = "CREATE TABLE {prefix}key_rank 
			(
				word VARCHAR(255) BINARY,
				open_key CHAR(1),
				bad_key CHAR(1),
				view_word VARCHAR(255) BINARY
			)";
		return $this->DB->query($sql);
	}
	
	function Log(){
		$sql = "CREATE TABLE {prefix}log 
			(
				id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title VARCHAR(255) BINARY,
				url VARCHAR(100),
				mark VARCHAR(19),
				last_time VARCHAR(21),
				passwd VARCHAR(13),
				message BLOB,
				comment BLOB,
				name VARCHAR(255) BINARY,
				mail VARCHAR(100),
				category VARCHAR(100),
				stamp INT UNSIGNED,
				banner VARCHAR(100),
				renew TINYINT UNSIGNED,
				ip VARCHAR(40),
				keywd VARCHAR(255) BINARY
			)";
		return $this->DB->query($sql);
	}
	
	function Log_Temp(){
		$sql = "CREATE TABLE {prefix}log_temp 
			(
				id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title VARCHAR(255) BINARY,
				url VARCHAR(100),
				mark VARCHAR(19),
				last_time VARCHAR(21),
				passwd VARCHAR(13),
				message BLOB,
				comment BLOB,
				name VARCHAR(255) BINARY,
				mail VARCHAR(100),
				category VARCHAR(100),
				stamp BIGINT UNSIGNED,
				banner VARCHAR(100),
				renew TINYINT UNSIGNED,
				ip VARCHAR(40),
				keywd VARCHAR(255) BINARY
			)";
		return $this->DB->query($sql);
	}
	
	function Rank(){
		$sql = "CREATE TABLE {prefix}rank 
			(
				id INT UNSIGNED,
				time INT UNSIGNED,
				ip VARCHAR(40)
			)";
		return $this->DB->query($sql);
	}
	
	function Rank_Counter(){
		$sql = "CREATE TABLE {prefix}rank_counter 
			(
				id INT UNSIGNED,
				rank INT UNSIGNED,
				rev INT UNSIGNED
			)";
		return $this->DB->query($sql);
	}
	
	function Report(){
		$sql = "CREATE TABLE {prefix}report 
			(
				id INT UNSIGNED,
				ip VARCHAR(40),
				no_url CHAR (1),
				move CHAR (1),
				no_banner CHAR (1),
				violation CHAR (1),
				other CHAR (1),
				comment VARCHAR(255) BINARY,
				name VARCHAR(100) BINARY,
				email VARCHAR(100) BINARY
			)";
		return $this->DB->query($sql);
	}
	
	function Rev(){
		$sql = "CREATE TABLE {prefix}rev 
		(
			id INT UNSIGNED,
			time INT UNSIGNED,
			ip VARCHAR(40)
		)";
		return $this->DB->query($sql);
	}
	
	function Text(){
		$sql = "CREATE TABLE {prefix}text 
			(
				name VARCHAR(20),
				value BLOB
			)";
		return $this->DB->query($sql);
	}
	
}

?>
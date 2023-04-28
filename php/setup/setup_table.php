<?php
/*
	Class - Setup_Table
		# Yomi-Search PHP - Database New Install
		# - Create Table & Default Config Setup
		# ------------------------------------#
		# Require
		# - DB
		# - Create_Table
		# - functions
		# ------------------------------------#
		# Data Base - Table Name List
		# - category
		# - cfg
		# - cfg_reg
		# - counter
		# - counter_log
		# - key
		# - key_rank
		# - log
		# - log_temp
		# - rank
		# - rank_counter
		# - reort
		# - rev
		# - text
		# ------------------------------------#
*/

class Setup_Table{
	/* Property */
	var $DB;
	var $Create_Table;
	var $Table_List = array();
	private $Install_Config = array();
	
	function __construct(){
		global $Setup_Config;
		/* # Setting Property # */
		$this->DB = new DB_Connecter($db_set['host'],$db_set['user'],$db_set['password'],$db_set['name'],$db_set['prefix'],$db_set['charset']);
		$this->Create_Table = new Create_Table($this->DB);
		//Get - Table Name List
		$this->Table_List = $this->DB->list_tables();
		//Setup config
		if(isset($Setup_Config)){
			$this->Install_Config = $Setup_Config;
		}else{
			print "ERROR - Not Loaded Setup_Config (". __CLASS__ . " : " . __LINE__ . ")";
			exit;
		}
	}

	/* # Check - $Table_List 
	 $Table = Not included prefix
	*/
	function Check_TableList($Table){
		$table_name = "{$this->DB->prefix}{$Table}";
		if(!isset($this->Table_List[$table_name])){
			return False;
		}else{
			return True;
		}
	}
	//Inclose SQL Value(array_map - callback)
	function Inclose_Value($value){
		return "'" . $value . "'";
	}
	
	function Category(){
		if(!$this->Check_TableList("category")) {
			//Create - Table
			$this->Create_Table->Category();
			//Sort - Category Config
			ksort($this->Install_Config['category'],SORT_STRING);
			
			//Category ID List
			$category_index = array_keys($this->Install_Config['category']);
			array_unshift($category_index,"");
			//reffer List
			$reffer_index = array_column($this->Install_Config['category'],"3");
			array_unshift($reffer_index,"");
			
			foreach($this->Install_Config['category'] as $key => $value){
				//Category Setting - 整形
				$insert = Array("id"=>"","title"=>"","up_id"=>0,"path"=>"","top_list"=>1,"etc_list"=>0,"regist"=>0,"reffer"=>"","sort_name"=>"","comment"=>"");
				$insert['id'] = array_search($key,$category_index,true);
				if($insert['id'] === False or !is_numeric($insert['id'])){$insert['id'] = "";}
				$insert['path'] = str_replace('_', '/', $key)."/";
				//Get - up_id(Parent - Category ID)
				if (stristr($key, '_')) {
					$parent_path = substr($key, 0, strrpos($key, '_'));
					$insert['up_id'] = array_search($parent_path,$category_index);
				}
				if($insert['up_id'] === False or !is_numeric($insert['up_id'])){
					$insert['up_id'] = 0;
				}
				//Search - reffer
				$reffer_list = preg_grep("/(?<!_)". $key . "(?!_)/",$reffer_index);
				if(is_array($reffer_list)){
					$reffer_ids = array_keys($reffer_list);
					$reffer_ids = preg_replace("/([0-9]+)/",'&$1&',$reffer_ids);
					$insert['reffer'] = implode("",$reffer_ids);
				}
				if(isset($value[0])){$insert['title'] = $this->DB->escape_string($value[0]);}				
				if(isset($value[1])){$insert['sort_name'] = $this->DB->escape_string($value[1]);}
				if(isset($value[2])){$insert['comment'] = $this->DB->escape_string($value[2]);}
				if(isset($value[4]) and is_numeric($value[4])){
					$insert['regist'] = $value[4];
				}else{
					$insert['regist'] = 0;
				}
				if(isset($value[5]) and is_numeric($value[5])){
					$insert['top_list'] = $value[5];
				}else{
					$insert['top_list'] = 0;
				}
				if(isset($value[6]) and is_numeric($value[6])){
					$insert['etc_list'] = $value[6];
				}else{
					$insert['etc_list'] = 0;
				}
				//SQL
				$insert = array_map(array($this,'Inclose_Value'),$insert);
				$insert = implode(",",$insert);
				$sql = "INSERT INTO {prefix}category VALUES({$insert});";
				$this->DB->query($sql);
			}

			return $this->Result_Message("category",True);
		}else{
			return $this->Result_Message("category",False);
		}
	}
	
	function Cfg(){
		if(!$this->Check_TableList("cfg")) {
			$this->CreateTable->Common_Config();
			
			//Auto Input - Default Url
			if(empty($this->Install_Config['common']['cgi_path_url'])){
				$this->Install_Config['common']['cgi_path_url'] = $this->Get_ServerSchema() . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
			}
			if(empty($this->Install_Config['common']['home'])){
				// 検索エンジンのURL設定 (例：http://localhost/yomi/yomi.php)
				$this->Install_Config['common']['home'] = $this->Get_ServerSchema() . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
				if(!empty($this->Install_Config['common']['script'])){
					$this->Install_Config['common']['home'] .= $this->Install_Config['common']['script'];
 				}
			}
			
			// cfgテーブルに初期データをインサート
			foreach($this->Install_Config['common'] as $key=>$val) {
				$key = $this->DB->escape_string($key);
				$val = $this->DB->escape_string($val);
				//Password Encryption
				if($key == "pass" and $val){$val = Password_Encryption($val);}			
				$sql = "INSERT INTO {prefix}cfg VALUES('{$key}', '{$val}')";
				$this->DB->query($sql);
			}
			foreach($this->Install_Config['description'] as $key=>$val) {
				if(preg_match("/^\d{2}/", $key)) continue;
				if($key == 'rank') $key = 'rank_ys';
				$key = $this->DB->escape_string($key);
				$val = $this->DB->escape_string($val);
				$sql = "INSERT INTO {prefix}cfg VALUES('{$key}', '{$val}')";
				$this->DB->query($sql);
			}
			return $this->Result_Message("cfg",True);
		}else{
			return $this->Result_Message("cfg",False);
		}
	}
		
	function Cfg_Reg(){
		if(!$this->Check_TableList("cfg_reg")) {
			$this->CreateTable->Register_Config();
			foreach ($this->Install_Config['register'] as $key=>$val) {
				$key = $this->DB->escape_string($key);
				$val = $this->DB->escape_string($val);
				$sql = "INSERT INTO {prefix}cfg_reg VALUES('{$key}', '{$val}')";
				$this->DB->query($sql);
			}
			return $this->Result_Message("cfg_reg",True);
		}else{
			return $this->Result_Message("cfg_reg",False);
		}			
	}
	
	function Counter(){
		if(!$this->Check_TableList("counter")) {
			$this->CreateTable->Counter();
			// counterテーブルに初期データをインサート
			$sql = "INSERT INTO {prefix}counter VALUES('0')";
			$this->DB->query($sql);
			return $this->Result_Message("counter",True);
		}else{
			return $this->Result_Message("counter",False);
		}			
	}
	
	function Counter_Log(){
		if(!$this->Check_TableList("counter_log")){
			$this->CreateTable->Counter_Log();
			return $this->Result_Message("counter_log",True);
		}else{
			return $this->Result_Message("counter_log",False);
		}
	}
	
	function Key(){
		if(!$this->Check_TableList("key")){
			$this->CreateTable->Key();
			return $this->Result_Message("key",True);
		}else{
			return $this->Result_Message("key",False);
		}
	}
	
	function Key_Rank(){
		if(!$this->Check_TableList("key_rank")) {
			$this->CreateTable->Key_Rank();
			return $this->Result_Message("key_rank",True);
		}else{
			return $this->Result_Message("key_rank",False);
		}
	}
	
	function Log(){
		if(!$this->Check_TableList("rank")) {
			$this->CreateTable->Log();
			return $this->Result_Message("log",True);
		}else{
			return $this->Result_Message("log",False);
		}
	}
	
	function Log_Temp(){
		if(!$this->Check_TableList("log_temp")) {
			$this->CreateTable->Log_Temp();
			return $this->Result_Message("log_temp",True);
		}else{
			return $this->Result_Message("log_temp",False);
		}
	}
	
	function Rank(){
		if(!$this->Check_TableList("rank")) {
			$this->CreateTable->Rank();
			return $this->Result_Message("rank",True);
		}else{
			return $this->Result_Message("rank",False);
		}
	}
	
	function Rank_Counter(){
		if(!$this->Check_TableList("rank_counter")) {
			$this->CreateTable->Rank_Counter();
			return $this->Result_Message("rank_counter",True);
		}else{
			return $this->Result_Message("rank_counter",False);
		}
	}
	
	function Report(){
		if(!$this->Check_TableList("report")){
			$this->CreateTable->Report();
			return $this->Result_Message("report",True);
		}else{
			return $this->Result_Message("report",False);
		}
	}
	
	function Rev(){
		if(!$this->Check_TableList("rev")){
			$this->CreateTable->Rev();
			return $this->Result_Message("rev",True);
		}else{
			return $this->Result_Message("rev",False);
		}
	}
	
	function Text(){
		if(!$this->Check_TableList("text")){
			$this->CreateTable->Text();
			$this->Install_Config['search_form']= $this->DB->escape_string($this->Install_Config['search_form']);
			$sql = "INSERT INTO {prefix}text VALUES('search_form', '" . $this->Install_Config['search_form'] . "')";
			$this->DB->query($sql);
			$this->Install_Config['menu_bar'] = $this->DB->escape_string($this->Install_Config['menu_bar']);
			$sql = "INSERT INTO {prefix}text VALUES('menu_bar', '" . $this->Install_Config['menu_bar'] . "')";
			$this->DB->query($sql);
			$sql = "INSERT INTO {prefix}text VALUES('head_sp', '')";
			$this->DB->query($sql);
			$sql = "INSERT INTO {prefix}text VALUES('foot_sp', '')";
			$this->DB->query($sql);
			return $this->Result_Message("text",True);
		}else{
			return $this->Result_Message("text",False);
		}
	}
	
	protected function Result_Message($Table,$Flag){
		if(!isset($this->Table_List["{$this->DB->prefix}{$Table}"])) {
			return "<div>データベース {$this->DB->name} にテーブル {$this->DB->prefix}{$Table} を作成しました。</div>";
		}else{
			return "<div>テーブル {$this->DB->prefix}{$Table} は作成済みです！</div>";
		}
	}
	
	private function Get_ServerSchema(){
		$scheme = "";
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
			$scheme = 'https';
		}else{
			$scheme = 'http';
		}
		return $scheme;
	}
	
}
?>
<?php

class Get_Settings{
	var $DB;
	static $Common = array();
	static $Register = array();
	static $Description = array();
	
	function __construct($DataBase_Object){
		$this->DB = $DataBase_Object;
		$this->$Common = $this->CommonCfg();
		$this->Register = $this->RegisterCfg();
		$this->Description = $this->DescriptionCfg();
	}
	
	function CommonCfg(){
		// cfgテーブルから設定情報を配列($Settings->common)へ読込
		$sql = 'SELECT name, value FROM {prefix}cfg';
		$this->DB->rowset_assoc($sql);
		$common = array();
		$common = array_column($this->DB->row,'value','name');
		if(!isset($common['sp_path_url']) or !$common['sp_path_url']){$common['sp_path_url'] = "./";}
		if(!isset($common['sp_search_name']) or !$common['sp_search_name']){$common['sp_search_name'] = $common['search_name'];}
		if(!isset($common['sp_script']) or !$common['sp_script']){$common['sp_script'] = $common['sp_path_url'];}
		if(!isset($common['sp_home']) or  !$common['sp_home']){$common['sp_home'] = $common['sp_path_url'];}
		if(!isset($common['sp_temp_path']) or !$common['sp_temp_path']){$common['sp_temp_path'] = $path_set['template']['root'];}
		if(!isset($common['sp_sub_path']) or !$common['sp_sub_path']){$common['sp_sub_path'] = "../" . $common['sub_path'];}
		if(!isset($common['sp_img_path']) or !$common['sp_img_path']){$common['sp_img_path'] = "../" . $common['img_path_url'];}
		
		return $common;
	}
	
	function RegisterCfg(){
		$sql = 'SELECT name, value FROM {prefix}cfg_reg';
		$this->DB->rowset_assoc($sql);
		$register = array();
		$register = array_column($this->DB->row,'value','name');
		return $register;
	}
	
	function DescriptionCfg(){
		$sql = 'SELECT name, value FROM {prefix}text';
		$this->DB->rowset_assoc($sql);
		$description = array();
		$description = array_column($this->DB->row,'value','name');
		return $description;
	}
	
}

?>
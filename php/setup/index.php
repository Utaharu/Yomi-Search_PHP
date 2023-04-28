<?php
require $script['lib']['db']['main'];
require_once $script['lib']['db']['get_setting'];

$db = new DB_Connecter($db_set['host'],$db_set['user'],$db_set['password'],$db_set['name'],$db_set['prefix'],$db_set['charset']);
$Get_Config = new Get_Settings($db);
$cfg = $Get_Config->Common;


// 表示用メッセージ初期化
$mes = "";
if(isset($Form['mode'])){
	
	if($Form['mode'] == 'install'){
		// #### Install - DataBase Table  ##### //
		// @ Get - Install Config (Require - Config File) @ //
		
		// @ Making - DB Table @ //
		require_once $script['lib']['setup']['table'];
		require_once $script['lib']['db']['create'];
		$Setup_Table = new Setup_Table();
		$mes .= $Setup_Table->Category();
		$mes .= $Setup_Table->Cfg();
		$mes .= $Setup_Table->Cfg_Reg();
		$mes .= $Setup_Table->Counter();
		$mes .= $Setup_Table->Counter_Log();
		$mes .= $Setup_Table->Key();
		$mes .= $Setup_Table->Key_Rank();
		$mes .= $Setup_Table->Log();
		$mes .= $Setup_Table->Log_Temp();
		$mes .= $Setup_Table->Rank();
		$mes .= $Setup_Table->Rank_Counter();
		$mes .= $Setup_Table->Report();
		$mes .= $Setup_Table->Rev();
		$mes .= $Setup_Table->Text();
		// @ Print Result Screen @ //
		require_once($Setup_Config['template']['install']);
		exit;
	
	}elseif($Form['mode'] == 'initialsetting'){
		// #### 初期設定画面を表示 #### //
		// @ Get - Config (DB) @ //
		require_once $script['lib']['setup']['initial'];
		$Initial_Setup= new Initial_Setup($db);
		// @ Print Initial Settings Screen @ //
		require_once($Setup_Config['template']['settings']);
		exit;
		
	}elseif($Form['mode'] == 'finish'){
		// #### 初期設定反映 #### //
		require_once $sctipt['setup']['initial'];
		$Initial_Setup = new Initial_Setup($db);
		// @ Initial Settings - Value Check @ //
		$mes = $Initial_Setup->Check_Value();
		// @ Update DB - Initial Settings @ //
		if(empty($mes)) {
			$Initial_Setup->Set_EngineName();
			$Initial_Setup->Set_EngineUrl();
			$Initial_Setup->Set_PAssword();
			$Initial_Setup->Set_Admin();
			$Initial_Setup->Set_Ranking();
			$Initial_Setup->Set_Mylink();
			
			// @ Print Compleate Screen @ //
			$cfg = $Get_Config->CommonCfg();
			require_once($Setup_Config['template']['compleate']);
			exit;
		} else {
			require_once($Setup_Config['template']['error']);
			exit;
		}

	}
  
	echo 'error!';
	exit;

// トップページ表示
} else {
	require_once($Setup_Config['template']['start']);
	exit;
}
?>
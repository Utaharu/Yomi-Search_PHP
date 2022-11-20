<?php
/* ###### ipv4 -> ipv6 DataBase Converter ######
ip記録領域をipv6対応出来るよう拡張するスクリプト。
*既存のデータベースがある場合に使用する。
*ip VARCHAR(15) を MODIFY ip VARCHAR(40)化するもの

*/

class DB_Check{
	var $table_list = array();
	var $ipv6_result = array();
	
	function __construct(){
		global $db;
		if(is_object($db)){
			$this->table_list = $db->list_tables();
		}
	}
	
	function Check_Ipv6_Column(){
		global $db;
		$this->ipv6_result = array('list'=>array(),'flag'=>True);
		
		if(is_array($this->table_list)){
			foreach ($this->table_list as $table_name){
				//ipv6の長さが保存できるか(Varchara 40)
				//[ipv6'][$table_name] => Null = ipカラムを含まない, 0 = ipカラムがあるが、未拡張でipv6が登録できない。, 1 = ipカラムがあり、拡張済みで、ipv6も登録可能。
				$this->ipv6_result['list'][$table_name] = Null;
				//ipフィールドの存在を確認。		
				$db->query("SHOW columns from ".$table_name . " WHERE FIELD = 'ip'");
				if(isset($db->result->num_rows) and $db->result->num_rows > 0){
					//Table内にipフィールドがある場合
					$ip_v6_flag = false;
					//Typeを確認。
					if($db->result){
						foreach($db->result as $line){
							if(isset($line['Type'])){
								//サイズを確認
								if(preg_match("/varchar\(([0-9]+)\)/i",$line['Type'],$cfg_ip_num)){
									//40以上に拡張済み
									if(isset($cfg_ip_num[1]) and $cfg_ip_num[1] >= 40){$ip_v6_flag = True;}
								}
							}
						}
					}
					if($ip_v6_flag){$this->ipv6_result['list'][$table_name] = True;}
					else{
						$this->ipv6_result['list'][$table_name] = False;
						$this->ipv6_result['flag'] = False;
					}
				}else{
					//Ipフィールドが存在しないテーブル
					$this->ipv6_result['list'][$table_name] = Null;
				}
			}
		}		
	}

	function Change_Ipv6_Column(){
		global $db;
		if(!isset($this->ipv6_result['list'])){
			$this->Check_Ipv6_Column();
		}
		
		pass_check();
		
		$status = "";
		if(isset($this->ipv6_result['list']) and is_array($this->ipv6_result['list'])){
			$status = "<p>------ 処理結果 ------</p>\n";
			$status .= "<ul>\n";
			foreach($this->ipv6_result['list'] as $table_name => $flag){
				if(!is_null($flag)){
					$status .= "	<li style=\"margin:5px 0px;\">".$table_name."\n";
					if(!$flag){
						$sql = "ALTER TABLE " . $table_name ." MODIFY ip VARCHAR(40)";
						$db->query($sql);
						$status .= "		<div style=\"line-height:20px; padding-left:5px;\">ipカラムを拡張しました。</div>\n";
					}else{$status .= "		<div style=\"line-height:20px; padding-left:5px;\">No Change</div>\n";}
					$status.= "	</li>\n";
				}
			}
			$status .= "</ul>\n";
		}		
		mes("ipカラムの拡張が完了しました。\n".$status, '拡張完了', 'kanri');
	}
}

?>
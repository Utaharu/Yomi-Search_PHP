<?php
if(!isset($_POST['mode']) || $_POST['mode'] == 'kanri') {
	$selected_00 = '';
	$selected_01 = ' selected';
} else {
	$selected_00 = ' selected';
	$selected_01 = '';
}

// 管理室用リストボックス生成
$admin_listbox = <<< LIST
      <input type="hidden" name="pass" value="{$_POST['pass']}">
	  <select name="mode" class="form">
        <option value="kanri"{$selected_00}>00. 管理室トップへ戻る</option>
        <option value="temp_to_regist"{$selected_01}>01. 登録待ち({$count_temp}件)</option>
        <option value="config">02. 環境設定</option>
        <option value="cfg_reg">03. 環境設定(登録処理関連)</option>
        <option value="cfg_marks">04. 特殊カテゴリ(マーク項目)設定</option>
        <option value="cfg_html">05. HTML設定</option>
        <option value="config_kt">06. カテゴリ設定</option>
        <option value="look_mes">07. 登録者のメッセージを見る</option>
        <option value="log_kt_change">08. ログデータの交換・移動・削除</option>
        <option value="log_repair">09. ログ（登録データ）のバックアップ・復元</option>
        <option value="rank_cfg">10. 人気ランキング・アクセスランキングの設定</option>
        <option value="key_cfg">11. キーワードランキングの設定</option>
        <option value="mylink_cfg">12. マイリンクの設定</option>
        <option value="log_conv">13. 各種ログ変換 </option>
        <option value="dl_check">14. デッドリンクチェック </option>
        <option value="cfg_admin_pass">15. 管理者パスワード変更</option>
        <option value="ver_info">16. バージョン情報</option>
      </select>
      <input name="Submit" type="submit" class="form" value="移動">
LIST;
?>
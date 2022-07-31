----------------------------------------------------------------------
                                                   2016 年 09 月 02 日

                   Yomi-Search(PHP)modified Ver1.5.8.n2.002

目次
====

01. 利用規約など
02. Yomi-Search(PHP)modifiedとは
02-1. Yomi-Search(PHP)modified.nとは
03. インストール方法
04. 旧バージョンからのアップデート方法
05. デバッグモードについて
06. 関連リンク
07. バージョンアップ履歴

----------------------------------------------------------------------


01. 利用規約など
================

本スクリプトはYomi-SearchPHP化プロジェクトで配布されている
Yomi-Search(PHP)Ver4.19.5をベースに、多くの改造を施した改造版
Yomi-Search(PHP)です。

したがって、利用規約はYomi-SearchPHP化プロジェクトの規約に準ずるものと
します。



02. Yomi-Search(PHP)modifiedとは
================================

WonderLinkのyomi氏によって開発されたYomi-Searchという検索エンジンスク
リプトがあります。これが非常に高機能かつ使いやすいということで、多くの
サイト管理者が自サイトにYomi-Searchを導入しました。

後に、このYomi-SearchをPHPで書き直したYomi-Search(PHP)が登場します。こ
のPHP版ではデータの保存にMySQLデータベースを使用し、高速な検索を実現し
ています。

本スクリプト[Yomi-Search(PHP)modified]は、上記Yomi-Search(PHP)の最新版
であるVer4.19.5をベースに、機能追加やバグ除去などの大幅な改造を施した
検索エンジンスクリプトです。


02-1. Yomi-Search(PHP)modified.nとは
================================
上記のYomi-Search(PHP)modified1.5.8
http://yomiphp-mod.sweet82.com/
を少々カスタマイズしたものです


03. インストール方法
====================
※Yomi-Search(PHP)modified1.5.8と同じです。


03-01. データベースに関する設定
-------------------------------
class/db.phpを開き、4行目～10行目のデータベースに関する設定を行ってく
ださい。

＜注意 2010/10/15追加＞
データベースで$db_pre +「key」というテーブル名を使用しています。そのため
$db_preを空文字に設定するとテーブル名が「key」のみとなり
keyはMySQLの予約語なのでそのままテーブル名とするとエラーとなってしまいます。
http://dev.mysql.com/doc/refman/5.1/ja/reserved-words.html
そのため、$db_preには半角スペース、半角記号以外の半角文字を1文字以上設定してください。


03-02. ファイルのアップロードとディレクトリの権限設定
-----------------------------------------------------
ReadMe.txtとChangeLog.txt以外のファイルをサーバへアップロード後、logデ
ィレクトリのパーミッション(アクセス権限)を777、logディレクトリ内の
look_mes.cgiとpass_check.cgiのパーミッションを666に設定。

03-03. セットアップスクリプトの実行
-----------------------------------
ブラウザから http://設置したURL/setup/setup.php にアクセスすることでイ
ンストールプログラムを実行します。後は、画面の指示に従ってインストール
を完了させてください。

03-04. setupディレクトリの削除
------------------------------
インストールが完了したら、まずサーバ上のsetupディレクトリ以下を削除し
てください。これらのファイルが残ったままになっていると、悪意ある第三者
に検索エンジンの管理者パスワードを変更されてしまう可能性があります。


03-05. 広告タグについて
------------------------------
/ads.phpにてヘッダフッタ、右サイド部分に広告タグを入れることができます。

例えばads.phpの
    //=============================//
    //ヘッダー部分の広告タグ設定
    //=============================//
    $HEADER_ADS_ARRAY[0] = <<<EOF
EOF;

の<<<EOF
の行とEOF;の行の間に、広告タグを入れてください。

    $HEADER_ADS_ARRAY[0] = <<<EOF
<!-- Rakuten Dynamicad FROM HERE -->
<script type="text/javascript">
<!--
rakuten_template = "s_728_90_txt";
rakuten_affiliateId = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
rakuten_service = "ichiba";
rakuten_target = "_blank";
rakuten_color_bg = "FFFFFF";
rakuten_color_border = "CCCCCC";
rakuten_color_text = "000000";
rakuten_color_link = "0000FF";
rakuten_color_price = "CC0000";
//--></script>
<script type="text/javascript"
  src="http://dynamic.rakuten.co.jp/js/rakuten_dynamic.js">
</script>
<!-- Rakuten Dynamicad TO HERE -->

EOF;

こんな感じです。
RIGHT,FOOTERも同様に追加してください。
右に表示する場合は
function getRightAdFlg()
{
    return FALSE;
}
を
function getRightAdFlg()
{
    return TRUE;
}
にしてください。


04. 旧バージョンからのアップデート方法
==============================================
Yomi-Search(PHP)modified1.5.8 > Yomi-Search(PHP)modified1.5.8.n2
-----------------------------
基本的には全てのファイルを上書きするだけでOKです。

外部検索エンジンにbingを追加しましたので、
$db->db_pre.'text'テーブルの
nameカラム=search_form
のvalueの
<option value="google">Google</option>
の下に
<option value="bing">bing</option>
を付け加えてください。
※この項目のoptionタグには</option>が付いていないと思われますので
こちらも付けてください。

valueカラムの値は

<option value="pre" selected>ご自身のサイト名</option>
<option value="yahoo">Yahoo!</option>
<option value="google">Google</option>
<option value="bing">bing</option>
<option value="infoseek">Infoseek</option>
<option value="goo">goo</option>
<option value="excite">Excite</option>
<option value="FRESHEYE">フレッシュアイ</option>
<option value="">-----------------</option>
<option value="vector">Vector</option>
<option value="">-----------------</option>
<option value="rakuten">楽天市場</option>
<option value="hmv_a">HMV(アーティスト名検索)</option>
<option value="hmv_t">HMV(タイトル名検索)</option>
<option value="bk1">bk1</option>
<option value="">-----------------</option>
<option value="com">.com</option>
<option value="cojp">.co.jp</option>
<option value="jp">.jp で</option>
<option value="net">.netで</option>
<option value="info">.infoで</option>
<option value="org">.orgで</option>
<option value="movi">.moviで</option>
こんな感じになります。



05. デバッグモードについて
==========================

改造を施す場合など、NOTICEを含む全てのエラーを表示させたい場合には
config4debug.phpを1箇所変更するだけでデバッグモードに移行できます。

この場合には、5行目の値を1に変更して上書きアップロードしてください。


06. 関連リンク
==============

■WonderLink
http://yomi.pekori.to/
本家Yomi-Searchの開発元サイト。検索エンジン以外にも多くのスクリプトが
フリーで配布されています。

■Yomi-Search PHP化プロジェクト
http://sql.s28.xrea.com/
Yomi-Search(PHP)の開発元です。2005年8月2日以降、開発が停止しています。

■Yomi-Search(PHP) Support Forum
http://yomi.php.jp-search.net/
Yomi-Search(PHP)に関するフォーラムです。設置やバグに関する情報が数多く
蓄積されています。

■Yomi-Search(PHP)modified
http://yomiphp-mod.sweet82.com/
本スクリプト[Yomi-Search(PHP)modified]の開発元。



07. バージョンアップ履歴
========================

2016/09/02   var.n2.003
・class/db.phpのDB周りをmysql_xxから、mysqli_xxに変更。
旧db.phpは_db.phpとして残してあります。

2011/11/06   var.n2.001
・バグ修正です。。

2011/1/16   var.n2
------------------------
・n1で細かすぎて置き換えていなかった部分の”記述を'にして微妙に高速化+使用メモリ本当に微妙に減少
・細かいバグ修正
・/php/meta_ys.phpのドメイン検索のところ(.co.jp, .com部分)をGoogleで検索するように変更
・smartphone版を作成。(jquery mobile alpha2を使用) http://jquerymobile.com/
※jquery mobileがアルファ版なため随時更新する予定です。
・一応、携帯版とスマートフォン版を追加しました。

2010/10/15  n1
------------------------
・ver n1 配布開始。
Yomi-Search(PHP)modified1.5.8に

・全体的に”を'にして微妙に高速化+使用メモリ微妙に減少
・FORMの&lt;/option&gt;を省略しているため、一部ブラウザで表示されないので追加
・db.phpのエラーをdebug_backtraceで出力するように変更(PHP4.3以上のみ)
・外部検索エンジンにbingを追加
・一部POST、GETの値を直でクエリに使用していたのをチェックするように処理追加
・HTML表示部にMETAタグ(keyword,description)追加
・mysql_list_tablesを廃止
・できるだけSELECT COUNT(*)を使わないように変更
・できるだけSELECT * FROM を使わないように変更
・functions.phpにおいてglobal変数を見直していらない所は呼ばないよう変更
・登録サイト表示時のメモリ使用量が約10KB減
・各ページに広告を表示する/ads.phpを追加(初期設定は何も広告を表示しません)
・一部echo文を連発していた部分を変数に入れて最後にまとめてechoするように変更
・パンくずリスト(ホーム＞ショッピング＞ショッピングモール）を下段にも追加

の処理を施しました

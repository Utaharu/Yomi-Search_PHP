# Yomi-Search PHP

<br/>

> ## このプログラムのバージョン履歴について
現在: Yomi-Search PHP Ver:8.0β -<br/>
4 :　(PHP版: Ver.6 - 7) [Yomi-Search(PHP)modified ver1.5.8.n2_1.1](http://www.nkbt.net/yomi/) - nkbt版<br/>
3　:　(PHP版: Ver.5 - 6) [Yomi-Search(PHP)modified ver1.5.8](http://yomiphp-mod.sweet82.com/) - Yomi-Search(PHP)modified版<br/>
2　:　(PHP版: Ver.4 - 5) [Yomi-Search(PHP) Ver4.19.5](http://sql.s28.xrea.com/) - Yomi-Search(PHP)版<br/>
1　:　(CGI版: Ver.0 - 4) Yomi-Search [WonderLink](http://yomi.pekori.to/) / [Vector](https://www.vector.co.jp/soft/unix/net/se124310.html) - CGI版<br/>

<br/>

> ## やっていくこと。
 各種修正や、php8対応など

>> ### 要検討
* smartphoneページ
* mobileページ

>> #### 拡張
- [ ] PHP8対応
- - [ ] pc (/)
- - [ ] smartphone (/s/)
- - [ ] mobile (/m/)

- [x] IPV6対応
- - [x] 新規設置(setup)
- - [x] 既存設置からipカラムを拡張する(管理者ページ->環境設定)

- [ ] [登録カテゴリー・セレクトボックスのmultiple化](https://github.com/Utaharu/Yomi-Search_PHP/issues/3)
- - [x] pc
- - [ ] smartphone (/s/)
- - [ ] mobile (/m/)

>> #### 修正
- [x] [rank.php、XSSオープンリダイレクトの脆弱性](https://github.com/Utaharu/Yomi-Search_PHP/issues/2)
- - [x] pc
- - [x] smartphone (/s/)
- - [x] mobile (/m/)
- [x] キーワードの表示/非表示設定が反映されない
- [x] 登録データのバックアップ・復元のバックアップデータの作成が行われない。
- [x] キーワードランキングの集計対象外のキーワードを一括登録が機能してない。

<br/>

> ## 更新履歴
-2022/11/25-
* 「登録するカテゴリのセレクトボックスをmultiple化」の再修正。([nkbt-37@1811202326](https://github.com/Utaharu/Yomi-Search_PHP/issues/3)) <br/>
　multiple設定の場合、スマホ向けページにて、表示機構などで上手く動作しなかった為、修正。<br/><br/>
  - jquery.mobile-1.0a2をjquery.mobile-1.4.5に更新。
  - jquery-1.4.4.minをjquery-2.2.4.minに更新。
  - スクリプトのエラーや処理の修正。
 
-2022/11/22-
* スマホ・モバイルページ向けのrank.php クロスサイトスクリプティング。脆弱性の修正。([nkbt-40@2105111814](https://github.com/Utaharu/Yomi-Search_PHP/issues/2))

-2022/11/20-
* (既存設置の場合用)データベースのipカラムをipv6様に拡張させる処理を追加。 <br/>
 db_check.phpの追加。<br/>
　管理者ページ -> 環境設定　画面から、状態の確認と処理を出来るように。<br/>

-2022/09/01-
* mobile版の一部エラーを修正など。php8適応用

-2022/08/31-
* mobile版の一部エラーを修正。php8適応用

-2022/08/30-
* smartphone版の一部エラーを修正。php8適応用

-2022/08/28-
* smartphone版の一部をエラー修正。php8適応用

-2022/08/15-
* admin.php array keyのWarningErrorが出ないように修正。

-2022/08/12-
* php/search.php idを検索対象の追加のコード記述ミスの修正。
* Copyright表示方法の調整。

-2022/08/11-
* php/search.php idを検索対象に追加。
* 登録バックアップデータの作成/ダウンロードが行えるように修正。(nkbt-31_2@1704100057)

-2022/08/09-
* admin.php キーワードランキングの集計対象外のキーワードを一括登録が機能してなかったのを修正。

-2022/08/08-
* admin.phpなどのキーワードランキング用。(nkbt-27@1702152209) <br/>
 キーワードの表示/非表示設定の反映が出来ない部分があったのを修正。 <br/>
* 登録するカテゴリのセレクトボックスをmultipleにも出来るように。([nkbt-37@1811202326](https://github.com/Utaharu/Yomi-Search_PHP/issues/3)) <br/>
 管理者ページ -> 環境設定(登録処理関連)から、切り替え可能に。 <br/>
 
-2022/08/07-
* テンプレート内で変数の記述間違いを修正。（cgi_path）

-2022/08/05-
* Deprecated nullエラーが出るregist等スクリプトを修正。
 
-2022/08/04-
* スクリプト名を変更した場合、一部リンクが動作しないのを修正。
* Deprecated nullエラーが出る一部のスクリプトを修正。

-2022/08/02-
* get_magic_quotes_gpcは使えなくなったので削除。

-2022/08/01-
* PCページ向けのrank.php クロスサイトスクリプティング・脆弱性の修正。([nkbt-40@2105111814](https://github.com/Utaharu/Yomi-Search_PHP/issues/2))

-2022/07/31-
* setup01.php - ipv6対応の為、ip VARCHAR(15)->ip VARCHAR(40)に拡張
* setup03.php - cfgテーブル更新後、設定情報を呼び出し、setup03.htmlの「検索エンジンのトップページに移動」リンクにスクリプト名を差し込むように変更。
* setup02.php - $HTTP_SERVER_VARS変数　削除により->$_SERVERに変更。
* setupディレクトリのsetup.phpをindex.phpに名前変更。
* yomi.php削除。index.phpのみに。

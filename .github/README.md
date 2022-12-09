# Yomi-Search PHP

<br/>

> ## このプログラムのバージョン履歴について
現在: Yomi-Search PHP Ver:8.0β (Yomi-Search(PHP)modified ver1.5.8.n3)<br/>
4 :　(PHP版: Ver.6 - 7) [Yomi-Search(PHP)modified ver1.5.8.n2_1.1](http://www.nkbt.net/yomi/) - nkbt版<br/>
3　:　(PHP版: Ver.5 - 6) [Yomi-Search(PHP)modified ver1.5.8](http://yomiphp-mod.sweet82.com/) - Yomi-Search(PHP)modified版<br/>
2　:　(PHP版: Ver.4 - 5) [Yomi-Search(PHP) Ver4.19.5](http://sql.s28.xrea.com/) - Yomi-Search(PHP)版<br/>
1　:　(CGI版: Ver.0 - 4) Yomi-Search [WonderLink](http://yomi.pekori.to/) / [Vector](https://www.vector.co.jp/soft/unix/net/se124310.html) - CGI版<br/>

<br/>

> ## やっていくこと。
* Yomi-Search PHP Ver:9.0
  - 各種処理コードの関数化な、調整など。

* Yomi-Search PHP Ver:8.0β 
  - nkbt-n2版からの各種修正や、php8対応など。


> ### 要検討
* smartphoneページ
* mobileページ


> ## 修正

<br/>

>> ### [拡張修正](https://github.com/Utaharu/Yomi-Search_PHP/issues?q=label%3A%E6%8B%A1%E5%BC%B5+)
- [ ] [PHP8対応](https://github.com/Utaharu/Yomi-Search_PHP/issues/5)
- - [ ] pc (/)
- - [ ] smartphone (/s/)
- - [ ] mobile (/m/)

- [x] [DBにおける、IPV6対応](https://github.com/Utaharu/Yomi-Search_PHP/issues/1)
- - [x] 新規設置(setup)
- - [x] 既存設置からipカラムを拡張用(管理者ページ->環境設定)

- [ ] [登録カテゴリー・セレクトボックスのmultiple化(nkbt-37@1811202326)](https://github.com/Utaharu/Yomi-Search_PHP/issues/3)
- - [x] pc
- - [x] smartphone (/s/)
- - [ ] mobile (/m/)

>> ### [バグ修正](https://github.com/Utaharu/Yomi-Search_PHP/issues?q=label%3A%E3%83%90%E3%82%B0)
- [x] [rank.php、XSSオープンリダイレクトの脆弱性](https://github.com/Utaharu/Yomi-Search_PHP/issues/2)
- - [x] pc
- - [x] smartphone (/s/)
- - [x] mobile (/m/)

- [x] [キーワードの表示/非表示設定が反映されない(nkbt-27@1702152209)](https://github.com/Utaharu/Yomi-Search_PHP/issues/4)

- [x] [登録データのバックアップ・復元のバックアップデータの作成が行われない。(nkbt-31_2@1704100057)](https://github.com/Utaharu/Yomi-Search_PHP/issues/6)
- [x] キーワードランキングの集計対象外のキーワードを一括登録が機能してない。

<br/>

> ## [更新履歴](History.md)
-2022/12/09-
*ファイルの整理・nullエラーの修正。

-2022/12/04-
* ファイルの整理・位置の変更。
* 呼び出しコードの修正など

-2022/12/03-
* 不要ファイルの削除
* ディレクトリの変更。
* テンプレートの修正。

-2022/11/29-
* スマホ向けの各種テンプレートの修正。
 - 検索結果ページで、再検索が行えなかったformタグを修正など。

-2022/11/28-
* スマホ向けページの変数の変更。
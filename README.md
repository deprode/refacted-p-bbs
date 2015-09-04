# P-BBS
[http://php.s3.to](http://php.s3.to)
Copyright(C) 1999-2015 ToR all rights reserved.

## 概要

* シンプルなノーマル掲示板です。
* 返信ボタンにより記事引用レスが可能です。
* ユーザーによる削除が出来ます。
* 連続投稿、二重投稿を防止します。
* 空白のみの書き込みを防止します
* URLの自動リンク
* 管理者モードにより、好きな記事を削除出来ます。
* 一ページ目をHTMLに書き出す事が出来ます。
* ログが一定以上になると過去ログへ書き出します

## 設置方法

1. 空のファイル「bbs.log」を作成します
2. 本体の名前を「p-bbs.php」などに変更していっしょにアップロードします
3. bbs.log のパーミッション（属性）を606にします。
4. そのディレクトリのパーミッション（属性）を755にします。
5. 過去ログディレクトリも属性 755にします。

デザインは可能な範囲で編集してください。

## 注意点

* ディレクトリに書き込み属性が無いとHTMLが書き出せません
* HTMLを書き出す設定にした場合、クッキーは効かないです

## 更新履歴
2000/12/02 pre 完成
2001/03/06 v1.0 完成ー
2001/03/11 v1.1 HTML書き出すOnOff、書き込み後Locationで飛ばす、管理モードpass→apass
2001/03/16 v1.1 ちょっと整形
2001/04/15 v1.2 デザイン変更、ページング、過去ログ
2001/04/20 v1.21 トクトクで動かしたらエラー出たので修正、@include→やめ、header→refresh
2001/04/24 v1.23 書き込み後表示関数化、ページング変更、管理モード実行後修正、ホスト表示、Re:[2]
2001/05/04 v1.231 クッキーをHTMLに書き出してしまうバグ修正,過去ログモードの非表示
2001/05/17 v1.232 文字数制限、行数制限追加
2001/05/27 v1.24 autolink修正、書き込み後refreshで飛ばす。Cタイプ。Cに編集機能
2002/01/14 v1.24 外部書き込み説明
2002/05/25 v1.24 i18n削除
2002/02/11 v1.28 クッキーの文字化け対策
2003/05/25 v1.29 禁止ホスト、禁止ワード追加
2003/06/07 v1.3 複数削除出来るように
<?php
/*
Beちゃっとぉ
© 2020 Fukuda-B/Dakhuf

// ----- 予定 -----
チャットにユーザー名を入力するようにする
改行コードに対応する
アプリ版を作る
コマンドに対応する
通知 (設定できるようにする)

// ----- コマンドなど -----

From: Youtube
太字 *太字*
_斜体_
-取り消し線-
:***: //***部分に文字列で隠し絵文字に自動変換される

From: Markdownチートシート
```ソースコード```
# これはH1タグ
## これはH2タグ

// ----- 更新履歴 -----
Ver.0.5
Cometによって効率化

Ver.0.4
ショートカット送信対応
URL自動リンク化

~Ver.0.3
Ajaxで基本的なチャット
*/

// ----- PHPini設定 -----
ini_set("max_execution_time",240); // 最大タイムアウト時間の指定
// ----- 定数定義 -----
define("HOLDING_TIMER", "180"); // リクエストの保持ループ回数
define("TIMER_DENOMINATOR", "1"); // 更新チェック実行間隔(s)
//>> (HOLDING_TIMER×TIMER_DENOMINATOR≠保持時間(s))

// ----- 変数 -----

$save_file = 'bbs.txt';
$save2_file = 'bbb.dat';
$break_flag = 0;

// タイムゾーン指定 (Asia/Tokyo)
date_default_timezone_set('Asia/Tokyo');
header("Access-Control-Allow-Origin: *"); // CORS対策

// ----- メイン処理 -----
// ----- メッセージがPOSTされたとき -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['b_send'])) {
  $put_data = htmlspecialchars($_POST['b_send'], ENT_QUOTES, "UTF-8");
  $push_data = $put_data."\t".date('Y-m-d H:i:s')."\n";
  $push_data2 = $put_data."\t".date('Y-m-d H:i:s')."\t".$_SERVER["REMOTE_ADDR"]."\n";
  file_put_contents($save_file, $push_data, FILE_APPEND);
  file_put_contents($save2_file, $push_data2, FILE_APPEND);
  if(isset($_POST["last_date"])) {
      echo date("ymdHis",filemtime($save_file))."\n".file_get_contents($save_file);
  } else {
    echo date("ymdHis",filemtime($save_file))."\n".file_get_contents($save_file);
    $set_time = date("ymdHis",filemtime($save_file));
    setcookie("last_date",$set_time,time()+60*60*24*7);
  }
// ----- メッセージがREQUESTされたとき -----
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['b_req'])) {
  if(isset($_POST["last_date"]) && $_POST['b_req'] != 'bbb') { // 前の更新日時と同じとき、再送しない
    // サーバでリクエストの保持
    $rec_date = $_POST["last_date"];
    for($i=0; $i<HOLDING_TIMER; $i++) { // REQUESTの保持ループ
      clearstatcache(false, $save_file); // ファイルのステータスのキャッシュをクリア
      $file_date = date("ymdHis",filemtime($save_file));
      // echo $file_date."\n".$rec_date."\n";
      if($rec_date !== $file_date) { // 更新があったら内容出力
        $break_flag= 1;
          break;
      }
      sleep(TIMER_DENOMINATOR);
    }
    if($break_flag===0) {
      echo 'B';
    } else {
      echo date("ymdHis",filemtime($save_file))."\n".file_get_contents($save_file);
    }
  } else {
    // 初回ロード時など、即レスポンスする
    echo date("ymdHis",filemtime($save_file))."\n".file_get_contents($save_file);
  }
}

?>
<?php
/**
 * mdownweb - build website using markdown extra
 * 
 * 閲覧者からのアクセスを捌くスクリプト．
 * 閲覧者からのリクエストをMdownWebクラスに投げ，出力指示まで行う
 * @author YoKamaru
 */

/**#@+
 * Configuration
 */
/**
 * 各ページの中身を記述したファイル（*.mdown）が置かれているディレクトリツリーのトップディレクトリ
 */
define('ARTICLE_DIR', 'articles');

require 'lib/mdownweb/mdownweb.php';

$site = new MdownWeb(ARTICLE_DIR);

$request = isset($_GET['p']) ? $_GET['p'] : 'index';

$site->load($request);
$site->output();
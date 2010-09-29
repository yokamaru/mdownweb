<?php
/**
 * mdownweb - build website using markdown extra
 * 
 */

/**#@+
 * Configuration
 */
/**
 * Articles' path
 */
define('ARTICLE_DIR', 'articles');

require 'lib/mdownweb/mdownweb.php';

$site = new MdownWeb(ARTICLE_DIR);

$request = isset($_GET['p']) ? $_GET['p'] : 'index.html';

$site->load($request);
$site->output();

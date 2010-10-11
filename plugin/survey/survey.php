<?php
/**
 * アンケートプラグイン
 * @author YoKamaru
 */

require_once(dirname(__FILE__) . '/../pluginbase.php');

/**
 * アンケート関連の処理を行うクラス
 * @author YoKamaru
 */
class Survey extends PluginBase
{
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * プラグインを実行する
     * @param $template MdownWeb本体によってセットされたテンプレート情報
     * @param $exec_result 他のプラグインによってセットされた情報 
     * @return プラグインの実行結果
     */
    public function exec($template, $exec_result)
    {
        return array();
    }
}

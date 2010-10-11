<?php
class PluginBase
{
    function __construct()
    {
        
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
<?php

class Plugins
{
    
    /**
     * プラグイン格納ディレクトリのパス
     * @var string
     */
    protected $plugin_directory;
    
    /**
     * プラグインのインスタンス配列
     * @var array
     */
    protected $plugin_instance;
    
    /**
     * プラグインの実行結果を格納する配列
     * @var array
     */
    protected $exec_result;
    
    /**
     * コンストラクタ
     */
    function __construct()
    {
        // プラグイン格納ディレクトリ設定
        $this->plugin_directory = realpath(dirname(__FILE__) . '/../../plugin');
        
        $this->plugin_instance = array();
        $this->exec_result = array();
    }
    
    /**
     * 使用するプラグインを追加する
     * @param $name プラグインのクラス名の配列
     */
    public function add($name)
    {
        while (list($key, $plugin_name) = each($name))
        {
            $plugin_path = $this->plugin_directory . DIRECTORY_SEPARATOR . 
                           strtolower($name) . DIRECTORY_SEPARATOR .
                           strtolower($name) . '.php';
            
            // プラグインの存在チェック
            if (!is_readable($plugin_path))
            {
                throw new PluginNotFoundExceprion('Plugin not found');
            }
            
            // インスタンスを生成して追加
            require_once($plugin_path);
            $new_instance = new $plugin_name;
            $this->plugin_instance[$plugin_name] = $new_instance;
        }
        return $this;
    }
    
    /**
     * プラグインを実行する
     */
    public function exec()
    {
        reset($this->plugin_instance);
        while (list($plugin_name, $instance) = each($this->plugin_instance))
        {
            $this->exec_result[$plugin_name] = $instance->exec();
        }
        return $this;
    }
    
}
<?php
/**
 * 
 */

require dirname(__FILE__) . '/../markdown/markdown.php';
require dirname(__FILE__) . '/errors.php';

/**
 * 
 * @author YoKamaru
 *
 */
class MdownWeb
{
    /**
     * 各ページの中身を記述したファイル（*.mdown）が置かれているディレクトリ
     * @var string
     */
    protected $article_directory;
    
    /**
     * 
     * @var string
     */
    protected $request_realpath;
    
    /**
     * 
     * @var array
     */
    protected $template_default = array('title' => 'Undefined',
                                        'sitename' => 'MdownWeb',
                                  	    'header' => '',
                                        'footer' => '',
                                        'body' => '',                        
    );
    
    /**
     * 
     * @var array
     */
    protected $template = array();
    
    protected $http_errorno;
    
    function __construct($directory)
    {
        if (!file_exists($directory))
        {
            throw new Exception('No such directory');
        }
        else
        {
            $this->article_directory = realpath($directory);
        }
    }
    
    /**
     * 
     * @param $request
     */
    public function load($request)
    {
        // リクエストの正当性検証
        try
        {
            $request_realpath = $this->checkRequest($request);
        }
        catch (FileNotFoundException $e)
        {
            $request_realpath = realpath(dirname(__FILE__) . '/error/error404.mdown');
            $this->http_errorno = 404;
        }
        catch (PermissionDeniedException $e)
        {
            $request_realpath = realpath(dirname(__FILE__) . '/error/error403.mdown');
            $this->http_errorno = 403;
        }
        
        // finally代替・救いようのないエラー
        if ($request_realpath === FALSE)
        {
            throw new Exception('No library presented error file');
        }
        
        $this->request_realpath = $request_realpath;
        
        // MarkdownなテキストをMarkupする
        $this->template = array_merge($this->template, array('body' => $this->load_markdown()));
        
        // 各ファイル固有の設定を読み込む
        $this->template = array_merge($this->template, $this->load_ini());
        
        return;
    }
    
    /**
     * 
     */
    protected function load_markdown()
    {
    // Markupする
        $mdown_text = file_get_contents($this->request_realpath);
        if ($mdown_text === FALSE)
        {
            throw new Exception('Error occurred during read the mdown file');
        }
        
        $html_text = $this->mdownToHtml($mdown_text);
        if ($html_text === FALSE)
        {
            throw new Exception('Error occurred during markupping');
        }
        
        return $html_text;
    }
    
    /**
     * 
     */
    protected function load_ini()
    {
        $ini_realpath = $this->request_realpath . '.ini';
        
        if (!is_readable($ini_realpath))
        {
            return array();
        }
        
        $ini_array = parse_ini_file($ini_realpath);
        
        // PHP 5.2.7での仕様変更対策
        // from manual 
        //   '構文エラーが発生した場合は、空の配列ではなく FALSE を返すようになりました。'
        if ($ini_array === FALSE)
        {
            $ini_array = arrray();
        }
        
        return $ini_array;
    }
    
    /**
     * リクエストが正当なものであるかチェックする
     * 
     * リクエストされたページに対して正当なアクセス権があるかをチェックする．
     * 読み込み権限が無いことに起因するエラーやディレクトリトラバーサルの防止を目的とする．
     * @param $request アクセスしたユーザからリクエストされたページ
     */
    protected function checkRequest($request)
    {
        
        if (! mb_strlen($request) > 0)
        {
            throw new Exception('Request is empty');
        }
        
        // リクエストの意図（ディレクトリorファイル）を判別
        // 末尾が/(or \)ならディレクトリを意図したとみなし，index.mdocを付加
        // （DirectoryIndex的処理）
        if (mb_substr($request, -1, 1) === DIRECTORY_SEPARATOR)
        {
            // ディレクトリと判定
            $request_withfilename = $request . 'index.mdown';
        }
        else
        {
            // ファイルと判定
            $request_withfilename = $request . '.mdown';
        }
        
        $path_unreal = $this->article_directory . DIRECTORY_SEPARATOR . $request_withfilename;

        $path_real = realpath($path_unreal);
        
        if ($path_real === FALSE)
        {
            // 本来403を返すべき状況も有りうるが，ここは404で．
            throw new FileNotFoundException('No such file');
        }
        
        $pattern = '^' . $this->article_directory . DIRECTORY_SEPARATOR;
        $is_subdir = mb_ereg_match($pattern, $path_real);
        
        if ($is_subdir === FALSE)
        {
            // ディレクトリトラバーサル検出
            throw new PermissionDeniedException('Detect directory traversal attack');
        }
        
        if (is_readable($path_real) === FALSE)
        {
            // 本来403を返すべき状況だが，404を返す
            throw new FileNotFoundException('Permission denied');
        }
        
        return $path_real;
    }
    
    /**
     * Markdown形式の文字列をMarkupし，HTMLを返す
     * @param $mdown_text Markdown形式の文字列
     */
    protected function mdownToHtml($mdown_text)
    {
        $html_text = Markdown($mdown_text);
        
        return $html_text;
    }
    
    public function output()
    {
        // テンプレート用の値の最終マージ
        $template = array_merge($this->template_default, $this->template);
        
        require 'template/template.php';
    }
}
<?php
/**
 * MdownWebの主要処理を行うクラスを記述したファイル
 * @author YoKamaru
 */

require dirname(__FILE__) . '/../markdown/markdown.php';
require dirname(__FILE__) . '/errors.php';

/**
 * MdownWebの主要処理を行うクラス
 * @author YoKamaru
 */
class MdownWeb
{
    /**
     * 各ページの中身を記述したファイル（*.mdown）が置かれているディレクトリツリーのトップディレクトリ
     * @var string
     */
    protected $article_directory;
    
    /**
     * リクエストされたページの絶対パス
     * @var string
     */
    protected $request_realpath;
    
    /**
     * テンプレートに埋め込む値が未定である際に使用するデフォルト値
     * @var array
     */
    protected $template_default = array('title' => 'Undefined',
                                        'sitename' => 'MdownWeb',
                                        'header' => '',
                                        'footer' => '',
                                        'body' => '',
    );
    
    /**
     * テンプレートに埋め込む値
     * @var array
     */
    protected $template = array();
    
    /**
     * 出力するHTTP Status codeを指定
     * 
     * 200番代以外のStatus code（エラー）を返す際に指定する
     * @var integer
     */
    protected $http_errorno;
    
    /**
     * コンストラクタ
     * @param $directory 各ページの中身を記述したファイル（*.mdown）が置かれているディレクトリツリーのトップディレクトリ
     */
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
     * ユーザからのリクエストをパースし，該当するファイルを読み込む
     * @param $request ユーザから与えられたリクエスト
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
     * Markdown形式のファイルを読み込み，Markupする
     * @throws Exception 外部の関数・ライブラリに起因する致命的なエラー
     * @return string MarkupしたHTML文字列
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
     * *.mdownファイルに付随した.iniファイル（*.mdown.ini）を読み込む
     * @return array 読み込んだiniファイルの中身　エラーが発生した場合は空配列を返す
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
     * @throws Exception 不当な引数に起因するエラー
     * @throws FileNotFoundException リクエストされたファイルが見つからない
     * @throws PermissionDeniedException リクエストされたファイルへのアクセス権が無い
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
     * @return string MarkupしたHTML文字列
     */
    protected function mdownToHtml($mdown_text)
    {
        $html_text = Markdown($mdown_text);
        
        return $html_text;
    }
    
    /**
     * テンプレートファイルを読み込み，出力する
     */
    public function output()
    {
        // テンプレート用の値の最終マージ
        $template = array_merge($this->template_default, $this->template);
        
        // HTTPヘッダ出力
        $this->outputHttpHeader();
        
        require dirname(__FILE__) . '/template/template.php';
    }
    
    /**
     * セットされたHTTP Status Codeに合わせたヘッダーを出力する
     */
    protected function outputHttpHeader()
    {
        switch($this->http_errorno)
        {
            case 403:
                header("HTTP/1.1 404 Not Found");
                break;
            case 404:
                header("HTTP/1.1 403 Forbidden");
                break;
        }
    }
}
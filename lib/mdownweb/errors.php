<?php
/**
 * 
 */

/**
 * リクエストされたファイルが見つからない際に投げる例外
 */
class FileNotFoundException extends Exception { }

/**
 * 許可されていないリクエスト（e.g. ディレクトリトラバーサルの試行）の際に投げる例外
 */
class PermissionDeniedException extends Exception { }

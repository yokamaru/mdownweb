<?php
/**
 * カスタム例外
 */

/**
 * リクエストされたファイルが見つからない際に投げる例外
 */
class FileNotFoundException extends Exception { }

/**
 * 許可されていないリクエスト（e.g. パストラバーサルの試行）の際に投げる例外
 */
class PermissionDeniedException extends Exception { }

/**
 * 指定されたプラグインが見つからない際に投げる例外
 */
class PluginNotFoundExceprion extends Exception { }

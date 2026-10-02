<?php
/**
 * JIN foods テーマ
 *
 * 現行サイト（Twenty Twelve を直接改造）で起きていた問題を持ち込まないための方針：
 *  1. 公式テーマを改造せず、独立したテーマとして作る
 *     → テーマ更新でカスタマイズが消える事故が起きない
 *  2. <title> と meta description をテンプレートに直書きしない
 *     → title-tag のサポートに任せる。現行は9ページすべて同じタイトルだった
 *  3. テーマ内に独自の画像パスを作らない
 *     → 現行は themes/twentytwelve/common/images/noimg/logo-f.png を参照していて404だった。
 *       代替画像は assets/images に置き、存在を確認してから出す（jinfoods_thumb_url）
 *  4. PHP 8 前提
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JINFOODS_VERSION', '1.0.0' );
define( 'JINFOODS_DIR', get_template_directory() );
define( 'JINFOODS_URI', get_template_directory_uri() );

require_once JINFOODS_DIR . '/inc/setup.php';
require_once JINFOODS_DIR . '/inc/enqueue.php';
require_once JINFOODS_DIR . '/inc/post-types.php';
require_once JINFOODS_DIR . '/inc/fields.php';
require_once JINFOODS_DIR . '/inc/template-tags.php';
require_once JINFOODS_DIR . '/inc/schema.php';
require_once JINFOODS_DIR . '/inc/contact-form.php';

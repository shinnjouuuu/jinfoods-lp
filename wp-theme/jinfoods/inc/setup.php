<?php
/**
 * テーマの基本設定
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'jinfoods_setup' );
function jinfoods_setup(): void {
	// <title> はテーマで直書きせず WordPress に生成させる。
	// 現行サイトは header.php にタイトルをハードコードしていて、
	// 全9ページが同じタイトルになっていた
	add_theme_support( 'title-tag' );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	// アイキャッチのサイズ。一覧は 1:1、記事は 16:10 で使う
	set_post_thumbnail_size( 800, 800, true );
	add_image_size( 'jinfoods_square', 800, 800, true );
	add_image_size( 'jinfoods_entry', 1280, 800, true );

	register_nav_menus( array(
		'primary' => 'ヘッダーのメニュー',
		'footer_service' => 'フッター：事業について',
		'footer_company' => 'フッター：会社情報',
	) );
}

/**
 * 抜粋の長さと省略記号
 */
add_filter( 'excerpt_length', fn() => 70 );
add_filter( 'excerpt_more', fn() => '…' );

/**
 * 不要な出力を止める（現行サイトで露出していたものを塞ぐ）
 *  - 生成器のバージョン表記
 *  - REST のユーザー一覧（診断時、管理者名2件が外部から取得できた）
 */
remove_action( 'wp_head', 'wp_generator' );

add_filter( 'rest_endpoints', 'jinfoods_block_user_endpoints' );
function jinfoods_block_user_endpoints( array $endpoints ): array {
	// ログインしていない相手にはユーザー一覧・詳細を返さない
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	return $endpoints;
}

/**
 * 食事ギャラリー（投稿タイプ gallery。inc/post-types.php）の一覧か1件のページかを判定する。
 * お知らせは「投稿」の news カテゴリ。
 * 現行サイトは ?cat=1 / ?cat=2 だったので、移行時にお知らせは投稿へ、行事食は gallery へ入れる
 */
function jinfoods_is_gallery(): bool {
	return is_post_type_archive( 'gallery' ) || is_singular( 'gallery' );
}

/**
 * 食事ギャラリーの一覧は、ページを分けずに全件を1ページに出す（2026-10-01、C「小さく並べて、押すと大きく」）。
 * 上の「◯枚」と拡大表示の「何枚目 / 全部で何枚」をそろえるため（WP の初期値の10件で分けると「36枚」の下で「3 / 10」になる）。
 * LP の gallery.html も36枚を1ページに並べている
 */
function jinfoods_gallery_all_posts( WP_Query $query ): void {
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'gallery' ) ) {
		$query->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', 'jinfoods_gallery_all_posts' );

// 前の置き場（投稿のカテゴリ gallery・/category/gallery/）は 2026-10-01 にカテゴリごと消した（新庄さん決定）。
// 公開前のテーマだけにあった URL なので、/gallery/ への転送は置いていない（今のサイトは ?cat=2 で、移行時の 301 は別に作る）

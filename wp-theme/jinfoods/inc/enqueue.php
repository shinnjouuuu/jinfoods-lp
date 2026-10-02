<?php
/**
 * CSS / JS の読み込み
 *
 * バージョンは filemtime で付ける。手で数字を上げると必ず上げ忘れるため
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'jinfoods_enqueue' );
function jinfoods_enqueue(): void {
	$css = '/assets/css/style.css';
	$js  = '/assets/js/main.js';

	// Webフォント：見出しの Zen角ゴシック 700 と英数字の Jost 500・700 だけ（本文は端末の書体。2026-09-30 B案。DESIGN.md §3）。
	// 太さや書体を足すときは、Sass の --font-* と DESIGN.md の表も直す
	wp_enqueue_style(
		'jinfoods-fonts',
		'https://fonts.googleapis.com/css2?family=Jost:wght@500;700&family=Zen+Kaku+Gothic+New:wght@700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'jinfoods',
		JINFOODS_URI . $css,
		array( 'jinfoods-fonts' ),
		jinfoods_asset_version( $css )
	);

	wp_enqueue_script(
		'jinfoods',
		JINFOODS_URI . $js,
		array(),
		jinfoods_asset_version( $js ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	// トップの FV の写真の層を 3D にする。Three.js と GSAP はこのファイルが読み込み後に cdnjs から読む
	if ( is_front_page() ) {
		$fv = '/assets/js/fv-steam.js';
		wp_enqueue_script(
			'jinfoods-fv-steam',
			JINFOODS_URI . $fv,
			array(),
			jinfoods_asset_version( $fv ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/**
 * ファイルの更新時刻をバージョンに使う。無ければテーマ版に落とす
 */
function jinfoods_asset_version( string $relative ): string {
	$path = JINFOODS_DIR . $relative;
	return file_exists( $path ) ? (string) filemtime( $path ) : JINFOODS_VERSION;
}

/**
 * 絵文字スクリプトを外す（この規模のサイトでは使わない）
 */
add_action( 'init', function (): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
} );

/**
 * Webフォントの CSS で描画を止めない（2026-10-01、Lighthouse の「レンダリングを妨げるリソース」から）。
 * preload で取りにいき、届いたら stylesheet に替える。届くまでは端末の書体で出し、届いたら差し替わる（display=swap）。
 * JS が無いときは noscript で普通に読む。
 * LP（静的版）は「使う字だけ」を頼む text= も付けているが、WP は投稿で字が増えるので付けない（全部の字のまま）
 */
add_filter( 'style_loader_tag', 'jinfoods_async_fonts', 10, 4 );
function jinfoods_async_fonts( string $tag, string $handle, string $href, string $media ): string {
	if ( 'jinfoods-fonts' !== $handle ) {
		return $tag;
	}
	return sprintf(
		'<link rel="preload" as="style" id="%1$s-css" href="%2$s" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n" .
		'<noscript><link rel="stylesheet" href="%2$s"></noscript>' . "\n",
		esc_attr( $handle ),
		esc_url( $href )
	);
}

/**
 * Google Fonts へ先につなぐ（preconnect）。書体の CSS と書体ファイルは別のホストから来る
 */
add_filter( 'wp_resource_hints', 'jinfoods_font_hints', 10, 2 );
function jinfoods_font_hints( array $urls, string $relation ): array {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}

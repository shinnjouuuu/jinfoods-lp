<?php
/**
 * 固定ページの本文を LP から移したものに差し替える（wp-cli の eval-file で実行する）
 *
 *   wp eval-file seed-pages.php --user=<管理者のログイン名>
 *
 * 本文は content/<スラッグ>.html、タイトルと説明文は content/meta.json にある。
 * どちらも LP（../*.html）から書き出したもので、LP を直したら書き出し直してから流す。
 *
 * --user を付けるのは、管理者でないと本文の iframe（会社概要の地図）や
 * class 属性が保存時に消されるため（unfiltered_html 権限が要る）。
 *
 * 本文は「カスタムHTML」ブロック1つで入れている。管理画面から編集はできるが HTML を直す形になる。
 * 同じ内容なら更新しないので、何度実行してもリビジョンは増えない。
 *
 * お問い合わせの本文は Contact Form 7 のフォームを題名で呼ぶ。先に seed-cf7.php を流してフォームを作っておく。
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "wp-cli から実行してください: wp eval-file seed-pages.php --user=<管理者>\n" );
	return;
}

if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( '--user=<管理者のログイン名> を付けて実行してください。付けないと地図や class が消えます。' );
}

$dir  = __DIR__ . '/content';
$meta = json_decode( (string) file_get_contents( $dir . '/meta.json' ), true );

foreach ( array( 'about', 'service', 'recruit', 'privacy', 'contact' ) as $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		WP_CLI::warning( "固定ページが見つかりません: {$slug}（先に seed.php を実行）" );
		continue;
	}

	$html = (string) file_get_contents( "{$dir}/{$slug}.html" );
	if ( $page->post_content === $html ) {
		WP_CLI::log( "本文は同じなので更新しない: {$slug}" );
	} else {
		$res = wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => wp_slash( $html ),
		), true );
		if ( is_wp_error( $res ) ) {
			WP_CLI::warning( "本文の更新に失敗: {$slug} / " . $res->get_error_message() );
			continue;
		}
		WP_CLI::log( "本文を更新: {$slug}（" . mb_strlen( $html ) . '字）' );
	}

	// SEO SIMPLE PACK のページ別設定。LP の <title> と description をそのまま使う
	if ( isset( $meta[ $slug ] ) ) {
		update_post_meta( $page->ID, 'ssp_meta_title', $meta[ $slug ]['title'] );
		update_post_meta( $page->ID, 'ssp_meta_description', $meta[ $slug ]['description'] );
	}
}

// トップページ：SEO SIMPLE PACK の全体設定（home_title / home_desc）に LP の値を入れる。
// 既定の「サイト名 | キャッチフレーズ」だと LP の3段のタイトルにならないため、置換タグを使わず文字で持つ
if ( isset( $meta['_home'] ) ) {
	$ssp = get_option( 'ssp_settings', array() );
	if ( is_array( $ssp ) ) {
		$ssp['home_title'] = $meta['_home']['title'];
		$ssp['home_desc']  = $meta['_home']['description'];
		update_option( 'ssp_settings', $ssp );
		WP_CLI::log( 'トップページのタイトルと説明文を設定' );
	}
}

// カテゴリー一覧（お知らせ）：SEO SIMPLE PACK のターム別設定
foreach ( array( 'news' ) as $cat_slug ) {
	$key  = "_cat_{$cat_slug}";
	$term = get_category_by_slug( $cat_slug );
	if ( ! $term || ! isset( $meta[ $key ] ) ) {
		continue;
	}
	update_term_meta( $term->term_id, 'ssp_meta_title', $meta[ $key ]['title'] );
	update_term_meta( $term->term_id, 'ssp_meta_description', $meta[ $key ]['description'] );
	WP_CLI::log( "カテゴリーのタイトルと説明文を設定: {$cat_slug}" );
}

// 食事ギャラリーの一覧（/gallery/）：SEO SIMPLE PACK の「投稿タイプのアーカイブ」の設定。
// 2026-10-01 にカテゴリから投稿タイプ gallery へ分けた。SEO SIMPLE PACK はアーカイブを既定で noindex にするので、
// カテゴリだったときと同じく検索に出すように戻す。アーカイブを持つ投稿タイプは gallery だけなので、共通の設定にそのまま入れる。
// meta.json の鍵は export-content.py が作る名前（_cat_gallery）のまま使う
if ( isset( $meta['_cat_gallery'] ) ) {
	$ssp = get_option( 'ssp_settings', array() );
	if ( is_array( $ssp ) ) {
		$ssp['pt_archive_noindex'] = false;
		$ssp['pt_archive_title']   = $meta['_cat_gallery']['title'];
		$ssp['pt_archive_desc']    = $meta['_cat_gallery']['description'];
		update_option( 'ssp_settings', $ssp );
		WP_CLI::log( '食事ギャラリーの一覧のタイトルと説明文を設定（検索に出す）' );
	}
}

WP_CLI::success( '固定ページの本文と、各ページのタイトル・説明文を流し込みました。' );

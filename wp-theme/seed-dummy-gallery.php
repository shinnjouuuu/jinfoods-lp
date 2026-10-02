<?php
/**
 * 食事ギャラリーのダミー投稿（Local の確認用。本番では流さない）
 *
 * seed.php が入れる行事食は5件だけで、/gallery/ の格子・「◯枚」・拡大表示の送り・トップの4列が
 * 運用の量で正しいか見えない。LP の gallery.html と同じ36枚になるまで足し、LP と並べて比べられるようにする。
 *  ・写真は LP の images/g-NN.jpg（800×800）のうち、seed.php の5件が使っていない31枚
 *  ・alt は gallery.html の同じ写真の説明をそのまま使う（新しい文は作らない）
 *  ・題は運用の書き方（例：2月行事食）。2023年10月〜2026年9月の毎月1件で、既存5件の月（2024年10月〜2025年2月）は飛ばす
 *  ・足した投稿とメディアには _jinfoods_dummy=1 を付ける。消すときはこれだけを拾う（seed.php の5件は消さない）
 *
 * 足す: php ... "$WP" --path="$PUB" eval-file seed-dummy-gallery.php
 * 消す: php ... "$WP" --path="$PUB" eval-file seed-dummy-gallery.php remove
 * （php の -d と $SOCK は README「初期データの投入」と同じ）
 *
 * @package jinfoods
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

$dummy_key = '_jinfoods_dummy';

// ---- 消す ----
if ( isset( $args[0] ) && 'remove' === $args[0] ) {
	$ids = get_posts( array(
		'post_type'   => array( 'gallery', 'attachment' ),
		'post_status' => 'any',
		'numberposts' => -1,
		'meta_key'    => $dummy_key,
		'fields'      => 'ids',
	) );
	foreach ( $ids as $id ) {
		// メディアは縮小版のファイルごと消す
		'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	}
	WP_CLI::success( 'ダミーを消しました（投稿とメディア ' . count( $ids ) . ' 件）' );
	return;
}

// ---- 足す ----
$exists = get_posts( array(
	'post_type'   => 'gallery',
	'post_status' => 'any',
	'numberposts' => 1,
	'meta_key'    => $dummy_key,
	'fields'      => 'ids',
) );
if ( $exists ) {
	WP_CLI::log( 'ダミーは入っています。入れ直すときは remove を流してから' );
	return;
}

$lp_dir = dirname( __DIR__ );

// gallery.html から写真ごとの説明を取る
$html = file_get_contents( $lp_dir . '/gallery.html' );
preg_match_all( '#href="images/(g-\d{2})\.jpg".*?alt="([^"]*)"#su', $html, $m, PREG_SET_ORDER );
$alts = array();
foreach ( $m as $row ) {
	$alts[ $row[1] ] = html_entity_decode( $row[2], ENT_QUOTES, 'UTF-8' );
}

// seed.php の5件（2月 g-01・1月 g-03・12月 g-02・11月 g-06・10月 g-09）が使っている写真は外す
$files = array_values( array_diff( array_keys( $alts ), array( 'g-01', 'g-02', 'g-03', 'g-06', 'g-09' ) ) );

// 毎月1件。新しい月から並べる
$months = array();
for ( $t = strtotime( '2026-09-01' ); $t >= strtotime( '2023-10-01' ); $t = strtotime( '-1 month', $t ) ) {
	$ym = gmdate( 'Y-m', $t );
	if ( $ym >= '2024-10' && $ym <= '2025-02' ) {
		continue;
	}
	$months[] = $ym;
}

// 季節がはっきりしている写真だけ月を合わせる（ひな祭り→3月、スイカ→8月）。残りは順に入れる
$fixed = array( '2026-03' => 'g-19', '2025-08' => 'g-14' );
$rest  = array_values( array_diff( $files, $fixed ) );

if ( count( $months ) !== count( $files ) ) {
	WP_CLI::error( '月の数（' . count( $months ) . '）と写真の数（' . count( $files ) . '）が合いません。gallery.html を確かめてください' );
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$added = 0;
foreach ( $months as $ym ) {
	$file  = $fixed[ $ym ] ?? array_shift( $rest );
	$src   = $lp_dir . '/images/' . $file . '.jpg';
	$title = (int) substr( $ym, 5, 2 ) . '月行事食';
	// seed.php と同じく月末の 9:00
	$date  = gmdate( 'Y-m-t', strtotime( $ym . '-01' ) ) . ' 09:00:00';

	if ( ! file_exists( $src ) ) {
		WP_CLI::warning( "画像が見つかりません: {$src}" );
		continue;
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'gallery',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_date'    => $date,
		'post_content' => '',
	) );
	update_post_meta( $post_id, $dummy_key, 1 );

	// アップロード先へコピーしてからメディア登録する（LP の元ファイルを移動させない）
	$tmp = wp_tempnam( $file . '.jpg' );
	copy( $src, $tmp );
	$attach_id = media_handle_sideload( array( 'name' => $file . '.jpg', 'tmp_name' => $tmp ), $post_id, $title );
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "取り込み失敗: {$file} / " . $attach_id->get_error_message() );
		continue;
	}
	update_post_meta( $attach_id, $dummy_key, 1 );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $alts[ $file ] );
	set_post_thumbnail( $post_id, $attach_id );

	$added++;
	WP_CLI::log( "{$ym} {$title} ← {$file}（{$alts[ $file ]}）" );
}

WP_CLI::success( "ダミーを {$added} 件足しました。消すときは remove を付けて流す" );

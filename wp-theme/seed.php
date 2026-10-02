<?php
/**
 * 初期データ投入スクリプト（wp-cli の eval-file で実行する）
 *
 *   wp eval-file seed.php
 *
 * やること：
 *  1. カテゴリ news を作る（現行サイトは ?cat=1 / ?cat=2 でスラッグが無かった）
 *  2. 固定ページ about / service / recruit / contact / privacy を作る
 *  3. 現行サイトの実際のお知らせ9件を投入する（行事食5件は投稿タイプ gallery へ。2026-10-01 に投稿のカテゴリから分けた）
 *  4. パーマリンクを投稿名に変える（現行は ?p=998 形式）
 *  5. トップページを固定ページではなく最新の投稿→front-page.php に任せる
 *
 * タイトル一致で既存を探すので、何度実行しても増えない。
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "wp-cli から実行してください: wp eval-file seed.php\n" );
	return;
}

// ---- 1. カテゴリ ----
// 食事ギャラリーはカテゴリではなく投稿タイプ gallery（inc/post-types.php）なので、ここでは作らない
$cats = array(
	'news' => 'お知らせ',
);
$cat_ids = array();
foreach ( $cats as $slug => $name ) {
	$term = get_category_by_slug( $slug );
	if ( ! $term ) {
		$res = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		$cat_ids[ $slug ] = is_wp_error( $res ) ? 0 : (int) $res['term_id'];
		WP_CLI::log( "カテゴリ作成: {$name} ({$slug})" );
	} else {
		$cat_ids[ $slug ] = (int) $term->term_id;
		WP_CLI::log( "カテゴリ既存: {$name}" );
	}
}

// ---- 2. 固定ページ ----
$pages = array(
	'about'   => '会社概要',
	'service' => '事業内容',
	'recruit' => '採用情報',
	'contact' => 'お問い合わせ',
	'privacy' => 'プライバシーポリシー',
);
foreach ( $pages as $slug => $title ) {
	$exists = get_page_by_path( $slug );
	if ( $exists ) {
		WP_CLI::log( "固定ページ既存: {$title}" );
		continue;
	}
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => "<!-- wp:paragraph --><p>{$title}の本文をここに入力します。</p><!-- /wp:paragraph -->",
	) );
	WP_CLI::log( "固定ページ作成: {$title} (ID {$id})" );
}

// ---- 2.5 食事ギャラリーの移し替え ----
// 2026-10-01 より前にこのスクリプトを流したサイトでは、行事食が「投稿」の gallery カテゴリに入っている。
// 投稿タイプ gallery に移し、空になったカテゴリは消す（2026-10-01 新庄さん決定。Local でも消した）。
// 投稿の ID・題・日付・アイキャッチ・本文はそのまま。
// 3. より前に流す（後だと、3. が gallery の中に同じ題を見つけられず、行事食を二重に作る）
$old_gallery = get_category_by_slug( 'gallery' );
if ( $old_gallery ) {
	$moving = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'any',
		'numberposts' => -1,
		'cat'         => $old_gallery->term_id,
	) );
	foreach ( $moving as $m ) {
		wp_remove_object_terms( $m->ID, $old_gallery->term_id, 'category' );
		set_post_type( $m->ID, 'gallery' );
		WP_CLI::log( "食事ギャラリーへ移動: {$m->post_title} (ID {$m->ID})" );
	}
	// ゴミ箱・下書きも含めて1件も残っていないときだけ消す（何か残っていたら消さずに知らせる）
	$left = get_posts( array( 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => 1, 'cat' => $old_gallery->term_id, 'fields' => 'ids' ) );
	if ( $left ) {
		WP_CLI::warning( 'カテゴリ gallery に投稿が残っているので消していません（ID ' . $left[0] . '）' );
	} else {
		wp_delete_term( $old_gallery->term_id, 'category' );
		WP_CLI::log( 'カテゴリ gallery を消しました' );
	}
}

// ---- 3. お知らせ（現行サイトの実データ）----
$posts = array(
	array(
		'date'  => '2025-04-01 09:00:00',
		'title' => '新規　委託開始',
		'cat'   => 'news',
		'body'  => "この度4月1日より、大分市明野にある「有料老人ホーム花埜家」の厨房を委託させていただくこととなりました。\n\nこれからもスタッフ一同精進して参りますので、どうぞよろしくお願い申し上げます。",
	),
	array( 'date' => '2025-02-28 09:00:00', 'title' => '2月行事食',  'cat' => 'gallery', 'body' => '' ),
	array( 'date' => '2025-01-31 09:00:00', 'title' => '1月行事食',  'cat' => 'gallery', 'body' => '' ),
	array( 'date' => '2024-12-31 09:00:00', 'title' => '12月行事食', 'cat' => 'gallery', 'body' => '' ),
	array( 'date' => '2024-11-30 09:00:00', 'title' => '11月行事食', 'cat' => 'gallery', 'body' => '' ),
	array( 'date' => '2024-10-31 09:00:00', 'title' => '10月行事食', 'cat' => 'gallery', 'body' => '' ),
	array(
		'date'  => '2024-10-01 09:00:00',
		'title' => 'ホームページリニューアル',
		'cat'   => 'news',
		// 新サイトの公開で事実と合わなくなるので下書きで入れる（2026-10-01 新庄さん判断。本文は先方の原文のまま残す）
		'status' => 'draft',
		'body'  => "平素から皆様には大変お世話になっております。\n\nこの度、合併によりホームページをリニューアルしました。\n\n今後も地域社会に食を通じ、貢献できますようスタッフ一同尽力してまいりますので今後とも宜しくお願い致します。\n\n株式会社ＪＩＮｆｏｏｄｓ　スタッフ一同",
	),
	array(
		'date'  => '2024-07-01 09:00:00',
		'title' => '清瀬病院　給食委託厨房業務　開始のお知らせ',
		'cat'   => 'news',
		'body'  => "拝啓　盛夏の候　ますますご清栄のこととお慶び申し上げます。\n\n平素は格別のご高配を賜り厚く御礼申し上げます。\n\nこのたび、別府にある清瀬病院内厨房を委託させていただくこととなりました。これもひとえに皆様方のお力添えのおかげと心より感謝いたしております。\n\n皆様のご期待に添えますよう誠心誠意努力してまいりますので、何卒ご指導ご鞭撻のほどお願い申し上げます。\n\nまずはご通知かたがたご挨拶申し上げます。\n\n敬具\n\n令和6年7月1日\n株式会社ＪＩＮｆｏｏｄｓ\n代表取締役　松下　宏紀",
	),
	array(
		'date'  => '2024-06-06 09:00:00',
		'title' => '会社合併のご案内',
		'cat'   => 'news',
		'body'  => "謹啓　時下益々ご盛栄のこととお慶び申し上げます。\n\n平素は格別のご高配を賜り、厚くお礼申し上げます。\n\nさて、このたび株式会社ＪＩＮと株式会社ＪＩＮｆｏｏｄｓが6月6日をもって合併し、株式会社ＪＩＮｆｏｏｄｓとして発足することになりました。\n\nお客様の信頼を第一に全力を尽くす所存でございますので、引き続きのご支援ご鞭撻を賜りますようお願い申し上げます。\n\nまずはご通知かたがたご挨拶申し上げます。\n\n謹白\n\n令和6年6月6日\n株式会社ＪＩＮｆｏｏｄｓ\n代表取締役社長　松下　宏紀",
	),
);

// 'cat' => 'gallery' の行は、投稿ではなく食事ギャラリー（投稿タイプ gallery）として入れる
foreach ( $posts as $p ) {
	$type  = 'gallery' === $p['cat'] ? 'gallery' : 'post';
	$found = get_posts( array(
		'post_type'   => $type,
		'title'       => $p['title'],
		'post_status' => 'any',
		'numberposts' => 1,
	) );
	if ( $found ) {
		WP_CLI::log( "投稿既存: {$p['title']}" );
		continue;
	}
	$blocks = '';
	$paras  = array_values( array_filter( explode( "\n\n", $p['body'] ) ) );
	$closed = false;
	foreach ( $paras as $para ) {
		// 結語（敬具・謹白）と、そのあとの署名・「〜スタッフ一同」で終わる段落は右寄せ（書状の決まり。DESIGN.md「お知らせの記事ページ」）。
		// エディターの段落の「右寄せ」と同じ形で入れるので、先方が新しく書く挨拶状も同じボタンで直せる
		$is_closing = in_array( $para, array( '敬具', '謹白' ), true );
		$is_right   = $is_closing || $closed || preg_match( '/スタッフ一同$/u', $para );
		$closed     = $closed || $is_closing;
		$blocks    .= $is_right
			? '<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right">' . nl2br( esc_html( $para ) ) . "</p><!-- /wp:paragraph -->\n"
			: '<!-- wp:paragraph --><p>' . nl2br( esc_html( $para ) ) . "</p><!-- /wp:paragraph -->\n";
	}
	$args = array(
		'post_type'    => $type,
		'post_status'  => $p['status'] ?? 'publish',
		'post_title'   => $p['title'],
		'post_date'    => $p['date'],
		'post_content' => $blocks,
	);
	if ( 'post' === $type ) {
		$args['post_category'] = array( $cat_ids[ $p['cat'] ] );
	}
	$id = wp_insert_post( $args );
	WP_CLI::log( "投稿作成: {$p['title']} (ID {$id}・{$type})" );
}

// ---- 3.5 アイキャッチ ----
// テーマ同梱の画像をメディアに取り込み、投稿のアイキャッチに設定する。
// front-page.php のギャラリーはアイキャッチのある投稿だけを出すため
// （代替画像を出さないのは、現行サイトの「壊れた画像」を作らないための方針）
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$thumbs = array(
	'2月行事食'   => 'g-01.jpg',
	'1月行事食'   => 'g-03.jpg',
	'12月行事食'  => 'g-02.jpg',
	'11月行事食'  => 'g-06.jpg',
	'10月行事食'  => 'g-09.jpg',
	// 「新規　委託開始」は写真を付けない（写真が記事の中身と関係ないため。2026-10-01 新庄さん判断）
	'清瀬病院　給食委託厨房業務　開始のお知らせ' => 'g-10.jpg',
);
foreach ( $thumbs as $title => $file ) {
	$found = get_posts( array( 'post_type' => array( 'post', 'gallery' ), 'title' => $title, 'post_status' => 'any', 'numberposts' => 1 ) );
	if ( ! $found ) {
		continue;
	}
	$post_id = $found[0]->ID;
	if ( has_post_thumbnail( $post_id ) ) {
		WP_CLI::log( "アイキャッチ既存: {$title}" );
		continue;
	}
	$src = get_template_directory() . '/assets/images/' . $file;
	if ( ! file_exists( $src ) ) {
		WP_CLI::warning( "画像が見つかりません: {$file}" );
		continue;
	}
	// アップロード先へコピーしてからメディア登録する（元ファイルを移動させない）
	$tmp = wp_tempnam( $file );
	copy( $src, $tmp );
	$attach_id = media_handle_sideload(
		array( 'name' => $file, 'tmp_name' => $tmp ),
		$post_id,
		$title
	);
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "取り込み失敗: {$file} / " . $attach_id->get_error_message() );
		continue;
	}
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $title );
	set_post_thumbnail( $post_id, $attach_id );
	WP_CLI::log( "アイキャッチ設定: {$title} ← {$file}" );
}

// ---- 3.8 受託施設 ----
// 業務案内ページのリストと、2025/04/01 のお知らせ本文から起こしたもの。
// 花埜家はリストに未掲載だった（2025年4月受託開始のお知らせだけが根拠）。受託は【要確認】。
// このメモを「補足」（facility_note）に入れないこと：補足はトップの受託施設の詳細で、種別の後ろに「・」でつないで出る（例：病院・40床）。
// 同一契約先の施設数は facility_count で「3施設」と出るので、補足には書かない
// 「エリア」のタクソノミーは 2026-10-01 に外した（inc/post-types.php）。所在地は facility_area_text だけで持つ
$facilities = array(
	array( 'name' => 'はんだの郷', 'area_text' => '大分市判田', 'count' => 1, 'type' => 'care', 'note' => '' ),
	array( 'name' => 'えがおの丘 いぬかい', 'area_text' => '豊後大野市犬飼', 'count' => 1, 'type' => 'care', 'note' => '' ),
	array( 'name' => '心笑花', 'area_text' => '大分市野津原', 'count' => 1, 'type' => 'care', 'note' => '' ),
	array( 'name' => '風の丘', 'area_text' => '大分市美し野', 'count' => 1, 'type' => 'care', 'note' => '' ),
	array( 'name' => 'とまと／とまと2／特別養護老人ホーム とまとの里', 'area_text' => '大分市大在', 'count' => 3, 'type' => 'care', 'note' => '' ),
	array( 'name' => '清瀬病院', 'area_text' => '別府市野口中町', 'count' => 1, 'type' => 'hospital', 'note' => '40床' ),
	array( 'name' => '有料老人ホーム花埜家', 'area_text' => '大分市明野', 'count' => 1, 'type' => 'care', 'note' => '' ),
);

$order = 0;
foreach ( $facilities as $f ) {
	$order += 10;
	$found = get_posts( array( 'post_type' => 'facility', 'title' => $f['name'], 'post_status' => 'any', 'numberposts' => 1 ) );
	if ( $found ) {
		WP_CLI::log( "受託施設既存: {$f['name']}" );
		continue;
	}
	$id = wp_insert_post( array(
		'post_type'   => 'facility',
		'post_status' => 'publish',
		'post_title'  => $f['name'],
		'menu_order'  => $order,
	) );
	update_post_meta( $id, 'facility_area_text', $f['area_text'] );
	update_post_meta( $id, 'facility_count', $f['count'] );
	update_post_meta( $id, 'facility_type', $f['type'] );
	update_post_meta( $id, 'facility_note', $f['note'] );
	WP_CLI::log( "受託施設作成: {$f['name']} ({$f['area_text']})" );
}

// ---- 4. パーマリンク ----
// 現行は ?p=998 形式だった。移行時は既存URLからの301設計が必要
if ( get_option( 'permalink_structure' ) !== '/%postname%/' ) {
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
	WP_CLI::log( 'パーマリンクを /%postname%/ に変更しました' );
}

// ---- 5. サイト情報 ----
// 社名は LP・フッターと同じ半角表記にそろえる（登記上の正式表記は先方確認待ち）
update_option( 'blogname', '株式会社JINfoods' );
update_option( 'blogdescription', '大分県の給食委託・厨房業務' );

WP_CLI::success( '初期データの投入が終わりました。外観 > テーマ から「JIN foods」を有効化してください。' );

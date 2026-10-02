<?php
/**
 * 投稿タイプとタクソノミー
 *
 * ここはテーマ側で登録する（プラグインで作らない）。
 * ACF や CPT UI の管理画面で作ると定義がDBに入るため、
 *  ・プラグインを止めた瞬間に投稿が管理画面から見えなくなる
 *  ・Git に乗らないので Local と本番でズレる
 * という事故が起きる。フィールドの定義（inc/fields.php）は
 * ACF が無ければ出ないだけで済むので、依存の向きをこの順にしている。
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'jinfoods_register_content' );
function jinfoods_register_content(): void {

	/**
	 * 受託施設
	 *
	 * 現在は「6契約先・8施設」がトップ・会社概要・事業内容の3箇所に
	 * べた書きされている。1箇所で管理して3箇所に出すためのもの。
	 * 単独のページは作らない（archive/single 無し）。一覧に出すだけ
	 */
	register_post_type( 'facility', array(
		'label'               => '受託施設',
		'labels'              => array(
			'name'          => '受託施設',
			'singular_name' => '受託施設',
			'add_new_item'  => '受託施設を追加',
			'edit_item'     => '受託施設を編集',
			'all_items'     => '受託施設一覧',
			'menu_name'     => '受託施設',
		),
		'public'              => false,   // 単独ページを持たせない
		'show_ui'             => true,    // 管理画面には出す
		'show_in_menu'        => true,
		'show_in_rest'        => true,    // ブロックエディタと REST を使う
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-building',
		'supports'            => array( 'title', 'page-attributes' ), // 並び順を手で決められるように
		'has_archive'         => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => false,
	) );

	// 「エリア」のタクソノミーは登録しない（2026-10-01 に外した）。
	// サイトのどこにも出ておらず、地図と一覧は ACF の「所在地の表示」（facility_area_text）を使うため、
	// 同じ情報を2か所に入れさせて管理画面で迷わせていた。市ごとに絞る画面を作るときに改めて足す

	/**
	 * 食事ギャラリー
	 *
	 * 2026-10-01 に投稿のカテゴリ（gallery）から分けた。投稿のままだと
	 *  ・記事の前後リンクが「前のお知らせ／次のお知らせ」になる
	 *  ・管理画面で、お知らせと行事食の写真が同じ「投稿」に混ざる
	 * ため。1件＝写真1枚（アイキャッチ）と題。一覧は /gallery/（archive-gallery.php）、1件ずつのページは single.php。
	 * CPT UI の画面で作り、「ツール → コードを取得」で書き出したものをここに写した（CPT UI 側の定義は消してある。
	 * 両方に残すと同じ名前を二重に登録する）
	 */
	register_post_type( 'gallery', array(
		'label'               => '食事ギャラリー',
		'labels'              => array(
			'name'                  => '食事ギャラリー',
			'singular_name'         => '食事ギャラリー',
			'menu_name'             => '食事ギャラリー',
			'all_items'             => '食事ギャラリー一覧',
			'add_new'               => '新規追加',
			'add_new_item'          => '食事ギャラリーに追加',
			'edit_item'             => '食事ギャラリーを編集',
			'new_item'              => '新しい写真',
			'view_item'             => '表示',
			'view_items'            => '食事ギャラリーを表示',
			'search_items'          => '食事ギャラリーを検索',
			'not_found'             => '見つかりませんでした',
			'not_found_in_trash'    => 'ゴミ箱にはありません',
			// 写真の欄（アイキャッチ）が、この投稿の主役だと分かる名前にする
			'featured_image'        => '写真',
			'set_featured_image'    => '写真を設定',
			'remove_featured_image' => '写真を外す',
			'use_featured_image'    => '写真として使う',
		),
		'public'              => true,
		'show_in_rest'        => true,    // ブロックエディタと REST を使う
		'menu_position'       => 6,       // 「投稿」（お知らせ）のすぐ下
		'menu_icon'           => 'dashicons-format-gallery',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'has_archive'         => true,    // /gallery/
		'rewrite'             => array( 'slug' => 'gallery', 'with_front' => false ),
		'hierarchical'        => false,
	) );
}

/**
 * 食事ギャラリーの題の入力欄に、書き方の例を出す
 */
add_filter( 'enter_title_here', 'jinfoods_gallery_title_placeholder', 10, 2 );
function jinfoods_gallery_title_placeholder( string $text, WP_Post $post ): string {
	return 'gallery' === $post->post_type ? '題（例：2月行事食）' : $text;
}

/**
 * 受託施設の一覧を取り出す。
 * トップ・会社概要・事業内容から同じものを呼ぶ
 *
 * @return WP_Post[]
 */
function jinfoods_get_facilities(): array {
	return get_posts( array(
		'post_type'      => 'facility',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
	) );
}

/**
 * 施設数と契約先数を数える。
 * 「8施設（6契約先）」のような表記を手で直さなくて済むようにする。
 *
 * 1つの契約先で複数の施設を受けている場合があるため
 * （とまと／とまと2／とまとの里 は同一契約先の3施設）、
 * 施設数はフィールドの値を合計する
 *
 * @return array{contracts:int,facilities:int}
 */
function jinfoods_facility_count(): array {
	$posts = jinfoods_get_facilities();
	$facilities = 0;
	foreach ( $posts as $p ) {
		// 1契約先あたりの施設数。未入力なら1として数える
		$n = (int) get_post_meta( $p->ID, 'facility_count', true );
		$facilities += max( $n, 1 );
	}

	return array(
		'contracts'  => count( $posts ),
		'facilities' => $facilities,
	);
}

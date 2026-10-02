<?php
/**
 * テンプレートから呼ぶヘルパー
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * サムネイルのURLを返す。無い場合は代替画像を返す。
 *
 * 現行サイトは代替画像として
 * themes/twentytwelve/common/images/noimg/logo-f.png を参照していたが、
 * そのファイルが存在せず 404 になり、トップページに壊れた画像が出ていた。
 * ここでは必ず存在を確認してから返し、無ければ空を返して
 * 呼び出し側で「画像の枠ごと出さない」判断ができるようにする。
 *
 * @return string 画像URL。代替も無ければ空文字
 */
function jinfoods_thumb_url( int $post_id = 0, string $size = 'jinfoods_square' ): string {
	$post_id = $post_id ?: get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	// 代替画像。実体があるときだけ返す
	$fallback_rel = '/assets/images/noimage.jpg';
	if ( file_exists( JINFOODS_DIR . $fallback_rel ) ) {
		return JINFOODS_URI . $fallback_rel;
	}

	return '';
}

/**
 * サムネイルの alt。設定が無ければ投稿タイトルを使う
 */
function jinfoods_thumb_alt( int $post_id = 0 ): string {
	$post_id = $post_id ?: get_the_ID();
	$thumb_id = get_post_thumbnail_id( $post_id );

	if ( $thumb_id ) {
		$alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		if ( $alt ) {
			return $alt;
		}
	}
	return get_the_title( $post_id );
}

/**
 * 投稿の代表カテゴリ名を返す（お知らせ／食事ギャラリー の表示に使う）。
 * 食事ギャラリーは投稿タイプ（カテゴリを持たない）なので、投稿タイプの名前を返す
 */
function jinfoods_primary_category( int $post_id = 0 ): string {
	$post_id = $post_id ?: get_the_ID();
	if ( 'gallery' === get_post_type( $post_id ) ) {
		return '食事ギャラリー';
	}
	$terms = get_the_category( $post_id );
	return $terms ? $terms[0]->name : 'お知らせ';
}

/**
 * ページ見出しに出す情報を、表示中のページから決める。
 *
 * 現行サイトはページ見出しを「文字を焼き込んだ画像」で出していて、
 * alt も空だった（m002.png 800x400 / m004.png 1439x719 とサイズもバラバラ）。
 * ここでは背景画像と文字を分け、文字はHTMLで組む。
 *
 * @return array{en:string,ja:string,lead:string,bg:string}
 */
/**
 * AI で生成した写真かどうか（2026-10-01）。生成画像には「イメージ」の札（c-image-note）を出す。
 * 撮影か実写の差し替えが済んだら、ここから外す（DESIGN.md「生成画像の表記」）
 */
function jinfoods_is_generated_image( string $url ): bool {
	$generated = array(
		'about-main.jpg',
		'strength-1.jpg',
		'strength-4.jpg',
		'recruit.jpg',
		'head-about.jpg',
		'head-service.jpg',
		'head-recruit.jpg',
		'head-contact.jpg',
		'head-news.jpg',
	);
	return in_array( basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ), $generated, true );
}

function jinfoods_pagehead(): array {
	$default_bg = JINFOODS_URI . '/assets/images/head-about.jpg';

	// 固定ページはスラッグで見分ける
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$map = array(
			'about'   => array( 'Company', '会社概要', '食で人と地域をつなぐ。大分県内の高齢者施設・病院に、毎日の食事をお届けしています。', 'head-about.jpg' ),
			'service' => array( 'Service', '事業内容', '大分県内の高齢者施設・病院の厨房業務を受託し、施設の中で毎日の食事を調製・提供しています。', 'head-service.jpg' ),
			'recruit' => array( 'Recruit', '採用情報', '調理スタッフ・調理補助を募集しています。未経験の方も、週末のみの勤務も歓迎します。', 'head-recruit.jpg' ),
			'contact' => array( 'Contact', 'お問い合わせ', '業務のご相談、試食のご依頼、お見積り、採用のご応募などをお受けしています。', 'head-contact.jpg' ),
			'privacy' => array( 'Privacy policy', 'プライバシーポリシー', '当サイトにおける個人情報の取り扱いについて。', 'head-about.jpg' ),
		);
		if ( isset( $map[ $slug ] ) ) {
			[ $en, $ja, $lead, $bg ] = $map[ $slug ];
			return array(
				'en'   => $en,
				'ja'   => $ja,
				'lead' => $lead,
				'bg'   => JINFOODS_URI . '/assets/images/' . $bg,
			);
		}
		return array(
			'en'   => 'Page',
			'ja'   => get_the_title(),
			'lead' => '',
			'bg'   => $default_bg,
		);
	}

	if ( jinfoods_is_gallery() ) {
		return array(
			'en'   => 'Gallery',
			'ja'   => '食事ギャラリー',
			'lead' => '施設でお出ししている毎日の献立と、季節の行事食をご紹介します。',
			'bg'   => JINFOODS_URI . '/assets/images/head-gallery.jpg',
		);
	}

	if ( is_search() ) {
		return array(
			'en'   => 'Search',
			'ja'   => '検索結果',
			'lead' => sprintf( '「%s」の検索結果です。', get_search_query() ),
			'bg'   => JINFOODS_URI . '/assets/images/head-news.jpg',
		);
	}

	if ( is_404() ) {
		return array(
			'en'   => 'Not found',
			'ja'   => 'ページが見つかりません',
			'lead' => 'お探しのページは移動または削除された可能性があります。',
			'bg'   => JINFOODS_URI . '/assets/images/head-news.jpg',
		);
	}

	// お知らせ一覧・記事詳細
	return array(
		'en'   => 'News',
		'ja'   => 'お知らせ',
		'lead' => '新規受託のご報告など、JINfoodsからのお知らせです。',
		'bg'   => JINFOODS_URI . '/assets/images/head-news.jpg',
	);
}

/**
 * お知らせの記事ページかどうか（「投稿」の1件のページ。食事ギャラリーは 2026-10-01 から別の投稿タイプ）。
 * ページ見出し（template-parts/pagehead.php）と記事詳細（single.php）は、必ずこの判定を使う
 * （別々に書くと、添付ファイルのページ＝single.php を使うが投稿ではないページで h1 が2つになる）
 */
function jinfoods_is_news_single(): bool {
	return is_singular( 'post' );
}

/**
 * 記事の写真（アイキャッチ）を出力する。
 * 代替画像は出さない。無いときは枠ごと出さないことで、現行サイトのような「壊れた画像」を作らない
 *
 * @param bool $lazy 画面の下の方に置くとき true（お知らせの記事は本文のあとに置く）
 */
function jinfoods_entry_media( bool $lazy = false ): void {
	if ( ! has_post_thumbnail() ) {
		return;
	}
	$attr = array(
		'class' => 'p-entry__img',
		'alt'   => jinfoods_thumb_alt(),
	);
	if ( $lazy ) {
		$attr['loading'] = 'lazy';
	}
	echo '<div class="p-entry__media">';
	the_post_thumbnail( 'jinfoods_entry', $attr );
	echo '</div>';
}

/**
 * パンくずの項目（ラベルと URL）を、表示中のページから組み立てる。
 * 画面のパンくず（jinfoods_breadcrumb）と構造化データ（inc/schema.php の BreadcrumbList）が同じ項目を使う（2026-10-02）
 *
 * @return array<int, array{label:string,url:string}>
 */
function jinfoods_breadcrumb_items(): array {
	$items = array( array( 'label' => 'トップ', 'url' => home_url( '/' ) ) );

	if ( is_singular( 'post' ) ) {
		$term = get_category_by_slug( 'news' );
		$items[] = array( 'label' => 'お知らせ', 'url' => $term ? get_category_link( $term->term_id ) : '' );
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_singular( 'gallery' ) ) {
		$items[] = array( 'label' => '食事ギャラリー', 'url' => get_post_type_archive_link( 'gallery' ) );
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_post_type_archive( 'gallery' ) ) {
		$items[] = array( 'label' => '食事ギャラリー', 'url' => '' );
	} elseif ( is_page() ) {
		$parent_id = wp_get_post_parent_id( get_queried_object_id() );
		if ( $parent_id ) {
			$items[] = array( 'label' => get_the_title( $parent_id ), 'url' => get_permalink( $parent_id ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_category() ) {
		$items[] = array( 'label' => single_cat_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$items[] = array( 'label' => '検索結果', 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'label' => 'ページが見つかりません', 'url' => '' );
	} else {
		$items[] = array( 'label' => 'お知らせ', 'url' => '' );
	}
	return $items;
}

/**
 * パンくずを出力する。
 * 区切り記号は CSS の擬似要素で描くので、ここでは文字として入れない
 * （読み上げで「大なり」と読まれるのを避ける）
 *
 * @param string $modifier 明るい地に置くときは 'c-breadcrumb--on-light'（お知らせの記事ページの薄い見出し）
 */
function jinfoods_breadcrumb( string $modifier = '' ): void {
	$items = jinfoods_breadcrumb_items();

	// 順番のある並びなので ol > li（2026-10-02、hallmark check P3。静的版と同じ形）
	printf( '<nav class="%s" aria-label="現在位置"><ol class="c-breadcrumb__list">', esc_attr( trim( 'c-breadcrumb ' . $modifier ) ) );
	foreach ( $items as $i => $item ) {
		$is_last = ( $i === count( $items ) - 1 );
		echo '<li class="c-breadcrumb__item">';
		if ( $is_last || ! $item['url'] ) {
			printf(
				'<span class="c-breadcrumb__current" aria-current="page">%s</span>',
				esc_html( $item['label'] )
			);
		} else {
			printf(
				'<a class="c-breadcrumb__link" href="%s">%s</a>',
				esc_url( $item['url'] ),
				esc_html( $item['label'] )
			);
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

/**
 * 電話番号。1箇所で持って全テンプレートから使う
 */
function jinfoods_tel(): array {
	// サイト設定（ACFのオプションページ）から取る。未設定なら既定値
	$display = jinfoods_setting( 'tel', '097-574-9225' );
	$note    = jinfoods_setting( 'tel_note', '6:00〜19:00／定休日なし' );

	return array(
		'display' => $display,
		// tel: は数字だけにする。ハイフン入りでも電話アプリが誤解しないように
		'href'    => 'tel:' . preg_replace( '/[^0-9+]/', '', $display ),
		'note'    => $note,
	);
}

/**
 * 働き方の条件（トップの採用の節と、採用ページの頭）。値はサイト設定 > 採用情報
 *
 * @param string $modifier 採用ページでは 'p-recruit__conds--page'（見出しの下線と線が2本並ぶのを避ける）
 */
function jinfoods_recruit_conds_html( string $modifier = '' ): string {
	$items = '';
	foreach ( jinfoods_recruit_defs()['conds'] as $i => [ $big, $small ] ) {
		$row = jinfoods_recruit_field( 'recruit_cond_' . ( $i + 1 ), array( 'big' => $big, 'small' => $small ) );
		if ( '' === trim( $row['big'] ) ) {
			continue;
		}
		$items .= sprintf(
			'<li class="p-recruit__cond"><span class="%s">%s</span>%s</li>',
			0 === $i ? 'p-recruit__cond-big p-recruit__cond-big--lead' : 'p-recruit__cond-big',
			esc_html( $row['big'] ),
			'' !== trim( $row['small'] ) ? '<span class="p-recruit__cond-small">' . esc_html( $row['small'] ) . '</span>' : ''
		);
	}
	if ( '' === $items ) {
		return '';
	}
	return sprintf( '<ul class="%s">%s</ul>', esc_attr( trim( 'p-recruit__conds ' . $modifier ) ), $items );
}

/**
 * 募集要項の表（採用ページ）。空の項目は消さずに「ご記入をお願いします」と出す
 */
function jinfoods_recruit_requirements_html(): string {
	$rows = '';
	foreach ( jinfoods_recruit_defs()['reqs'] as $slug => [ $label, $value, $note ] ) {
		$row   = jinfoods_recruit_field( "recruit_req_{$slug}", array( 'value' => $value, 'note' => $note ) );
		$empty = '' === trim( $row['value'] );
		$val   = $empty
			? '<span class="p-recruit__todo">（ご記入をお願いします）</span>'
			: nl2br( esc_html( trim( $row['value'] ) ), false );
		if ( '' !== trim( $row['note'] ) ) {
			$val .= ( $empty ? '' : '<br>' ) . '<span class="c-deflist__note">' . esc_html( $row['note'] ) . '</span>';
		}
		$rows .= sprintf(
			'<div class="%s"><dt class="c-deflist__key">%s</dt><dd class="c-deflist__val">%s</dd></div>',
			$empty ? 'c-deflist__row p-recruit__todo-row' : 'c-deflist__row',
			esc_html( $label ),
			$val
		);
	}
	return '<dl class="c-deflist">' . $rows . '</dl>';
}

// 採用ページの本文（固定ページ）から呼ぶ。export-content.py が静的版の該当箇所をこのショートコードに置き換える
add_shortcode( 'jinfoods_recruit_conds', static fn() => jinfoods_recruit_conds_html( 'p-recruit__conds--page' ) );
add_shortcode( 'jinfoods_recruit_requirements', 'jinfoods_recruit_requirements_html' );

<?php
/**
 * 構造化データ（JSON-LD）
 *
 * 2026-10-02 追加（hallmark check P3。静的版の各ページの head と同じ形）。値は画面に出ているものだけを使う。
 *  ・トップと会社概要：Organization（社名・所在地・電話・設立日。url はトップ）
 *  ・トップ以外：BreadcrumbList（画面のパンくずと同じ項目。jinfoods_breadcrumb_items）
 *  ・お知らせの記事：NewsArticle（題・日付は画面と同じ）
 * 営業時間は入れていない。確認のお願いの17（電話を受けられる時間）の答えが来るまで、検索エンジン向けには出さない
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 会社の情報。所在地・電話は会社概要の表と同じ（変えるときは両方）
 */
function jinfoods_schema_organization(): array {
	$tel = jinfoods_tel();
	return array(
		'@type'       => 'Organization',
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => JINFOODS_URI . '/assets/images/logo.png',
		// 国際形式（097-574-9225 → +81-97-574-9225）
		'telephone'   => '+81-' . ltrim( $tel['display'], '0' ),
		'foundingDate' => '2021-02-05',
		'address'     => array(
			'@type'           => 'PostalAddress',
			'postalCode'      => '870-0822',
			'addressRegion'   => '大分県',
			'addressLocality' => '大分市',
			'streetAddress'   => '大道町5丁目3-32',
			'addressCountry'  => 'JP',
		),
	);
}

add_action( 'wp_head', 'jinfoods_print_schema', 20 );
function jinfoods_print_schema(): void {
	$graph = array();

	if ( is_front_page() || is_page( 'about' ) ) {
		$graph[] = jinfoods_schema_organization();
	}

	if ( ! is_front_page() ) {
		$list = array();
		foreach ( jinfoods_breadcrumb_items() as $i => $item ) {
			// 最後（今のページ）は URL を持たないので、今のページの URL を入れる
			$url = $item['url'] ? $item['url'] : ( is_singular() || is_page() ? get_permalink() : '' );
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['label'],
			);
			if ( $url ) {
				$entry['item'] = $url;
			}
			$list[] = $entry;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list,
		);
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'            => 'NewsArticle',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( 'Y-m-d' ),
			'mainEntityOfPage' => get_permalink(),
			'publisher'        => array(
				'@id'   => home_url( '/#organization' ),
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);
	}

	if ( ! $graph ) {
		return;
	}
	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	);
}

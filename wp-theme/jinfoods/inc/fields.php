<?php
/**
 * カスタムフィールドの定義（ACF）
 *
 * ACF の管理画面で作るとDBに入って Git に乗らないため、PHP で登録する。
 * 実運用では ACF の画面で作ってから「PHPとして書き出し」してここへ貼るのが速い。
 *
 * ACF が無効でも投稿タイプ自体は生きている（inc/post-types.php がテーマ側にあるため）。
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'jinfoods_register_fields' );
function jinfoods_register_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ---- 受託施設 ----
	acf_add_local_field_group( array(
		'key'      => 'group_jinfoods_facility',
		'title'    => '受託施設の情報',
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'facility',
		) ) ),
		'position' => 'normal',
		'fields'   => array(
			array(
				'key'          => 'field_facility_area_text',
				'label'        => '所在地の表示',
				'name'         => 'facility_area_text',
				'type'         => 'text',
				'instructions' => '「市＋地区」を空白なしで入力。トップの一覧と地図の点の位置に使います。例）大分市判田。地図に登録の無い地区は点が出ず、地図の下の「ほかに」に並びます（地区を足すときは制作側へ）',
				'required'     => 1,
			),
			array(
				'key'           => 'field_facility_count',
				'label'         => 'この契約先の施設数',
				'name'          => 'facility_count',
				'type'          => 'number',
				'instructions'  => '1つの契約先で複数の施設を受けている場合はその数。例）とまと／とまと2／とまとの里 なら 3',
				'default_value' => 1,
				'min'           => 1,
			),
			array(
				'key'          => 'field_facility_type',
				'label'        => '種別',
				'name'         => 'facility_type',
				'type'         => 'select',
				'choices'      => array(
					'care'     => '高齢者施設',
					'hospital' => '病院',
					'company'  => '企業・社員食堂',
				),
				'default_value' => 'care',
			),
			array(
				'key'          => 'field_facility_note',
				'label'        => '補足',
				'name'         => 'facility_note',
				'type'         => 'text',
				'instructions' => '任意。規模などの短い語。例）40床。トップの受託施設の詳細で種別の後ろにつないで出る（病院・40床）。社内向けのメモや、施設数（上の欄で出る）は書かない',
			),
		),
	) );

	// ---- サイト設定（オプションページ）----
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => 'サイト設定',
			'menu_title' => 'サイト設定',
			'menu_slug'  => 'jinfoods-settings',
			'capability' => 'manage_options',
			'position'   => 22,
			'icon_url'   => 'dashicons-admin-settings',
			'redirect'   => false,
		) );

		acf_add_local_field_group( array(
			'key'      => 'group_jinfoods_settings',
			'title'    => 'サイト共通の情報',
			'location' => array( array( array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'jinfoods-settings',
			) ) ),
			'fields'   => array(
				array(
					'key'           => 'field_tel',
					'label'         => '電話番号',
					'name'          => 'tel',
					'type'          => 'text',
					'default_value' => '097-574-9225',
					'instructions'  => 'ハイフン付きで入力。tel: リンクは自動で作ります',
				),
				array(
					'key'           => 'field_tel_note',
					'label'         => '電話の注記',
					'name'          => 'tel_note',
					'type'          => 'text',
					'default_value' => '6:00〜19:00／定休日なし',
				),
				array(
					'key'           => 'field_staff_count',
					'label'         => 'スタッフ数',
					'name'          => 'staff_count',
					'type'          => 'number',
					'default_value' => 50,
				),
				array(
					'key'           => 'field_staff_note',
					'label'         => 'スタッフ数の注記',
					'name'          => 'staff_note',
					'type'          => 'text',
					'default_value' => 'パート・アルバイトを含む',
					'instructions'  => '「◯年◯月現在」を入れる場合はここへ',
				),
				array(
					'key'           => 'field_license',
					'label'         => '有資格者',
					'name'          => 'license',
					'type'          => 'text',
					'default_value' => '栄養士・管理栄養士・調理師',
				),
				array(
					'key'           => 'field_hours',
					'label'         => '営業時間',
					'name'          => 'hours',
					'type'          => 'text',
					'default_value' => '6:00〜19:00',
				),
			),
		) );

		// ---- 採用情報（サイト設定の下）----
		// 給与は最低賃金の改定（毎年秋）で必ず直すため、本文の HTML ではなく入力欄で持つ。
		// 無料版の ACF には繰り返し欄が無いので、行は固定で作る。項目名（募集職種 など）は募集時の明示事項なので変えさせない
		acf_add_options_sub_page( array(
			'page_title'  => '採用情報',
			'menu_title'  => '採用情報',
			'menu_slug'   => 'jinfoods-recruit',
			'parent_slug' => 'jinfoods-settings',
			'capability'  => 'manage_options',
		) );

		$defs   = jinfoods_recruit_defs();
		$fields = array(
			array(
				'key'   => 'field_recruit_tab_conds',
				'label' => '働き方の条件',
				'type'  => 'tab',
			),
			array(
				'key'     => 'field_recruit_conds_msg',
				'label'   => '',
				'type'    => 'message',
				'message' => 'トップの採用の節と、採用ページの頭に同じものが出ます。1行目だけ大きく朱で出ます。「大きく出す語」が空の行は出しません',
			),
		);
		foreach ( $defs['conds'] as $i => [ $big, $small ] ) {
			$n = $i + 1;
			$fields[] = array(
				'key'        => "field_recruit_cond_{$n}",
				'label'      => "{$n}行目",
				'name'       => "recruit_cond_{$n}",
				'type'       => 'group',
				'layout'     => 'row',
				'sub_fields' => array(
					array(
						'key'           => "field_recruit_cond_{$n}_big",
						'label'         => '大きく出す語',
						'name'          => 'big',
						'type'          => 'text',
						'default_value' => $big,
					),
					array(
						'key'           => "field_recruit_cond_{$n}_small",
						'label'         => '小さく添える語',
						'name'          => 'small',
						'type'          => 'text',
						'default_value' => $small,
					),
				),
			);
		}
		$fields[] = array(
			'key'   => 'field_recruit_tab_reqs',
			'label' => '募集要項',
			'type'  => 'tab',
		);
		$fields[] = array(
			'key'     => 'field_recruit_reqs_msg',
			'label'   => '',
			'type'    => 'message',
			'message' => '採用ページの「募集要項」の表に出ます。改行はそのまま改行になります。空にした項目は「（ご記入をお願いします）」と黄色で出ます（募集時に明示が必要な項目のため、行ごと消さない）',
		);
		foreach ( $defs['reqs'] as $slug => [ $label, $value, $note ] ) {
			$fields[] = array(
				'key'        => "field_recruit_req_{$slug}",
				'label'      => $label,
				'name'       => "recruit_req_{$slug}",
				'type'       => 'group',
				'layout'     => 'row',
				'sub_fields' => array(
					array(
						'key'           => "field_recruit_req_{$slug}_value",
						'label'         => '内容',
						'name'          => 'value',
						'type'          => 'textarea',
						'rows'          => 2,
						'new_lines'     => '',
						'default_value' => $value,
					),
					array(
						'key'           => "field_recruit_req_{$slug}_note",
						'label'         => '注記',
						'name'          => 'note',
						'type'          => 'text',
						'instructions'  => '任意。小さい灰色の字で下に出ます',
						'default_value' => $note,
					),
				),
			);
		}

		acf_add_local_field_group( array(
			'key'      => 'group_jinfoods_recruit',
			'title'    => '採用情報',
			'location' => array( array( array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'jinfoods-recruit',
			) ) ),
			'fields'   => $fields,
		) );
	}
}

/**
 * 採用情報の既定値。ACF が無い・一度も保存していないときはこれを出す。
 * 値の正本は静的版の recruit.html / index.html。静的版を直したらここも合わせる
 *
 * @return array{conds: list<array{0:string,1:string}>, reqs: array<string, array{0:string,1:string,2:string}>}
 */
function jinfoods_recruit_defs(): array {
	return array(
		'conds' => array(
			array( '6:00〜19:00', 'の間でシフト応相談' ),
			array( '正社員／パート・アルバイト', '' ),
			array( '週末のみ・平日のみ', 'の勤務も歓迎' ),
			array( '未経験・学生の方も歓迎', '／資格保有者は優遇' ),
			array( '交通費手当・資格手当', 'あり' ),
		),
		'reqs'  => array(
			'job'      => array( '募集職種', "① 正社員（調理スタッフ）\n② パート・アルバイト（調理補助）", '' ),
			'work'     => array( '仕事内容', '各受託施設内での調理・盛り付け・配膳・洗浄などの厨房業務', '' ),
			'number'   => array( '募集人数', '各若干名', '規定の人数に達した場合は募集を終了いたします' ),
			'qualify'  => array( '応募資格', "年齢・性別不問／経験者・資格保有者は優遇\n未経験の方・学生の方も歓迎します", '' ),
			'type'     => array( '雇用形態', '', '正社員／パート・アルバイトの別を明記します' ),
			'term'     => array( '契約期間', '', '期間の定めの有無。定めがある場合は期間と更新の有無' ),
			'place'    => array( '就業場所', '各受託施設内（大分市・豊後大野市・別府市）', '配属先は面接時にご相談のうえ決定します' ),
			'hours'    => array( '就業時間', "6:00〜19:00の間でシフト制（応相談）\n週末のみ・平日のみの勤務も歓迎します", '' ),
			'holiday'  => array( '休日', '月10日程度', '' ),
			'salary'   => array( '給与', "① 月給 205,000円＋諸手当\n② 時給 6:00〜9:00 1,100円／9:00〜19:00 1,000円", '日曜・祝日は20円UP／研修期間あり' ),
			'allow'    => array( '諸手当の内訳', '', '固定残業代を含む場合は、時間数と金額の明示が必要です' ),
			'insure'   => array( '加入保険', '各種保険あり', '健康保険・厚生年金・雇用保険・労災保険の加入範囲を明記します' ),
			'other'    => array( 'その他の手当', '交通費手当あり／資格手当あり', '' ),
			'smoke'    => array( '受動喫煙防止措置', '', '2020年4月から募集時の明示が必須の項目です（屋内禁煙／喫煙専用室あり など）' ),
		),
	);
}

/**
 * 採用情報の1行を取る。一度も保存していなければ既定値、保存して空なら空のまま返す
 * （空にした＝未記入として出したいので、jinfoods_setting() のように既定値へ戻さない）
 *
 * @param array<string,string> $default 例）array( 'big' => '…', 'small' => '…' )
 * @return array<string,string>
 */
function jinfoods_recruit_field( string $name, array $default ): array {
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $name, 'option' );
		if ( is_array( $v ) ) {
			return array_map( 'strval', $v + $default );
		}
	}
	return $default;
}

/**
 * サイト設定の値を取る。ACFが無い・未入力でも既定値で動くようにする
 */
function jinfoods_setting( string $key, string $default = '' ): string {
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $key, 'option' );
		if ( $v !== null && $v !== '' ) {
			return (string) $v;
		}
	}
	return $default;
}

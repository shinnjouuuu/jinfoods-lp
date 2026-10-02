<?php
/**
 * トップページ（LP）
 *
 * お知らせと食事ギャラリーは WP_Query で出す。それ以外は
 * 更新頻度が低いのでテンプレートに置く（数値や受託施設は
 * 変わったときに1箇所直せば済むよう、この先 ACF に逃がす想定）
 *
 * @package jinfoods
 */
get_header();
?>


  <!-- ============================================================
       FV（2026-09-30 作り直し・2回目。静的版 index.html と同じ）
       縦組みで、右から 受託の一文 → 見出し → ボタン2つ。その左右の奥に実写の料理・行事食の写真の層
       （生成画像は FV に出さない）。理由は ../../sass/object/project/_fv.scss の冒頭
       ============================================================ -->
  <section class="p-fv js-fv" aria-labelledby="fv-title">
    <?php // 写真の層（湯気の層）：実写の料理・行事食9枚。位置は _fv.scss の表、3D は assets/js/fv-steam.js ?>
    <div class="p-fv__steam js-fv-steam" aria-hidden="true">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-01.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-02.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-03.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-04.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-05.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-06.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-07.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-08.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
      <img class="p-fv__steam-img js-fv-steam-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/fv/fv-09.jpg' ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
    </div>
    <script>
      // 3D を試せるときだけ、CSS の写真を隠して待つ（js/fv-steam.js が 3D にするか、だめなら戻す）
      (function (el) {
        var m = window.matchMedia, c = navigator.connection;
        if (m && !m('(prefers-reduced-motion: reduce)').matches && !(c && c.saveData)) el.classList.add('is-pending');
      })(document.querySelector('.js-fv-steam'));
    </script>
    <div class="p-fv__inner l-inner">
      <div class="p-fv__type">
        <p class="p-fv__offer">大分県で高齢者施設・病院の給食を受託しています</p>
        <h1 class="p-fv__title" id="fv-title">食で、<br>人と<span class="p-fv__title-accent">地域</span>をつなぐ。</h1>
      </div>
      <div class="c-btn-group p-fv__cta">
        <a class="c-btn c-btn--primary js-anchor" data-cta="contact-fv" href="#contact">お問い合わせ<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--text js-anchor" href="#service">事業内容を見る<span class="c-arrow-line" aria-hidden="true"></span></a>
      </div>
    </div>
  </section>

  <!-- ============================================================
       ABOUT
       ============================================================ -->
  <!-- 2026-09-30 コンペ C案「入口を3つ」。上で何の会社かを言い、下で会社概要ページの3つの中身へ送る。
       本文の2段落目（管理栄養士・きざみ食…）は「4つの強み」01・02 と同じ内容なので外した（LP と同じ） -->
  <section class="l-section l-section--wave p-about" id="about" aria-labelledby="about-title">
    <div class="p-about__inner l-inner">
      <div class="p-about__head">
        <div class="p-about__body u-reveal js-reveal">
          <div class="c-heading">
            <p class="c-heading__eyebrow" aria-hidden="true">About</p>
            <h2 class="c-heading__title" id="about-title"><span class="p-about__phrase">毎日の食事を、</span><span class="p-about__phrase">施設の中でつくる。</span></h2>
          </div>
          <p class="p-about__text">
            株式会社JINfoodsは、大分県内の高齢者施設・病院の厨房業務を受託し、施設の中で毎日の食事を調製・提供しています。
          </p>
        </div>

        <div class="p-about__photos u-reveal js-reveal">
          <div class="c-media c-media--4x3">
            <?php // lazy にしない：FV を中身の高さにしたので、SP・タブレットでは最初の画面に入る ?>
            <img class="c-media__img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/about-main.jpg' ); ?>" alt="厨房で調理をするスタッフ" width="640" height="480"><span class="c-image-note">イメージ</span>
          </div>
          <div class="c-media c-media--4x3">
            <img class="c-media__img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/about-sub.jpg' ); ?>" alt="配膳されたトレー" width="560" height="420" loading="lazy">
          </div>
        </div>
      </div>

      <?php // 会社概要ページの #team / #area / #profile へ。拠点の入口だけ地図を縮めた絵にして予告する ?>
      <ul class="p-about__doors">
        <li>
          <a class="p-about__door" href="<?php echo esc_url( home_url( '/about/#team' ) ); ?>">
            <svg class="p-about__door-icon" viewBox="0 0 48 48" aria-hidden="true"><circle cx="17" cy="16" r="6"/><circle cx="32" cy="16" r="6"/><path d="M6 38c0-7 5-12 11-12s11 5 11 12M21 38c0-7 5-12 11-12s10 5 10 12"/></svg>
            <span class="p-about__door-text">
              <span class="p-about__door-title">施設に入る体制</span>
              <span class="p-about__door-note">管理栄養士・調理師・調理スタッフ</span>
            </span>
            <span class="p-about__door-arrow" aria-hidden="true">→</span>
          </a>
        </li>
        <li>
          <a class="p-about__door" href="<?php echo esc_url( home_url( '/about/#area' ) ); ?>">
            <span class="p-about__door-map" aria-hidden="true">
              <svg class="p-about__door-map-svg" viewBox="-150 -120 300 330"><circle r="110" class="p-about__door-map-ring"/><circle r="220" class="p-about__door-map-ring"/><circle cx="-101" cy="-68" r="16" class="p-about__door-map-dot"/><circle cx="126" cy="-21" r="16" class="p-about__door-map-dot"/><circle cx="38" cy="79" r="16" class="p-about__door-map-dot"/><circle cx="-71" cy="77" r="16" class="p-about__door-map-dot"/><circle cx="50" cy="182" r="16" class="p-about__door-map-dot"/><circle r="18" class="p-about__door-map-hq"/></svg>
            </span>
            <span class="p-about__door-text">
              <span class="p-about__door-title">拠点と受託エリア</span>
              <span class="p-about__door-note">大分市・豊後大野市・別府市</span>
            </span>
            <span class="p-about__door-arrow" aria-hidden="true">→</span>
          </a>
        </li>
        <li>
          <a class="p-about__door" href="<?php echo esc_url( home_url( '/about/#profile' ) ); ?>">
            <svg class="p-about__door-icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M12 6h17l9 9v27H12z"/><path d="M29 6v9h9M18 24h14M18 31h14M18 38h9"/></svg>
            <span class="p-about__door-text">
              <span class="p-about__door-title">会社概要・沿革</span>
              <span class="p-about__door-note">所在地・設立・従業員数</span>
            </span>
            <span class="p-about__door-arrow" aria-hidden="true">→</span>
          </a>
        </li>
      </ul>
    </div>
  </section>

  <!-- ============================================================
       STATS：委託検討者が最初に見たい「実績」なので強みより前に置く
       数値はすべて公開情報から検証済み。未確認のものは置いていない
       ============================================================ -->
  <?php
  // 2026-09-30 コンペ D案「B の並び × C の見た目」（LP と同じ見た目）。数字と稼働時間は管理画面の値から出す。
  // 稼働時間の帯は 0:00〜24:00 の1日を表し、稼働している範囲だけを塗る。
  // 帯の両端を開始・終了にすると、端まで塗られて24時間稼働に見えるため（2026-10-01）。
  // 目盛りは 0:00・開始・終了・24:00 で、位置は帯の左端からの%
  $jinfoods_hours = (string) jinfoods_setting( 'hours', '6:00〜19:00' );
  $jinfoods_ticks = array();
  $jinfoods_span  = null;
  if ( preg_match( '/^(\d{1,2}):(\d{2})\s*[〜~～-]\s*(\d{1,2}):(\d{2})$/u', trim( $jinfoods_hours ), $m ) ) {
    $from = (int) $m[1] + (int) $m[2] / 60;
    $to   = (int) $m[3] + (int) $m[4] / 60;
    if ( $to > $from && $to <= 24 ) {
      $jinfoods_span  = array( round( $from / 24 * 100, 2 ), round( $to / 24 * 100, 2 ) );
      $jinfoods_ticks = array(
          array( '0:00', 0, true ),
          array( "{$m[1]}:{$m[2]}", $jinfoods_span[0], false ),
          array( "{$m[3]}:{$m[4]}", $jinfoods_span[1], false ),
          array( '24:00', 100, true ),
      );
    }
  }
  $jinfoods_count = jinfoods_facility_count();
  ?>
  <section class="l-section l-section--paper l-section--wave p-stats" id="stats" aria-labelledby="stats-title">
    <div class="p-stats__bg js-parallax" aria-hidden="true">
      <img class="p-stats__bg-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/stats-bg.jpg' ); ?>" alt="" width="1600" height="500" loading="lazy">
    </div>
    <div class="p-stats__scrim" aria-hidden="true"></div>

    <div class="p-stats__inner l-inner l-section__body">
      <div class="c-heading c-heading--center u-reveal js-reveal">
        <p class="c-heading__eyebrow" aria-hidden="true">Data</p>
        <h2 class="c-heading__title" id="stats-title">数字で見るJINfoods</h2>
      </div>

      <div class="p-stats__day u-reveal js-reveal">
        <p class="p-stats__day-head">
          <span class="p-stats__label">稼働時間</span>
          <span class="p-stats__time"><?php echo esc_html( $jinfoods_hours ); ?></span>
          <span class="p-stats__note">定休日なし</span>
        </p>
        <?php if ( $jinfoods_span ) : ?>
          <div class="p-stats__bar js-stats-bar" role="img" aria-label="<?php echo esc_attr( '1日のうち ' . $jinfoods_hours . ' に稼働' ); ?>" style="<?php echo esc_attr( '--from: ' . $jinfoods_span[0] . '; --to: ' . $jinfoods_span[1] . ';' ); ?>">
            <span class="p-stats__bar-fill"></span>
            <span class="p-stats__ticks" aria-hidden="true"><?php foreach ( $jinfoods_ticks as $t ) : ?><span class="p-stats__tick<?php echo $t[2] ? ' p-stats__tick--edge' : ''; ?>" style="<?php echo esc_attr( '--p: ' . $t[1] . ';' ); ?>"><?php echo esc_html( $t[0] ); ?></span><?php endforeach; ?></span>
          </div>
        <?php endif; ?>
      </div>

      <ul class="p-stats__facts u-reveal js-reveal">
        <li class="p-stats__fact">
          <p class="p-stats__label">受託施設</p>
          <p class="p-stats__value"><span class="u-vh"><?php echo esc_html( (string) $jinfoods_count['facilities'] ); ?>施設</span><span class="p-stats__num js-count" data-count-to="<?php echo esc_attr( (string) $jinfoods_count['facilities'] ); ?>" aria-hidden="true"><?php echo esc_html( (string) $jinfoods_count['facilities'] ); ?></span><span class="p-stats__unit" aria-hidden="true">施設</span></p>
          <p class="p-stats__note"><?php echo esc_html( $jinfoods_count['contracts'] . '契約先（うち病院1・40床）' ); ?></p>
        </li>
        <li class="p-stats__fact">
          <p class="p-stats__label">スタッフ</p>
          <p class="p-stats__value"><span class="u-vh"><?php echo esc_html( jinfoods_setting( 'staff_count', '50' ) ); ?>名</span><span class="p-stats__num js-count" data-count-to="<?php echo esc_attr( jinfoods_setting( 'staff_count', '50' ) ); ?>" aria-hidden="true"><?php echo esc_html( jinfoods_setting( 'staff_count', '50' ) ); ?></span><span class="p-stats__unit" aria-hidden="true">名</span></p>
          <!-- 【要確認】会社概要に「従業員数50名」とあるが時点の記載がない。
               クライアントに何年何月時点かを確認してから注記を入れる -->
          <p class="p-stats__note"><?php echo esc_html( jinfoods_setting( 'staff_note', 'パート・アルバイトを含む' ) ); ?></p>
        </li>
        <li class="p-stats__fact p-stats__fact--wide">
          <p class="p-stats__label">有資格者</p>
          <p class="p-stats__word"><?php echo esc_html( jinfoods_setting( 'license', '栄養士・管理栄養士・調理師' ) ); ?></p>
          <p class="p-stats__note">在籍しています</p>
        </li>
      </ul>
    </div>
  </section>

  <!-- ============================================================
       FACILITY：「8施設」の中身を実名で示す
       【要確認】施設名の掲載は業務案内ページで既に公開済みだが、
       トップで前面に出すと露出度が変わるため、各施設への確認を推奨
       ============================================================ -->
  <?php
  // 2026-09-30 コンペ B案「一覧と地図が連動」（LP と同じ見た目）。同日夜に「タグを最初から出し、押すと地図の下に詳細」へ変更。
  // 地図の点は「地区名（facility_area_text）」から座標を引く。座標は地区の代表点で、
  // 会社概要ページの地図と同じ原点・縮尺（1km = 11）。表に無い地区の施設は「位置を確認中」として点を出さない。
  // 番号は一覧の並び（menu_order）で、点がある施設だけに振る。
  // sp / md はタグの向き（空＝右）。自動で決めると重なるので、地区を足したら 320〜1440px で重なりを確かめてから書く
  //   sp：left / above-end / below-end / tagless（置き場が無いのでタグを隠して番号だけ）　md：left / above
  $jinfoods_spot_xy = array(
    '別府市野口中町' => array( 'xy' => array( -101, -68 ), 'sp' => 'below-end', 'md' => '' ),
    '大分市大在'     => array( 'xy' => array( 126, -21 ), 'sp' => 'above-end', 'md' => 'above' ),
    '大分市判田'     => array( 'xy' => array( 38, 79 ), 'sp' => '', 'md' => '' ),
    '大分市野津原'   => array( 'xy' => array( -71, 77 ), 'sp' => 'left', 'md' => 'left' ),
    '豊後大野市犬飼' => array( 'xy' => array( 50, 182 ), 'sp' => 'left', 'md' => '' ),
    // 明野は本社のすぐ右で、SP ではどの向きでも本社の文字か1・4番にぶつかる → SP はタグを隠して番号だけ（tagless）
    '大分市明野'     => array( 'xy' => array( 56, 5 ), 'sp' => 'tagless', 'md' => '' ),   // 明野東・西・北・高尾の代表点の平均（花埜家。受託は【要確認】）
    // 大分市美し野（風の丘）は地名検索でも公開情報でも位置が引けないので入れていない
  );
  $jinfoods_fac_types = array(
    'care'     => '高齢者施設',
    'hospital' => '病院',
    'company'  => '企業・社員食堂',
  );
  $jinfoods_fac_rows = array();
  $jinfoods_fac_no   = 0;
  foreach ( jinfoods_get_facilities() as $fac ) {
    $area  = (string) get_post_meta( $fac->ID, 'facility_area_text', true );
    $spot  = $jinfoods_spot_xy[ $area ] ?? null;
    $name  = get_the_title( $fac );
    $count = (int) get_post_meta( $fac->ID, 'facility_count', true );
    $type  = (string) get_post_meta( $fac->ID, 'facility_type', true );
    $jinfoods_fac_rows[] = array(
      'no'    => $spot ? ++$jinfoods_fac_no : 0,
      'name'  => $name,
      // 複数施設の契約先は、タグには最初の名前だけを出す（例：とまと／とまと2／… → とまと）。詳細には全部出す
      'short' => $count > 1 ? trim( explode( '／', $name )[0] ) : $name,
      'area'  => $area,
      'count' => $count,
      'type'  => $jinfoods_fac_types[ $type ] ?? '',
      'note'  => (string) get_post_meta( $fac->ID, 'facility_note', true ),
      'spot'  => $spot,
    );
  }
  $count = jinfoods_facility_count();
  ?>
  <section class="l-section l-section--wave p-facility" id="facility" aria-labelledby="facility-title">
    <div class="l-inner l-section__body">
      <div class="c-heading c-heading--center u-reveal js-reveal">
        <p class="c-heading__eyebrow" aria-hidden="true">Facility</p>
        <h2 class="c-heading__title" id="facility-title">受託施設一覧</h2>
        <p class="c-heading__lead">大分市・豊後大野市・別府市の施設で、毎日の食事を提供しています。</p>
      </div>

      <?php
      // 2026-09-30 夕：右の一覧をやめて地図だけにした。同日夜：施設名のタグを最初から出し、押すと地図の下に詳細が出る形に変えた（LP と同じ）。
      // 点は SVG の上に重ねたボタン。位置は viewBox（-235 -125 470 345）に対する %。タグの向きは座標の表の sp / md
      // 表に無い向き・綴り違いは右（既定）にする。存在しないクラスを黙って出さない
      $jinfoods_pin_mods = static function ( array $spot ): string {
        $m  = '';
        $sp = $spot['sp'] ?? '';
        $md = $spot['md'] ?? '';
        if ( in_array( $sp, array( 'left', 'above-end', 'below-end', 'tagless' ), true ) ) {
          $m .= ' p-facility__pin--' . $sp;
        }
        if ( in_array( $md, array( 'left', 'above' ), true ) ) {
          $m .= ' p-facility__pin--md-' . $md;
        }
        return $m;
      };
      ?>
      <figure class="p-area-map p-facility__map u-reveal js-reveal">
        <div class="p-facility__stage">
          <svg class="p-area-map__svg" viewBox="-235 -125 470 345" role="img" aria-labelledby="facility-map-title">
            <title id="facility-map-title">受託施設の位置の概略図（本社からの直線距離）</title>
            <?php get_template_part( 'template-parts/area-map-land' ); ?>
            <circle r="110" class="p-area-map__ring"/>
            <circle r="220" class="p-area-map__ring"/>
            <text x="6" y="-114" class="p-area-map__ring-label">10km</text>
            <text x="164" y="150" class="p-area-map__ring-label">20km</text>
            <g class="p-area-map__north" transform="translate(212 -96)"><path d="M0 -14 L7 6 L0 2 L-7 6 Z"/><text y="22" text-anchor="middle">N</text></g>
            <g>
              <circle r="11" class="p-area-map__hq-dot"/>
              <text x="0" y="-20" text-anchor="middle" class="p-area-map__label p-area-map__label--hq">本社</text>
            </g>
          </svg>
          <?php foreach ( $jinfoods_fac_rows as $row ) : ?>
            <?php
            if ( ! $row['no'] ) {
              continue;
            }
            $px = round( ( $row['spot']['xy'][0] + 235 ) / 470 * 100, 2 );
            $py = round( ( $row['spot']['xy'][1] + 125 ) / 345 * 100, 2 );
            ?>
            <button class="p-facility__pin js-facility-pin<?php echo esc_attr( $jinfoods_pin_mods( $row['spot'] ) ); ?>" type="button" style="<?php echo esc_attr( "--x: {$px}%; --y: {$py}%;" ); ?>" aria-expanded="false" aria-controls="facility-detail-<?php echo (int) $row['no']; ?>">
              <span class="p-facility__pin-num"><?php echo (int) $row['no']; ?></span>
              <span class="p-facility__tag"><?php echo esc_html( $row['short'] ); ?><?php if ( $row['count'] > 1 ) : ?><span class="p-facility__count"><?php echo (int) $row['count']; ?>施設</span><?php endif; ?><span class="p-facility__tag-mark" aria-hidden="true"></span></span>
            </button>
          <?php endforeach; ?>
        </div>
        <figcaption class="p-area-map__cap">
          <span class="p-area-map__source">地図：国土数値情報（行政区域データ）（国土交通省）を加工して作成</span>
        </figcaption>
      </figure>

      <?php $jinfoods_no_pin = array_filter( $jinfoods_fac_rows, static fn( $r ) => ! $r['no'] ); ?>
      <div class="p-facility__foot u-reveal js-reveal">
        <?php // 押した施設の詳細（押す前は全部 hidden で、何も出さない）。地図の figure の外に置いて、出典を地図の直下に残す。補足（facility_note）は種別に「・」でつなぐ ?>
        <?php foreach ( $jinfoods_fac_rows as $row ) : ?>
          <?php
          if ( ! $row['no'] ) {
            continue;
          }
          $jinfoods_type_text = implode( '・', array_filter( array( $row['type'], $row['note'] ), 'strlen' ) );
          ?>
          <div class="p-facility__detail" id="facility-detail-<?php echo (int) $row['no']; ?>" hidden>
            <p class="p-facility__detail-name"><span class="p-facility__detail-num" aria-hidden="true"><?php echo (int) $row['no']; ?></span><?php echo esc_html( $row['name'] ); ?></p>
            <dl class="p-facility__detail-list">
              <div class="p-facility__detail-row"><dt class="p-facility__detail-term">所在地</dt><dd><?php echo esc_html( $row['area'] ); ?></dd></div>
              <?php if ( '' !== $jinfoods_type_text ) : ?>
                <div class="p-facility__detail-row"><dt class="p-facility__detail-term">種別</dt><dd><?php echo esc_html( $jinfoods_type_text ); ?></dd></div>
              <?php endif; ?>
            </dl>
          </div>
        <?php endforeach; ?>
        <p class="p-facility__hint js-facility-hint" hidden>施設名を押すと、所在地と種別が出ます</p>
        <p class="u-vh js-facility-status" aria-live="polite"></p>
        <?php if ( $jinfoods_no_pin ) : ?>
          <?php // 位置を確認中の施設（地図に点が無い）。地区名の座標の表に足せば、自動で地図に点が出てこの行から消える ?>
          <p class="p-facility__others"><span>ほかに</span><?php foreach ( $jinfoods_no_pin as $row ) : ?><span class="c-tag"><?php echo esc_html( $row['name'] . ( '' !== $row['area'] ? '（' . $row['area'] . '）' : '' ) ); ?></span><?php endforeach; ?></p>
        <?php endif; ?>
        <p class="p-facility__note">
          ※ 同一の契約先で複数の施設を受託している場合があります。<?php echo esc_html( (string) $count['contracts'] ); ?>契約先・<?php echo esc_html( (string) $count['facilities'] ); ?>施設で稼働しています。
        </p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       STRENGTH（2026-09-30 コンペ B案「介護食の5段階を主役に」）
       4つのうち、トップのほかの節に出てこない介護食（食事形態の5段階）を 01 にして大きく見せ、
       残り3つ（管理栄養士・行事食・地域）は小さく並べて、根拠のページへ文字リンクで送る。
       主役の本文と5段階の名前・説明は、事業内容ページの「5段階の食事形態に対応」と同じ文（直すときは両方）。
       以前の紙芝居（見出しを固定して1枚ずつ送る）はやめた。理由は _strength.scss の冒頭
       ============================================================ -->
  <section class="l-section l-section--sage l-section--wave p-strength" id="strength" aria-labelledby="strength-title">
    <div class="l-inner l-section__body">
      <div class="c-heading c-heading--center c-heading--deep-eyebrow u-reveal js-reveal">
        <p class="c-heading__eyebrow" aria-hidden="true">Strength</p>
        <h2 class="c-heading__title" id="strength-title">JINfoodsの4つの強み</h2>
      </div>

      <div class="p-strength__hero u-reveal js-reveal">
        <div class="p-strength__hero-media">
          <img class="p-strength__hero-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/strength-2.jpg' ); ?>" alt="ムース食に対応した食事" width="960" height="600" loading="lazy">
        </div>
        <div class="p-strength__hero-body">
          <p class="p-strength__num">01</p>
          <h3 class="p-strength__hero-title">介護食・ムース食に対応</h3>
          <p class="p-strength__hero-text">噛む力・飲み込む力は、利用者さまお一人ごとに違います。同じ献立を、状態に合わせて5段階の形態でご用意します。施設によってはムース食もご相談いただけます。</p>
          <a class="c-btn c-btn--text c-btn--sm" href="<?php echo esc_url( home_url( '/service/#texture' ) ); ?>">5段階の食事形態を見る<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></a>
        </div>
        <!-- 図（p-strength__glyph）は飾り。--n は1辺の切れ目の数で、1＝塊のまま → 5＝細かく刻む。ミキサーは刻み線のない丸 -->
        <ol class="p-strength__steps js-strength-steps" aria-label="食事形態の5段階">
          <li class="p-strength__step" style="--i: 0;">
            <span class="p-strength__glyph" style="--n: 1;" aria-hidden="true"></span>
            <span class="p-strength__step-name">常食</span>
            <span class="p-strength__step-desc">通常の形態のまま提供します。</span>
          </li>
          <li class="p-strength__step" style="--i: 1;">
            <span class="p-strength__glyph" style="--n: 2;" aria-hidden="true"></span>
            <span class="p-strength__step-name">一口大</span>
            <span class="p-strength__step-desc">ひと口で食べられる大きさに切り分けます。</span>
          </li>
          <li class="p-strength__step" style="--i: 2;">
            <span class="p-strength__glyph" style="--n: 3;" aria-hidden="true"></span>
            <span class="p-strength__step-name">粗刻み</span>
            <span class="p-strength__step-desc">噛みやすいよう粗く刻みます。</span>
          </li>
          <li class="p-strength__step" style="--i: 3;">
            <span class="p-strength__glyph" style="--n: 5;" aria-hidden="true"></span>
            <span class="p-strength__step-name">極刻み</span>
            <span class="p-strength__step-desc">さらに細かく刻み、まとまりを持たせます。</span>
          </li>
          <li class="p-strength__step" style="--i: 4;">
            <span class="p-strength__glyph p-strength__glyph--paste" aria-hidden="true"></span>
            <span class="p-strength__step-name">ミキサー</span>
            <span class="p-strength__step-desc">なめらかなペースト状にします。</span>
          </li>
        </ol>
      </div>

      <ul class="p-strength__others">
        <li class="p-strength__other u-reveal js-reveal">
          <div class="p-strength__other-media">
            <img class="p-strength__other-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/strength-1.jpg' ); ?>" alt="提供前の食事を確認する管理栄養士" width="960" height="600" loading="lazy"><span class="c-image-note">イメージ</span>
          </div>
          <p class="p-strength__other-num">02</p>
          <h3 class="p-strength__other-title">管理栄養士による<wbr>献立設計</h3>
          <p class="p-strength__other-text">栄養バランスを計算して毎月の献立を組み立て、提供前には管理栄養士が検食して味と形態を確認しています。施設ごとのご要望や利用者さまの状態に合わせて調整します。</p>
          <a class="c-btn c-btn--text c-btn--sm p-strength__other-more" href="<?php echo esc_url( home_url( '/about/#team' ) ); ?>">施設に入る体制を見る<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></a>
        </li>
        <li class="p-strength__other u-reveal js-reveal">
          <div class="p-strength__other-media">
            <img class="p-strength__other-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/strength-3.jpg' ); ?>" alt="季節の行事食" width="960" height="600" loading="lazy">
          </div>
          <p class="p-strength__other-num">03</p>
          <h3 class="p-strength__other-title">毎月の行事食</h3>
          <p class="p-strength__other-text">季節や行事に合わせた彩り豊かな献立を、毎月ご用意しています。食べる楽しみが、毎日の張り合いにつながります。</p>
          <a class="c-btn c-btn--text c-btn--sm p-strength__other-more js-anchor" href="#gallery">食事ギャラリーを見る<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></a>
        </li>
        <li class="p-strength__other u-reveal js-reveal">
          <div class="p-strength__other-media">
            <img class="p-strength__other-img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/strength-4.jpg' ); ?>" alt="厨房スタッフと施設職員が打ち合わせをしている様子" width="960" height="600" loading="lazy"><span class="c-image-note">イメージ</span>
          </div>
          <p class="p-strength__other-num">04</p>
          <h3 class="p-strength__other-title">地域に根ざした<wbr>きめ細かな対応</h3>
          <p class="p-strength__other-text">大分市・豊後大野市・別府市の施設に密着しています。距離が近いからこそ、急なご相談にも柔軟にお応えできます。</p>
          <a class="c-btn c-btn--text c-btn--sm p-strength__other-more" href="<?php echo esc_url( home_url( '/about/#area' ) ); ?>">拠点と受託エリアを見る<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></a>
        </li>
      </ul>
    </div>
  </section>

  <!-- ============================================================
       SERVICE
       受託実績があるのは高齢者施設と病院の2領域だけ。
       企業・社員食堂は実績ゼロなので「対応可能領域」として別枠に落とす
       ============================================================ -->
  <section class="l-section l-section--paper l-section--wave p-service" id="service" aria-labelledby="service-title">
    <div class="l-inner p-service__inner">
      <div class="p-service__head u-reveal js-reveal">
        <div class="c-heading">
          <p class="c-heading__eyebrow" aria-hidden="true">Service</p>
          <h2 class="c-heading__title" id="service-title">事業内容</h2>
        </div>
        <a class="c-btn c-btn--text" href="<?php echo esc_url( home_url( '/service/' ) ); ?>">事業内容を見る<span class="c-arrow-line" aria-hidden="true"></span></a>
      </div>

      <div class="p-service__media u-reveal js-reveal">
        <div class="p-service__photo">
          <img class="p-service__img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/service-1.jpg' ); ?>" alt="高齢者施設で提供している献立" width="640" height="400" loading="lazy">
        </div>
        <div class="p-service__photo">
          <img class="p-service__img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/service-2.jpg' ); ?>" alt="焼き魚を主菜にした献立" width="640" height="400" loading="lazy">
        </div>
      </div>

      <ul class="p-service__rows u-reveal js-reveal">
        <li class="p-service__field">
          <a class="p-service__row" href="<?php echo esc_url( home_url( '/service/#field' ) ); ?>">
            <span class="p-service__name">高齢者施設給食</span>
            <span class="p-service__text">特別養護老人ホーム・介護老人保健施設・グループホームなどの厨房業務を受託しています。利用者さまの状態に合わせた食事を、施設内で毎日調製します。</span>
            <span class="c-arrow-line" aria-hidden="true"></span>
          </a>
        </li>
        <li class="p-service__field">
          <a class="p-service__row" href="<?php echo esc_url( home_url( '/service/#field' ) ); ?>">
            <span class="p-service__name">病院給食</span>
            <span class="p-service__text">入院患者さまの治療と回復を支えるため、栄養管理された食事を提供しています。別府市の清瀬病院さまで稼働中です。</span>
            <span class="c-arrow-line" aria-hidden="true"></span>
          </a>
        </li>
        <li class="p-service__field p-service__field--ask">
          <a class="p-service__row p-service__row--ask js-anchor" data-cta="consult-top" href="#contact">
            <span class="p-service__name">企業・社員食堂も<wbr>ご相談ください</span>
            <span class="p-service__text">現在の受託は高齢者施設・病院ですが、給食委託厨房業務として対応可能です。まずはお気軽にご相談ください。</span>
            <span class="c-arrow-line" aria-hidden="true"></span>
          </a>
        </li>
      </ul>
    </div>
  </section>

  <!-- ============================================================
       GALLERY：料理写真のみ。厨房・人物は about / strength 側に置く
       ============================================================ -->
  <section class="l-section l-section--wave p-gallery" id="gallery" aria-labelledby="gallery-title">
    <div class="l-inner l-section__body">
      <div class="c-heading c-heading--center u-reveal js-reveal">
        <p class="c-heading__eyebrow" aria-hidden="true">Gallery</p>
        <h2 class="c-heading__title" id="gallery-title">食事ギャラリー</h2>
        <p class="c-heading__lead">施設で提供している毎日の食事と、季節の行事食をご紹介します。</p>
      </div>

      <ul class="p-gallery__list u-reveal js-reveal">
        <?php
        // 食事ギャラリーの最新8件。アイキャッチが無い投稿は枠ごと出さない
        // （現行サイトは代替画像の参照先が存在せず、壊れた画像が出ていた）
        $gal = new WP_Query( array(
            'post_type'           => 'gallery',  // 2026-10-01 に投稿のカテゴリから投稿タイプへ分けた（inc/post-types.php）
            'posts_per_page'      => 8,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'meta_query'          => array( array( 'key' => '_thumbnail_id' ) ),
        ) );
        if ( $gal->have_posts() ) :
            $gi = 0;
            while ( $gal->have_posts() ) : $gal->the_post(); $gi++;
        ?>
          <li class="p-gallery__item">
            <a class="p-gallery__link" href="<?php the_permalink(); ?>">
              <div class="c-media c-media--1x1">
                <?php the_post_thumbnail( 'jinfoods_square', array(
                    'class'   => 'p-gallery__img',
                    'alt'     => jinfoods_thumb_alt(),
                    'loading' => $gi > 4 ? 'lazy' : 'eager',
                ) ); ?>
              </div>
              <?php // 幕（hover できる端末と Tab のときだけ見える）。読み上げは写真の代わりの文に任せる ?>
              <span class="p-gallery__veil" aria-hidden="true">
                <span class="p-gallery__cap"><?php echo esc_html( jinfoods_thumb_alt() ); ?></span>
                <span class="p-gallery__go"><svg class="p-gallery__go-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 12 12 4M6 4h6v6"/></svg></span>
              </span>
            </a>
          </li>
        <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
      </ul>

      <div class="p-gallery__foot u-reveal js-reveal">
        <a class="c-btn c-btn--outline" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">ギャラリー一覧を見る<span class="c-btn__arrow" aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>

  <!-- ============================================================
       NEWS：最近の投稿を行リストで出す（一覧ページと同じ部品 c-post-list）。
       投稿がある前提。並びは新しい順。件数はトップの予告なので 5
       ============================================================ -->
  <section class="l-section l-section--paper l-section--wave p-news" id="news" aria-labelledby="news-title">
    <div class="p-news__inner l-inner">
      <div class="p-news__head u-reveal js-reveal">
        <div class="p-news__label">
          <p class="p-news__eyebrow" aria-hidden="true">News</p>
          <h2 class="p-news__title" id="news-title">お知らせ</h2>
        </div>
        <a class="c-btn c-btn--text" href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>"><span class="u-vh">お知らせの</span>一覧を見る<span class="c-arrow-line" aria-hidden="true"></span></a>
      </div>
      <?php
      $news = new WP_Query( array(
          'post_type'           => 'post',
          'posts_per_page'      => 5,
          'category_name'       => 'news',
          'orderby'             => 'date',
          'order'               => 'DESC',
          'ignore_sticky_posts' => true,
          'no_found_rows'       => true,
      ) );
      if ( $news->have_posts() ) :
      ?>
        <ul class="c-post-list u-reveal js-reveal">
          <?php while ( $news->have_posts() ) : $news->the_post(); ?>
            <li class="c-post-list__item">
              <a class="c-post-list__link" href="<?php the_permalink(); ?>">
                <span class="c-post-list__text">
                  <span class="c-post-list__meta">
                    <time class="c-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
                  </span>
                  <span class="c-post-list__title"><?php the_title(); ?></span>
                </span>
                <span class="c-arrow-line" aria-hidden="true"></span>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php
          wp_reset_postdata();
      else :
      ?>
        <p class="p-news__empty">準備中です。</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- ============================================================
       RECRUIT（副導線）
       2026-10-01 コンペ A案「条件を見出しに」：働き方の条件を、時間帯を先頭に大きく。文言は前の箇条書きと同じ。
       労働条件の詳細（給与・就業時間）はここに書かない。
       募集時明示事項に不足があり社労士確認が未了のため、既存の採用ページへ送る
       ============================================================ -->
  <section class="l-section l-section--wave p-recruit" id="recruit" aria-labelledby="recruit-title">
    <div class="p-recruit__inner l-inner">
      <div class="p-recruit__body u-reveal js-reveal">
        <div class="c-heading">
          <p class="c-heading__eyebrow" aria-hidden="true">Recruit</p>
          <h2 class="c-heading__title" id="recruit-title">調理スタッフ・調理補助を<br>募集しています</h2>
        </div>
        <?php echo jinfoods_recruit_conds_html(); // 値はサイト設定 > 採用情報。採用ページの頭と同じもの ?>
        <a class="c-btn c-btn--primary" data-cta="recruit-top" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">募集要項を見る<span class="c-btn__arrow" aria-hidden="true">→</span></a>
      </div>

      <div class="c-media c-media--4x3 u-reveal js-reveal">
        <img class="c-media__img" src="<?php echo esc_url( JINFOODS_URI . '/assets/images/recruit.jpg' ); ?>" alt="厨房で働くスタッフ" width="640" height="480" loading="lazy"><span class="c-image-note">イメージ</span>
      </div>
    </div>
  </section>

  <!-- ============================================================
       CONTACT（締めCTA）
       ============================================================ -->
  <section class="l-section l-section--sage l-section--wave p-contact" id="contact" aria-labelledby="contact-title">
    <div class="p-contact__inner l-inner">
      <h2 class="p-contact__title" id="contact-title">お食事サービスの導入・見直しを<br>ご検討の担当者さまへ</h2>
      <p class="p-contact__text">
        試食のご依頼、サービスに関するご相談、お見積りなど、お気軽にお問い合わせください。担当者が迅速に対応いたします。
      </p>
      <?php // 施設の担当者向けの入口4つ（2026-10-01 D案）。言葉はお問い合わせの種類の選択肢と同じ ?>
      <div class="p-contact__doors">
        <a class="c-btn c-btn--primary" data-cta="trial-band" href="<?php echo esc_url( home_url( '/contact/#form' ) ); ?>">試食のご依頼<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--light" data-cta="consult-band" href="<?php echo esc_url( home_url( '/contact/#form' ) ); ?>">業務のご相談<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--light" data-cta="recruit-band" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">採用について<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <?php $tel = jinfoods_tel(); // 番号はサイト設定から（header・footer・cta.php と同じ） ?>
        <a class="c-btn c-btn--tel c-btn--light c-btn--compact" data-cta="tel-band" href="<?php echo esc_attr( $tel['href'] ); ?>">
          <span class="c-btn__tel-num"><?php echo esc_html( $tel['display'] ); ?></span>
          <span class="c-btn__tel-note"><?php echo esc_html( $tel['note'] ); ?></span>
        </a>
      </div>
    </div>
  </section>


<?php
get_footer();

<?php
/**
 * ヘッダー
 *
 * <title> と meta description はここに書かない。wp_head() に任せる。
 * 現行サイトは header.php にタイトルを直書きしていたため全9ページが同じ名前だった。
 *
 * @package jinfoods
 */
$tel = jinfoods_tel();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php // ロゴのマークから作った仮のアイコン（静的版と同じ。2026-10-02）。管理画面の「サイトアイコン」を設定したら、そちらに任せてここは消す ?>
<link rel="icon" href="<?php echo esc_url( JINFOODS_URI . '/assets/images/favicon.ico' ); ?>" sizes="any">
<link rel="apple-touch-icon" href="<?php echo esc_url( JINFOODS_URI . '/assets/images/apple-touch-icon.png' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="c-skip-link js-skip" href="#main">本文へスキップ</a>

<header class="l-header js-header">
  <div class="l-header__inner l-inner">
    <?php
    // ロゴは h1 にしない。
    // トップは front-page.php のキャッチ、下層はページ見出しが唯一の h1 になる。
    // （最初トップだけ h1 にしていたが、FVのキャッチと衝突して h1 が2個になった）
    ?>
    <p class="l-header__logo-wrap">
      <a class="l-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <img class="l-header__logo-img"
             src="<?php echo esc_url( JINFOODS_URI . '/assets/images/logo.png' ); ?>"
             alt="<?php bloginfo( 'name' ); ?>" width="282" height="106">
      </a>
    </p>

    <nav class="l-header__nav" aria-label="メインメニュー">
      <?php
      if ( has_nav_menu( 'primary' ) ) {
          wp_nav_menu( array(
              'theme_location' => 'primary',
              'container'      => false,
              'menu_class'     => 'l-header__nav-list',
              'depth'          => 1,
              'link_class'     => 'l-header__nav-link',
          ) );
      } else {
          // メニュー未設定でも壊れないように既定を出す
          ?>
          <ul class="l-header__nav-list">
            <li><a class="l-header__nav-link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">会社概要</a></li>
            <li><a class="l-header__nav-link" href="<?php echo esc_url( home_url( '/service/' ) ); ?>">事業内容</a></li>
            <?php // 受託施設は一覧ページを持たない（CPT facility は has_archive=false）。フッターと同じくトップの段へ飛ばす ?>
            <li><a class="l-header__nav-link" href="<?php echo esc_url( home_url( '/#facility' ) ); ?>">受託施設</a></li>
            <li><a class="l-header__nav-link" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">食事ギャラリー</a></li>
            <li><a class="l-header__nav-link" href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>">お知らせ</a></li>
          </ul>
          <?php
      }
      ?>
    </nav>

    <div class="l-header__tel">
      <a class="l-header__tel-num" data-cta="tel-header" href="<?php echo esc_attr( $tel['href'] ); ?>"><?php echo esc_html( $tel['display'] ); ?></a>
      <span class="l-header__tel-note"><?php echo esc_html( $tel['note'] ); ?></span>
    </div>

    <div class="l-header__cta">
      <a class="c-btn c-btn--primary c-btn--sm" data-cta="contact-header" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a>
      <a class="c-btn c-btn--text c-btn--sm" data-cta="recruit-header" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">採用情報<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></a>
    </div>

    <button class="l-header__toggle js-drawer-toggle" type="button" aria-expanded="false" aria-controls="drawer">
      <span class="l-header__toggle-bar" aria-hidden="true"></span>
      <span class="u-vh js-drawer-label">メニューを開く</span>
    </button>
  </div>
</header>

<div class="l-drawer js-drawer" id="drawer" hidden>
  <div class="l-drawer__body l-inner l-inner--narrow">
    <?php // 2026-09-30 夜に組み替え（C案。LP と同じ）：目次（事業の5つ）→ 採用の帯 → 連絡。「トップページ」はロゴで戻れるので置かない ?>
    <nav aria-label="メインメニュー">
      <ul class="l-drawer__nav">
        <?php
        $jinfoods_drawer_tiles = array(
          array( '/about/', 'About', '会社概要' ),
          array( '/service/', 'Service', '事業内容' ),
          array( '/#facility', 'Facility', '受託施設' ),
          array( '/gallery/', 'Gallery', '食事ギャラリー' ),
          array( '/category/news/', 'News', 'お知らせ' ),
        );
        foreach ( $jinfoods_drawer_tiles as $tile ) :
          ?>
          <li class="l-drawer__nav-item"><a class="l-drawer__tile" href="<?php echo esc_url( home_url( $tile[0] ) ); ?>"><span class="l-drawer__tile-en" aria-hidden="true"><?php echo esc_html( $tile[1] ); ?></span><span class="l-drawer__tile-ja"><?php echo esc_html( $tile[2] ); ?></span></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <a class="l-drawer__recruit" data-cta="recruit-drawer" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>"><span class="l-drawer__recruit-text"><span class="l-drawer__recruit-sub">調理スタッフ募集中</span>採用情報</span><span class="c-arrow-line c-arrow-line--from-end" aria-hidden="true"></span></a>
    <div class="l-drawer__cta">
      <?php // お問い合わせと半分ずつ並べるときだけ詰める（お問い合わせページは電話だけで幅いっぱい） ?>
      <a class="c-btn c-btn--tel<?php echo is_page( 'contact' ) ? '' : ' c-btn--compact'; ?>" data-cta="tel-drawer" href="<?php echo esc_attr( $tel['href'] ); ?>">
        <span class="c-btn__tel-num"><?php echo esc_html( $tel['display'] ); ?></span>
        <span class="c-btn__tel-note"><?php echo esc_html( $tel['note'] ); ?></span>
      </a>
      <?php if ( ! is_page( 'contact' ) ) : // お問い合わせページでは自分自身へのリンクになるので電話だけ（SP 固定バーと同じ） ?>
        <a class="c-btn c-btn--primary" data-cta="contact-drawer" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<main id="main">

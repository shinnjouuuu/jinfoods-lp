<?php
/**
 * フッター
 *
 * @package jinfoods
 */
$tel = jinfoods_tel();
?>
</main>

<footer class="l-footer">
  <div class="l-inner">
    <div class="l-footer__body">
      <div class="l-footer__brand">
        <img class="l-footer__logo"
             src="<?php echo esc_url( JINFOODS_URI . '/assets/images/logo.png' ); ?>"
             alt="<?php bloginfo( 'name' ); ?>" width="282" height="106" loading="lazy">
        <address class="l-footer__address">
          株式会社JINfoods<br>
          〒870-0822　大分県大分市大道町<span class="u-nowrap">5丁目3-32</span><br>
          TEL：<a class="l-footer__tel" data-cta="tel-footer" href="<?php echo esc_attr( $tel['href'] ); ?>"><?php echo esc_html( $tel['display'] ); ?></a>　<span class="u-nowrap">FAX：097-574-9235</span><br>
          営業時間 6:00〜19:00／定休日なし
        </address>
      </div>

      <nav class="l-footer__nav" aria-label="フッターメニュー">
        <div>
          <h2 class="l-footer__nav-title">事業について</h2>
          <?php
          if ( has_nav_menu( 'footer_service' ) ) {
              wp_nav_menu( array(
                  'theme_location' => 'footer_service',
                  'container'      => false,
                  'menu_class'     => 'l-footer__nav-list',
                  'depth'          => 1,
                  'link_class'     => 'l-footer__nav-link',
              ) );
          } else {
              ?>
              <ul class="l-footer__nav-list">
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/service/' ) ); ?>">事業内容</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/#facility' ) ); ?>">受託施設一覧</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">食事ギャラリー</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>">お知らせ</a></li>
              </ul>
              <?php
          }
          ?>
        </div>
        <div>
          <h2 class="l-footer__nav-title">会社情報</h2>
          <?php
          if ( has_nav_menu( 'footer_company' ) ) {
              wp_nav_menu( array(
                  'theme_location' => 'footer_company',
                  'container'      => false,
                  'menu_class'     => 'l-footer__nav-list',
                  'depth'          => 1,
                  'link_class'     => 'l-footer__nav-link',
              ) );
          } else {
              ?>
              <ul class="l-footer__nav-list">
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">会社概要</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/about/#map' ) ); ?>">アクセス</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">採用情報</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
                <li><a class="l-footer__nav-link" href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></li>
              </ul>
              <?php
          }
          ?>
        </div>
      </nav>
    </div>

    <p class="l-footer__copyright">&copy; <?php bloginfo( 'name' ); ?> All Rights Reserved.</p>
  </div>
</footer>

<?php
// お問い合わせページでは「お問い合わせ」ボタンが自分自身へのリンクになるので、電話だけにする
$jinfoods_spbar_single = is_page( 'contact' );
?>
<nav class="p-spbar<?php echo $jinfoods_spbar_single ? ' p-spbar--single' : ''; ?> js-spbar" aria-label="連絡先">
  <a class="c-btn c-btn--outline c-btn--sm" data-cta="<?php echo is_page( 'recruit' ) ? 'apply-tel-spbar' : 'tel-spbar'; ?>" href="<?php echo esc_attr( $tel['href'] ); ?>">電話する</a>
  <?php if ( ! $jinfoods_spbar_single ) : ?>
    <?php // 採用ページでは応募の言葉にする（2026-10-01。行き先は同じお問い合わせフォーム） ?>
    <a class="c-btn c-btn--primary c-btn--sm" data-cta="<?php echo is_page( 'recruit' ) ? 'apply-form-spbar' : 'contact-spbar'; ?>" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo is_page( 'recruit' ) ? 'フォームで応募' : 'お問い合わせ'; ?></a>
  <?php endif; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>

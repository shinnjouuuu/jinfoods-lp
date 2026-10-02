<?php
/**
 * 404
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <div class="l-under__block">
      <div class="l-under__prose">
        <p>お探しのページは移動または削除された可能性があります。お手数ですが、下記からお探しください。</p>
      </div>
      <div class="c-btn-group">
        <a class="c-btn c-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--outline" href="<?php echo esc_url( home_url( '/category/news/' ) ); ?>">お知らせ一覧<span class="c-btn__arrow" aria-hidden="true">→</span></a>
      </div>
    </div>
  </div>
</div>

<?php
get_template_part( 'template-parts/cta' );
get_footer();

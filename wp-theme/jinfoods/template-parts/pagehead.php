<?php
/**
 * ページ見出し
 *
 * 現行サイトは文字を焼き込んだ画像で出していて alt も空だった。
 * ここでは背景画像と文字を分ける
 *
 * @package jinfoods
 */
// お知らせの記事ページは、写真なしの薄い帯（パンくずと英字ラベルだけ。記事の題が h1）。
// 2026-10-01 コンペ A（DESIGN.md「お知らせの記事ページ」）。ギャラリーの投稿は下の大きな見出しのまま
if ( jinfoods_is_news_single() ) :
	?>
<div class="l-pagehead l-pagehead--compact">
  <div class="l-pagehead__inner l-inner">
    <?php jinfoods_breadcrumb( 'c-breadcrumb--on-light' ); ?>
    <p class="l-pagehead__en" aria-hidden="true">News</p>
  </div>
</div>
	<?php
	return;
endif;

$head = jinfoods_pagehead();
?>
<div class="l-pagehead">
  <div class="l-pagehead__bg" aria-hidden="true">
    <?php // 下層ページの LCP（いちばん大きく描かれる要素）なので、ほかの画像より先に読ませる ?>
    <img class="l-pagehead__bg-img" src="<?php echo esc_url( $head['bg'] ); ?>" alt="" width="1440" height="420" fetchpriority="high">
  </div>
  <div class="l-pagehead__scrim" aria-hidden="true"></div>
  <?php if ( jinfoods_is_generated_image( $head['bg'] ) ) : // 背景が生成画像のときだけ「イメージ」の札 ?>
    <span class="c-image-note l-pagehead__note" aria-hidden="true">イメージ</span>
  <?php endif; ?>
  <div class="l-pagehead__inner l-inner">
    <?php jinfoods_breadcrumb(); ?>
    <p class="l-pagehead__en" aria-hidden="true"><?php echo esc_html( $head['en'] ); ?></p>
    <h1 class="l-pagehead__title"><?php echo esc_html( $head['ja'] ); ?></h1>
    <?php if ( $head['lead'] ) : ?>
      <p class="l-pagehead__lead"><?php echo esc_html( $head['lead'] ); ?></p>
    <?php endif; ?>
  </div>
</div>

<?php
/**
 * 固定ページ（汎用）
 *
 * 会社概要・事業内容など内容が決まっているページは、
 * 管理画面から自由に編集できるようここで本文を出す。
 * 見出し（h1）はページ見出し側にあるので、本文では h2 から始める
 *
 * 本文が自前で l-under__block を持っている場合（LP から移した「カスタムHTML」の本文）は、
 * 外側で包むと段が二重になるのでそのまま出す。普通に書いた本文は従来どおり prose で包む
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <?php while ( have_posts() ) : the_post(); ?>
      <?php if ( false !== strpos( get_the_content(), 'l-under__block' ) ) : ?>
        <?php the_content(); ?>
      <?php else : ?>
        <div class="l-under__block">
          <div class="l-under__prose c-editor">
            <?php the_content(); ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endwhile; ?>
  </div>
</div>

<?php
// プライバシーポリシーとお問い合わせは営業の締め（問い合わせの段）を出さない。LP と同じ
// （お問い合わせページで出すと、入口がそのページのフォームに戻るだけになる。2026-10-01）
if ( ! is_page( array( 'privacy', 'contact' ) ) ) {
	get_template_part( 'template-parts/cta' );
}
get_footer();

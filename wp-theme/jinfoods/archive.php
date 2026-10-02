<?php
/**
 * 一覧ページ
 *
 * 現行サイトの問題：
 *  ・ギャラリー一覧が 9,346px。画像の幅が 285〜380px でバラバラでグリッドが揃わなかった
 *  ・お知らせ一覧も画像主体で 4,301px
 *  ・記事タイトルがすべて h1 になっていた（お知らせ12個・ギャラリー14個）
 * → ギャラリーは 1:1 のグリッド、お知らせは行リスト。見出しは h2 にする
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
// ギャラリーの一覧は category.php（2026-10-01 にここのギャラリーの分岐を消した。ここでは成り立たず、通らなかったため）
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <div class="l-under__block">

      <?php if ( ! have_posts() ) : ?>
        <div class="l-under__prose"><p>まだ投稿がありません。</p></div>


      <?php else : ?>
        <ul class="c-post-list">
          <?php while ( have_posts() ) : the_post(); ?>
            <li class="c-post-list__item">
              <a class="c-post-list__link" href="<?php the_permalink(); ?>">
                <span class="c-post-list__text">
                  <span class="c-post-list__meta">
                    <time class="c-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
                    <span class="c-tag c-tag--accent"><?php echo esc_html( jinfoods_primary_category() ); ?></span>
                  </span>
                  <span class="c-post-list__title"><?php the_title(); ?></span>
                </span>
                <span class="c-arrow-line" aria-hidden="true"></span>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php endif; ?>

      <?php
      the_posts_pagination( array(
          'mid_size'  => 1,
          'prev_text' => '前へ',
          'next_text' => '次へ',
          'class'     => 'c-pager',
      ) );
      ?>
    </div>
  </div>
</div>

<?php
get_template_part( 'template-parts/cta' );
get_footer();

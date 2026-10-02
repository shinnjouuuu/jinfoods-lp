<?php
/**
 * カテゴリの一覧（お知らせ）
 *
 * 現行サイトの問題：
 *  ・お知らせ一覧が画像主体で 4,301px
 *  ・記事タイトルがすべて h1 になっていた（お知らせ12個・ギャラリー14個）
 * → お知らせは行リスト。見出しは h2 にする
 *
 * 食事ギャラリーは 2026-10-01 に投稿タイプ gallery へ分けた（カテゴリ gallery は消した）。一覧は archive-gallery.php
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <div class="l-under__block">
      <?php if ( ! have_posts() ) : ?>
        <div class="l-under__prose"><p>まだ投稿がありません。</p></div>

      <?php else : ?>
        <?php // お知らせは記事だけなので、タグは出さない（全部「お知らせ」になる。DESIGN.md「お知らせ一覧」） ?>
        <ul class="c-post-list">
          <?php while ( have_posts() ) : the_post(); ?>
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
      <?php endif; ?>

      <?php
      the_posts_pagination( array(
          'mid_size'  => 1,
          'prev_text' => '前へ',
          'next_text' => '次へ',
          'class'     => 'c-pager',
      ) );
      ?>

      <?php // LP は行事食もお知らせ一覧に並べていたが、ここでは食事ギャラリー側に分けているので「に掲載」とする ?>
      <p class="c-prose__note">※ 行事食のご報告は<a href="<?php echo esc_url( get_post_type_archive_link( 'gallery' ) ); ?>">食事ギャラリー</a>に掲載しています。</p>
    </div>
  </div>
</div>

<?php
get_template_part( 'template-parts/cta' );
get_footer();

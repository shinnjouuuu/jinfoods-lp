<?php
/**
 * 記事詳細
 *
 * 現行サイトはタイトルが h1 で2回出ていた。ここでは h1 を1ページに1つにする
 *  ・お知らせの記事：ページ見出しは薄い帯（template-parts/pagehead.php）で、記事の題が h1。
 *    日付だけ（タグなし）、写真は本文のあと（2026-10-01 コンペ A。DESIGN.md「お知らせの記事ページ」）
 *  ・食事ギャラリーの1件（投稿タイプ gallery。2026-10-01 に投稿のカテゴリから分けた）：
 *    ページ見出しの h1 は「食事ギャラリー」、題は h2、タグあり、写真は本文の前（今まで通り）
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
$is_news = jinfoods_is_news_single();  // ページ見出しの薄い帯と同じ判定（inc/template-tags.php）
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <?php while ( have_posts() ) : the_post(); ?>
      <article class="p-entry" id="post-<?php the_ID(); ?>">
        <div class="p-entry__meta">
          <time class="c-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
          <?php if ( ! $is_news ) : ?>
            <span class="c-tag c-tag--accent"><?php echo esc_html( jinfoods_primary_category() ); ?></span>
          <?php endif; ?>
        </div>

        <?php if ( $is_news ) : ?>
          <h1 class="p-entry__title"><?php the_title(); ?></h1>
        <?php else : ?>
          <h2 class="p-entry__title"><?php the_title(); ?></h2>
          <?php jinfoods_entry_media(); ?>
        <?php endif; ?>

        <div class="p-entry__body">
          <?php the_content(); ?>
        </div>

        <?php
        // お知らせは写真を本文のあとに置く（最初の画面で本文を読み始められるように）
        if ( $is_news ) {
            jinfoods_entry_media( true );
        }
        ?>

        <div class="p-entry__nav">
          <?php
          // お知らせは同じカテゴリーの中だけでたどる。絞らないと「次」が初期投稿（Hello world!）になっていた（2026-10-01）。
          // 食事ギャラリーは投稿タイプが別なので、何もしなくても食事ギャラリーの中だけをたどる
          $prev = $is_news ? get_previous_post( true, '', 'category' ) : get_previous_post();
          $next = $is_news ? get_next_post( true, '', 'category' ) : get_next_post();
          // 食事ギャラリーの前後は、拡大表示のボタン（「前の写真」「次の写真」）とそろえる
          $prev_label = $is_news ? '前のお知らせ' : '前の写真';
          $next_label = $is_news ? '次のお知らせ' : '次の写真';
          // 行き先の題を読み上げ用に添える（「前のお知らせ」だけだと、どの記事へ行くか分からない。2026-10-02、hallmark check P3）
          if ( $prev ) {
              printf(
                  '<a class="c-btn c-btn--text p-entry__prev" href="%s"><span class="c-arrow-line c-arrow-line--back" aria-hidden="true"></span>%s<span class="u-vh">：%s</span></a>',
                  esc_url( get_permalink( $prev ) ),
                  esc_html( $prev_label ),
                  esc_html( get_the_title( $prev ) )
              );
          }

          $news_cat   = get_category_by_slug( 'news' );
          $list_url   = $is_news ? ( $news_cat ? get_category_link( $news_cat->term_id ) : home_url( '/' ) ) : get_post_type_archive_link( 'gallery' );
          $list_label = $is_news ? 'お知らせ一覧' : 'ギャラリー一覧';
          printf(
              '<a class="c-btn c-btn--outline p-entry__list" href="%s">%s<span class="c-btn__arrow" aria-hidden="true">→</span></a>',
              esc_url( $list_url ),
              esc_html( $list_label )
          );

          if ( $next ) {
              printf(
                  '<a class="c-btn c-btn--text p-entry__next" href="%s">%s<span class="u-vh">：%s</span><span class="c-arrow-line" aria-hidden="true"></span></a>',
                  esc_url( get_permalink( $next ) ),
                  esc_html( $next_label ),
                  esc_html( get_the_title( $next ) )
              );
          }
          ?>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
</div>

<?php
get_template_part( 'template-parts/cta' );
get_footer();

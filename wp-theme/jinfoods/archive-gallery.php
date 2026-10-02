<?php
/**
 * 食事ギャラリーの一覧（投稿タイプ gallery。/gallery/）
 *
 * 2026-10-01 に投稿のカテゴリ（/category/gallery/）から分けた。
 * 投稿タイプの定義は CPT UI で作り、「コードを取得」で inc/post-types.php に写した（CPT UI 側の定義は消してある）。
 * 格子と拡大表示は C「小さく並べて、押すと大きく」（DESIGN.md「食事ギャラリーのページ」）。
 * 事業内容へ送る帯は 2026-10-02 に格子の最後のマス（p-arcgal__end）へ移した（最後の行の端数を埋めるため）。
 * 全件を1ページに出すのは inc/setup.php の jinfoods_gallery_all_posts
 *
 * @package jinfoods
 */
get_header();
get_template_part( 'template-parts/pagehead' );
?>

<div class="l-under">
  <div class="l-inner l-under__body">
    <?php // Block（p-arcgal）を section に Mix する（一覧の Element の親） ?>
    <section class="l-under__block p-arcgal">
      <div class="l-under__prose">
        <p>行事食は毎月ご用意しています。刻み食・ムース食など、食事形態を変えても彩りと形を残すよう心がけています。</p>
      </div>

      <?php if ( ! have_posts() ) : ?>
        <div class="l-under__prose"><p>まだ投稿がありません。</p></div>

      <?php else : ?>
        <?php
        // 2026-10-01 作り直し（C「小さく並べて、押すと大きく」。DESIGN.md「食事ギャラリーのページ」）。
        // 押すと拡大表示（c-viewer）に大きな写真と説明。href は投稿ページ（JS が動かないときはそのまま開く）
        global $wp_query;
        ?>
        <p class="p-arcgal__count"><span class="p-arcgal__count-num"><?php echo esc_html( number_format_i18n( $wp_query->found_posts ) ); ?></span>枚</p>
        <ul class="p-arcgal__list">
          <?php
          $i = 0;
          while ( have_posts() ) :
              the_post();
              $i++;
              $thumb_id = get_post_thumbnail_id();
              $alt      = jinfoods_thumb_alt();
              $full     = jinfoods_thumb_url( get_the_ID(), 'jinfoods_square' );
          ?>
            <li class="p-arcgal__item">
              <a class="p-arcgal__link js-viewer-item" href="<?php the_permalink(); ?>" data-viewer-src="<?php echo esc_url( $full ); ?>" aria-haspopup="dialog">
                <?php
                if ( $thumb_id ) {
                    // 格子は最大 187px。WP が作った縮小版（300px など）から、端末に合う大きさを選ばせる
                    echo wp_get_attachment_image( $thumb_id, 'jinfoods_square', false, array(
                        'class'   => 'p-arcgal__img js-viewer-thumb',
                        'alt'     => $alt,
                        'sizes'   => '(min-width: 1140px) 187px, (min-width: 600px) 19vw, 32vw',
                        'loading' => $i > 6 ? 'lazy' : 'eager',
                    ) );
                } elseif ( $full ) {
                    printf( '<img class="p-arcgal__img js-viewer-thumb" src="%s" alt="%s" width="800" height="800"%s>', esc_url( $full ), esc_attr( $alt ), $i > 6 ? ' loading="lazy"' : '' );
                }
                ?>
                <span class="p-arcgal__veil" aria-hidden="true"><span class="p-arcgal__cap"><?php echo esc_html( $alt ); ?></span><span class="p-arcgal__mark"><svg class="p-arcgal__mark-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M8 3v10M3 8h10"/></svg></span></span>
              </a>
            </li>
          <?php endwhile; ?>
          <?php // 締めのマス：最後の行の残りのマスを埋める（何マス広げるかは CSS の nth-child。DESIGN.md「食事ギャラリーのページ」の __end） ?>
          <li class="p-arcgal__end"><a class="p-arcgal__end-link" href="<?php echo esc_url( home_url( '/service/' ) ); ?>"><span class="p-arcgal__end-body"><span class="p-arcgal__end-title">食事形態の対応範囲をご覧いただけます</span><span class="p-arcgal__end-text">常食から刻み食・ムース食まで、5段階に対応しています。</span></span><span class="p-arcgal__end-go">事業内容を<wbr>見る<span class="c-arrow-line c-arrow-line--sm" aria-hidden="true"></span></span></a></li>
        </ul>
        <dialog class="c-viewer js-viewer" aria-label="写真を大きく見る">
          <div class="c-viewer__bar js-viewer-outside">
            <button class="c-viewer__btn js-viewer-close" type="button" aria-label="閉じる"><svg class="c-viewer__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M3.5 3.5l9 9M12.5 3.5l-9 9"/></svg></button>
          </div>
          <figure class="c-viewer__figure js-viewer-outside">
            <div class="c-viewer__frame"><img class="c-viewer__img js-viewer-img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/noimage.jpg' ); ?>" alt="" width="800" height="800" loading="lazy"></div>
            <figcaption class="c-viewer__cap js-viewer-cap" aria-hidden="true"></figcaption>
          </figure>
          <div class="c-viewer__nav js-viewer-outside">
            <button class="c-viewer__btn js-viewer-step" type="button" data-step="-1" aria-label="前の写真"><svg class="c-viewer__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M10 3.5 5.5 8l4.5 4.5"/></svg></button>
            <p class="c-viewer__count js-viewer-count" aria-hidden="true"></p>
            <button class="c-viewer__btn js-viewer-step" type="button" data-step="1" aria-label="次の写真"><svg class="c-viewer__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M6 3.5 10.5 8 6 12.5"/></svg></button>
          </div>
          <p class="u-vh js-viewer-status" aria-live="polite"></p>
        </dialog>
      <?php endif; ?>
    </section>

  </div>
</div>

<?php
get_template_part( 'template-parts/cta' );
get_footer();

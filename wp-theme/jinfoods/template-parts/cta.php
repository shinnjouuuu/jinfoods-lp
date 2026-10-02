<?php
/**
 * 締めのCTA
 *
 * @package jinfoods
 */
$tel = jinfoods_tel();
// 採用ページだけは応募向けの案内にする（2026-10-01。施設の担当者向けの案内が、求職者のページの最後に出ていたため）。
// 文言は採用ページの本文にあった「応募・お問い合わせ」の箱と同じ。ほかのページは今までどおり
$is_recruit = is_page( 'recruit' );
?>
<section class="l-section l-section--sage l-section--wave p-contact" aria-labelledby="contact-title">
  <div class="p-contact__inner l-inner">
    <?php if ( $is_recruit ) : ?>
      <h2 class="p-contact__title" id="contact-title">応募・お問い合わせ</h2>
      <p class="p-contact__text">履歴書をご準備のうえ、お電話またはフォームからご連絡ください。</p>
    <?php else : ?>
      <h2 class="p-contact__title" id="contact-title">お食事サービスの導入・見直しを<br>ご検討の担当者さまへ</h2>
      <p class="p-contact__text">試食のご依頼、サービスに関するご相談、お見積りなど、お気軽にお問い合わせください。</p>
    <?php endif; ?>
    <?php if ( $is_recruit ) : ?>
      <div class="p-contact__cta">
        <a class="c-btn c-btn--primary u-full" data-cta="apply-form-band" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">フォームで応募<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--light u-full" data-cta="apply-tel-band" href="<?php echo esc_attr( $tel['href'] ); ?>">
          <span class="c-btn__tel-num"><?php echo esc_html( $tel['display'] ); ?></span>
          <span class="c-btn__tel-note"><?php echo esc_html( $tel['note'] ); ?></span>
        </a>
      </div>
    <?php else : ?>
      <?php // 施設の担当者向けの入口4つ（2026-10-01 D案）。言葉はお問い合わせの種類の選択肢と同じ ?>
      <div class="p-contact__doors">
        <a class="c-btn c-btn--primary" data-cta="trial-band" href="<?php echo esc_url( home_url( '/contact/#form' ) ); ?>">試食のご依頼<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--light" data-cta="consult-band" href="<?php echo esc_url( home_url( '/contact/#form' ) ); ?>">業務のご相談<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--light" data-cta="recruit-band" href="<?php echo esc_url( home_url( '/recruit/' ) ); ?>">採用について<span class="c-btn__arrow" aria-hidden="true">→</span></a>
        <a class="c-btn c-btn--tel c-btn--light c-btn--compact" data-cta="tel-band" href="<?php echo esc_attr( $tel['href'] ); ?>">
          <span class="c-btn__tel-num"><?php echo esc_html( $tel['display'] ); ?></span>
          <span class="c-btn__tel-note"><?php echo esc_html( $tel['note'] ); ?></span>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

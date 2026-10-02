<?php
/**
 * お問い合わせのフォーム（Contact Form 7）の読み込み
 *
 * フォームの中身は wp-theme/cf7/contact-form.html にあり、seed-cf7.php で入れる。
 * 見た目はテーマの Sass（_form.scss の末尾）が持つ。
 *
 * @package jinfoods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * フォームの HTML に <p> と <br> を足させない。
 * 中身は静的版と同じ形（fieldset・div・p）で書いてあり、足されると行の gap と余白が崩れる
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/**
 * CF7 の CSS は読まない。色が CF7 の青・緑・黄で、トークンに合わせるには詳細度で勝ち続けることになるため。
 * 要る分（読み上げ用の結果を隠す・入力の誤り・送信のあとの知らせ）は _form.scss の末尾に書いてある
 */
add_filter( 'wpcf7_load_css', '__return_false' );

/**
 * CF7 の JS（約 20KB）は、本文にフォームがあるページでだけ読む。既定では全ページで読まれる
 */
add_filter( 'wpcf7_load_js', 'jinfoods_has_contact_form' );
function jinfoods_has_contact_form(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	return has_shortcode( $content, 'contact-form-7' );
}

/**
 * スパム対策（Cloudflare Turnstile）の JS も、フォームのあるページでだけ読む。
 * CF7 は鍵が入っていると全ページで challenges.cloudflare.com の api.js を読む（お問い合わせ > インテグレーション）。
 * CF7 が読み込みを登録した（優先度 10）あとで外す
 */
add_action( 'wp_enqueue_scripts', 'jinfoods_turnstile_only_with_form', 20 );
function jinfoods_turnstile_only_with_form(): void {
	if ( ! jinfoods_has_contact_form() ) {
		wp_dequeue_script( 'cloudflare-turnstile' );
	}
}

/**
 * CF7 の送信の前に2つ足す。document の捕捉（capture）で受け、CF7 の処理（フォームの change と submit）より先に動く。
 * CF7 の JS に付けるので、フォームのあるページでだけ出る
 *  1. 電話番号の欄の全角の数字・記号を半角に直す。CF7 は半角の数字しか電話番号として受け付けず、
 *     パソコンで日本語入力のまま「０９７－…」と打つと「電話番号を入力してください。」とはじかれるため
 *  2. 送信中（フォームに submitting が付いている間）の2回目の送信を止める。CF7 は送信中かを確かめずに送り直すので、
 *     Enter の連打で同じメールが2通届く。マウスとタップは Sass（pointer-events）で止めてある
 */
add_action( 'wp_enqueue_scripts', 'jinfoods_cf7_tel_halfwidth', 20 );
function jinfoods_cf7_tel_halfwidth(): void {
	$js = <<<'JS'
(() => {
  const toHalf = (input) => {
    input.value = input.value
      .replace(/[０-９＋（）]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0xfee0))
      .replace(/[－ー―‐−]/g, '-')
      .replace(/　/g, ' ');
  };
  document.addEventListener('change', (e) => {
    if (e.target instanceof HTMLInputElement && e.target.type === 'tel') toHalf(e.target);
  }, true);
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('wpcf7-form')) return;
    if (form.classList.contains('submitting')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return;
    }
    form.querySelectorAll('input[type="tel"]').forEach(toHalf);
  }, true);
})();
JS;
	wp_add_inline_script( 'contact-form-7', $js, 'before' );
}

/**
 * 欄の補足文を、その欄に aria-describedby で結ぶ（読み上げで欄に入ったとき補足も読む。WCAG 1.3.1）。
 * 静的版は textarea に aria-describedby="f-body-hint" と書いてあるが、CF7 のタグはこの属性を受け付けない。
 * そこで補足文の側に data-describes="欄の id" を書き（cf7/contact-form.html）、ここで結ぶ。
 *
 * 一度付けるだけでは消える：CF7 6.1 は入力の誤りを出すとき欄の aria-describedby を誤りの文の id で上書きし、
 * 誤りが消えると属性ごと外す（送信のときも、入力を変えたときの確かめでも）。
 * だから属性が書き換わるたびに補足文の id を末尾に足し直す（誤りがあるときは「誤りの文 → 補足文」の順に読まれる）
 */
add_action( 'wp_enqueue_scripts', 'jinfoods_cf7_hint_describedby', 20 );
function jinfoods_cf7_hint_describedby(): void {
	$js = <<<'JS'
(() => {
  const link = () => {
    document.querySelectorAll('.wpcf7-form [data-describes]').forEach((hint) => {
      const field = document.getElementById(hint.dataset.describes);
      if (!field || !hint.id) return;
      const ensure = () => {
        const ids = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        if (ids.includes(hint.id)) return;  // 足し直した自分の書き換えでもう一度呼ばれるので、あれば何もしない
        field.setAttribute('aria-describedby', [...ids, hint.id].join(' '));
      };
      ensure();
      new MutationObserver(ensure).observe(field, { attributes: true, attributeFilter: ['aria-describedby'] });
    });
  };
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', link, { once: true }) : link();
})();
JS;
	wp_add_inline_script( 'contact-form-7', $js, 'before' );
}

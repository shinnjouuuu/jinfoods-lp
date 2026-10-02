<?php
/**
 * お問い合わせのフォーム（Contact Form 7）を作る・更新する（wp-cli の eval-file で実行する）
 *
 *   wp eval-file seed-cf7.php --user=<管理者のログイン名>
 *
 * フォームの中身は cf7/contact-form.html、届くメールの本文は cf7/mail.txt。
 * 題名「お問い合わせ」のフォームを1つだけ持ち、何度流しても同じものを更新する。
 * 固定ページの本文（content/contact.html）は [contact-form-7 title="お問い合わせ"] でこのフォームを呼ぶので、題名は変えない。
 * seed-pages.php より先に流す（フォームが無いと、お問い合わせのページに「404 "Not Found"」と出る）。
 *
 * 送信先は、先方確認までは WordPress の管理者メール（設定 > 一般）。決まったら $to を書き換えて流し直す。
 * 管理画面で直したメッセージ（送信完了の文など）は上書きしない。
 *
 * --user を付けるのは、管理者でないとフォームの class 属性が保存時に消されるため（unfiltered_html 権限が要る）。
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "wp-cli から実行してください: wp eval-file seed-cf7.php --user=<管理者>\n" );
	return;
}

if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
	WP_CLI::error( 'Contact Form 7 が有効になっていません。プラグイン > Contact Form 7 を有効化してから流してください。' );
}

if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( '--user=<管理者のログイン名> を付けて実行してください。付けないとフォームの class が消えます。' );
}

$title = 'お問い合わせ';
$dir   = __DIR__ . '/cf7';
$form  = trim( (string) file_get_contents( $dir . '/contact-form.html' ) );
$body  = trim( (string) file_get_contents( $dir . '/mail.txt' ) );

// 送信先（先方確認待ち）。決まるまでは管理者メールに届く
$to = '[_site_admin_email]';

$contact_form = wpcf7_get_contact_form_by_title( $title );
$is_new       = ! $contact_form;
if ( $is_new ) {
	// 新しく作るときだけ、サイトの言語（日本語）の既定のメッセージを入れる
	$contact_form = WPCF7_ContactForm::get_template( array( 'title' => $title ) );
}

$contact_form->set_properties( array(
	'form'                => $form,
	'mail'                => array_merge( (array) $contact_form->prop( 'mail' ), array(
		'active'             => true,
		'subject'            => '[_site_title]「[subject]」のお問い合わせ',
		// 送り主はサイトのドメインのアドレス（CF7 の既定と同じ）。送信した人のアドレスにすると迷惑メール扱いされる
		'sender'             => '[_site_title] <' . WPCF7_ContactFormTemplate::from_email() . '>',
		'recipient'          => $to,
		'body'               => $body,
		'additional_headers' => 'Reply-To: [your-email]',
		'attachments'        => '',
		'use_html'           => false,
		'exclude_blank'      => false,
	) ),
	// 自動返信は使わない（文面が先方確認前のため）
	'mail_2'              => array_merge( (array) $contact_form->prop( 'mail_2' ), array( 'active' => false ) ),
	// 同意のチェックが無いとき、送信ボタンを押せなくするのではなく、ほかの欄と同じく文で知らせる
	'additional_settings' => 'acceptance_as_validation: on',
) );
$contact_form->save();

WP_CLI::success( sprintf(
	'%s: %s（ID %d・送信先 %s＝%s）',
	$is_new ? 'フォームを作成' : 'フォームを更新',
	$title,
	$contact_form->id(),
	$to,
	get_option( 'admin_email' )
) );

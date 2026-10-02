# JIN foods WordPress テーマ

静的版（`../index.html` ほか12ページ）を WordPress テーマにしたもの。
CSS と JS は静的版と同じものを `assets/` に入れている（Sass の正本は `../sass/`）。

## Local への入れ方

```bash
./install-to-local.sh <Localのサイト名>
```

サイトが無ければ Local アプリで先に作る。作ったら上記を実行し、
Local でサイトを起動して 外観 > テーマ から「JIN foods」を有効化する。

## 初期データの投入

wp-cli で実行する。カテゴリ・固定ページ・現行サイトの実際のお知らせ9件（お知らせ4件は「投稿」、行事食5件は食事ギャラリー）が入る。
2026-10-01 より前に流したサイト（行事食が「投稿」の gallery カテゴリにある）は、もう一度流すと食事ギャラリーへ移り、空になった gallery カテゴリは消える。

```bash
SITE=jinfoods
PUB="$HOME/Local Sites/$SITE/app/public"
WP=/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar
SOCK=$(find "$HOME/Library/Application Support/Local/run" -name mysqld.sock | head -1)

php -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
  "$WP" --path="$PUB" eval-file seed.php
```

### 食事ギャラリーのダミー（Local の確認用。本番では流さない）

行事食が5件だけだと /gallery/ の格子や拡大表示の送りが運用の量で正しいか見えないので、LP の gallery.html と同じ36枚になるまでダミーを足す（LP の写真と説明を使う。毎月1件の「◯月行事食」）。
足したものには `_jinfoods_dummy` が付いていて、`remove` でそれだけ消える（seed.php の5件は残る）。

```bash
php -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
  "$WP" --path="$PUB" eval-file seed-dummy-gallery.php          # 足す
php -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
  "$WP" --path="$PUB" eval-file seed-dummy-gallery.php remove   # 消す
```

### お問い合わせのフォーム（Contact Form 7）

プラグイン Contact Form 7 を有効にしてから流す。題名「お問い合わせ」のフォームを1つ作る（2回目からは同じものを更新）。
**固定ページの本文より先に流す**（お問い合わせの本文がこのフォームを題名で呼ぶので、無いと「404 "Not Found"」と出る）。

```bash
php -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
  "$WP" --path="$PUB" eval-file seed-cf7.php --user=user
```

- フォームの中身は `cf7/contact-form.html`、届くメールの本文は `cf7/mail.txt`。静的版（`../contact.html`）のフォームと同じ class・同じ言葉にしてあり、入力欄だけ CF7 のタグ。**静的版のフォームを変えたら、こちらも同じに直す**
- 送信先は、先方確認までは WordPress の管理者メール（設定 > 一般）。決まったら `seed-cf7.php` の `$to` を書き換えて流し直す。送り主はサイトのドメインのアドレス、返信先（Reply-To）は送信した人
- 送信完了などのメッセージは、最初に作ったときの日本語の既定のまま。管理画面で直したものは流し直しても上書きしない
- 同意のチェックは `acceptance_as_validation: on`（チェックするまで送信ボタンを押せなくする CF7 の既定をやめ、送ったときに文で知らせる）
- CF7 の CSS は読まない。JS は本文にフォームがあるページでだけ読む（`inc/contact-form.php`）。見た目は `../sass/object/project/_form.scss` の末尾
- 電話番号の欄は、全角の数字・ハイフンを半角に直してから CF7 が確かめる（`inc/contact-form.php`。CF7 は半角しか受け付けない）
- `Contact form 1` は CF7 を有効にしたときに自動でできる見本で、使っていない
- Local で送ったメールは Local の Mailpit（サイトの Tools タブ）に届く。外には出ない
- スパム対策は Cloudflare Turnstile。鍵は**お問い合わせ → インテグレーション → Turnstile**に入れる（リポジトリには置かない）。フォームの中身の `[turnstile size:flexible theme:light]` が、同意の枠と送信ボタンのあいだに枠を出す（書かないと CF7 がフォームのいちばん上に入れる）。鍵が入っていないあいだ、このタグは何も出さない
  - 鍵の取り方：Cloudflare の管理画面 → Turnstile → Add widget。Hostname に本番のドメイン、Widget Mode は Managed、Pre-clearance はいいえ。Create で Site Key と Secret Key が出る
  - Local は Cloudflare の試験用の鍵（必ず通る）を入れてある：Site Key `1x00000000000000000000AA`／Secret Key `1x0000000000000000000000000000000AA`。本番の鍵は localhost では動かない。必ず落ちる秘密鍵 `2x0000000000000000000000000000000AA` に替えると、落ちたときの表示を確かめられる
  - CF7 は鍵が入ると全ページで challenges.cloudflare.com の api.js を読むので、フォームの無いページでは外している（`inc/contact-form.php`）

### 固定ページの本文（会社概要・事業内容・採用情報・プライバシーポリシー・お問い合わせ）

本文の正本は静的版の HTML。`export-content.py` で `content/` に書き出し、`seed-pages.php` で流し込む。
あわせて SEO SIMPLE PACK のタイトル・説明文（トップ・固定ページ・ギャラリー／お知らせ一覧）も静的版の値に揃える。
食事ギャラリーの一覧（/gallery/）は SEO SIMPLE PACK が既定で noindex にするので、ここで検索に出す設定に戻す。

```bash
cd ..                                   # jinfoods-lp/
python3 wp-theme/export-content.py      # 静的版を直したら毎回書き出し直す
cd wp-theme
php -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
  "$WP" --path="$PUB" eval-file seed-pages.php --user=user
```

- `--user` は管理者のログイン名。付けないと地図の iframe や class が保存時に消えるので、スクリプト側で止めている
- 本文は「カスタムHTML」ブロック1つで入る。管理画面から編集はできるが、HTML を直す形になる
- 同じ内容なら更新しないので、何度流してもリビジョンは増えない
- トップの説明文からは施設数を外している（8施設か9施設か＝花埜家の扱いが先方確認待ち）
- 採用情報の「働き方の条件」と「募集要項」の表は、本文では `[jinfoods_recruit_conds]` `[jinfoods_recruit_requirements]` になる（`export-content.py` が置き換える）。
  値は管理画面の サイト設定 > 採用情報 で直す。トップの採用の節も同じ値を出す。一度も保存していなければ `inc/fields.php` の既定値（静的版と同じ文言）が出る
  - 静的版の文言を変えたら、`inc/fields.php` の `jinfoods_recruit_defs()` も合わせる（保存前の既定値になるため）
  - 空にした募集要項の行は消さずに「（ご記入をお願いします）」と黄色で出す。募集時に明示が必要な項目のため

## AI（WordPress.org の「AI」プラグイン）

記事の全文は書かせない。行事食・受託開始など事実の報告なので、作り話が混ざると困る。使うのは次の2つだけ。

| 機能 | 使う場面 |
|---|---|
| 代替テキストの作成 | ギャラリーの写真。メディアの編集画面とメディア選択の画面にボタンが出る |
| 題名の案 | 投稿の題名欄にカーソルを置くと「Generate」が出る |

Local では有効にしてある（`wpai_features_enabled` と各機能の `wpai_feature_<ID>_enabled` を 1）。動かすには次が要る。

1. 設定 > コネクタ で Anthropic の API キーを入れる（料金は API キーの持ち主に掛かる。誰が持つかは先方と決める）
2. 設定 > AI で、上の2つ以外が無効のままか確かめる

- 試験的なプラグインで、配布元も本番では自己責任としている。本番に入れる前に Local で一通り試す
- メタディスクリプションの作成は SEO SIMPLE PACK に対応していない（連携先は Yoast・Rank Math・AIOSEO・SEOPress）ので使わない

### Local への反映の注意

`install-to-local.sh` はテーマを消してから入れ直す。Local 側だけにあるファイルがあると消えるので、
実行前に `diff -rq jinfoods "$HOME/Local Sites/<サイト>/app/public/wp-content/themes/jinfoods"` で差が無いことを確かめる。

Local を再起動するとソケットのパスが変わるので、`SOCK` は都度取り直す。

## 現行サイトの失敗を持ち込まないための決めごと

| | 現行サイト | このテーマ |
|---|---|---|
| ベース | Twenty Twelve を直接改造 | 独立テーマ。更新でカスタマイズが消えない |
| `<title>` | header.php に直書き（全9ページ同一） | `add_theme_support('title-tag')` に任せる |
| 代替画像 | テーマ内の存在しないパスを参照して404 | `jinfoods_thumb_url()` で存在確認。無ければ枠ごと出さない |
| h1 | 1ページに3〜14個 | ロゴはトップだけ h1。下層はページ見出しのみ |
| REST | 管理者ユーザー名2件が外部から取得できた | 未ログイン時は users エンドポイントを外す |
| PHP | 7.4（サポート終了） | 8.1 以上を要求 |

## テンプレートの対応

| ファイル | 役割 |
|---|---|
| `front-page.php` | トップ（LP）。お知らせ3件・ギャラリー8件は `WP_Query` |
| `page.php` | 固定ページ（本文は管理画面から編集） |
| `category.php` | お知らせの一覧（行リスト） |
| `archive-gallery.php` | 食事ギャラリーの一覧（/gallery/。小さな正方形の格子＋拡大表示）。全件を1ページに出す |
| `archive.php` | そのほかの一覧（日付別など） |
| `single.php` | お知らせの記事（題が h1）と、食事ギャラリーの1件（題が h2。h1 はページ見出し） |
| `inc/post-types.php` | 投稿タイプ（受託施設 `facility`・食事ギャラリー `gallery`）。CPT UI で作ったものも「コードを取得」でここに写し、CPT UI 側の定義は消す |
| `404.php` | 見つからないとき |
| `inc/template-tags.php` | パンくず・ページ見出し・サムネ取得 |

## まだやっていないこと

- お問い合わせフォーム（Contact Form 7）の残り。フォーム・同意チェック・見た目・送信は 2026-10-01 に Local で通した。残りは本番の WordPress に入れるときに決めること
  - **送信先のメールアドレス**（先方確認待ち。いまは管理者メール）
  - **スパム対策の本番の鍵**。Turnstile で組んである（Local は試験用の鍵）。誰の Cloudflare アカウントで取るかを先方と決める。プライバシーポリシーに外部サービス（Cloudflare）の記載が要るかも確認する
  - 自動返信（送った人への控え）を出すか。出すなら文面を先方に確認する（いまは出さない）
  - 本番のサーバー（heteml）から送ったメールが届くか。送り主のドメインと送信元のサーバーが合っていないと迷惑メールに入る
  - 設定 > 一般 のタイムゾーンを「東京」、日付の形式を日本の形に。Local は UTC のままで、メールの送信日時が9時間ずれて「10月 1, 2026 9:19 am」の形で出る
- 既存URL（`?p=998` `?page_id=9`）からの301リダイレクト設計
- 構造化データ（Organization / LocalBusiness / JobPosting）
- GA4

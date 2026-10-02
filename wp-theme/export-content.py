#!/usr/bin/env python3
"""
LP（静的HTML）の固定ページ本文を、WordPress に流し込める形で書き出す。

    python3 wp-theme/export-content.py      # jinfoods-lp/ で実行

書き出すもの（wp-theme/content/）
  about.html / service.html / recruit.html / privacy.html / contact.html
      LP の <div class="l-inner l-under__body"> の中身を「カスタムHTML」ブロック1つに包んだもの
  meta.json
      各ページの <title> と description（SEO SIMPLE PACK に入れる）

WordPress への反映は seed-pages.php（wp eval-file seed-pages.php --user=<管理者>）。

変換のきまり
  ・LP のページ間リンク（about.html など）は WordPress のパーマリンクに置き換える
  ・画像はテーマ内の assets/images を参照する（WordPress をドメイン直下に置く前提のルート相対）
    <picture> の <source srcset> も同じ。テーマに無い画像を指していたら止める（WebP はテーマに無いことがある）
  ・制作メモの HTML コメントは本文に持ち込まない（管理画面の編集欄が読みにくくなるため）
  ・トップの説明文から施設数を外す。8施設か9施設（花埜家）かが先方確認待ちのため
  ・採用情報の「働き方の条件」と「募集要項」の表はショートコードに置き換える。
    値は WordPress のサイト設定 > 採用情報で持つ（給与を HTML で直させないため）。既定値は inc/fields.php
  ・お問い合わせのフォームは Contact Form 7 のショートコードに置き換える（LP のフォームは送らない見本）。
    フォームの中身は cf7/contact-form.html で、seed-cf7.php が入れる。LP のフォームを変えたら、そちらも同じに直す
"""
import html
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent          # jinfoods-lp/
OUT = ROOT / "wp-theme" / "content"
PAGES = ["about", "service", "recruit", "privacy", "contact"]

LINKS = [
    ("index.html#facility", "/#facility"),
    ("contact.html", "/contact/"),
    ("gallery.html", "/gallery/"),  # 食事ギャラリーは投稿タイプ gallery の一覧（2026-10-01 にカテゴリから分けた）
    ("news.html", "/category/news/"),
    ("about.html#map", "/about/#map"),
    ("about.html", "/about/"),
    ("service.html", "/service/"),
    ("recruit.html", "/recruit/"),
    ("privacy.html", "/privacy/"),
    ("index.html", "/"),
]
IMG = "/wp-content/themes/jinfoods/assets/images/"
THEME_IMAGES = ROOT / "wp-theme" / "jinfoods" / "assets" / "images"


def body_of(src: str) -> str:
    """<div class="l-inner l-under__body"> の中身だけを取り出す"""
    start = src.index('<div class="l-inner l-under__body">')
    depth = 0
    for m in re.finditer(r"<(/?)div\b[^>]*>", src[start:]):
        depth += -1 if m.group(1) else 1
        if depth == 0:
            end = start + m.end()
            break
    block = src[start:end]
    return block[block.index(">") + 1 : block.rindex("</div>")]


def title_desc(src: str) -> tuple[str, str]:
    t = html.unescape(re.search(r"<title>(.*?)</title>", src, re.S).group(1).strip())
    d = html.unescape(re.search(r'<meta name="description" content="([^"]*)"', src).group(1))
    return t, d


def convert(inner: str) -> str:
    inner = re.sub(r"<!--.*?-->", "", inner, flags=re.S)
    # 制作側の申し送り（静的版の見本にだけ置く社内メモ）は WP に入れない（2026-10-02、4スキルレビュー P1）。
    # 採用の「（ご記入をお願いします）」は WP でも黄色で出す決まりなので外さない（README「固定ページの本文」）
    inner = re.sub(r'\s*<p class="c-prose__note">\s*<strong>【制作側からの申し送り】</strong>.*?</p>', "", inner, flags=re.S)
    for a, b in LINKS:
        inner = inner.replace(f'href="{a}"', f'href="{b}"')
    inner = inner.replace('src="images/', f'src="{IMG}')
    # srcset はカンマ区切りで候補が並ぶ（"images/a.webp 1x, images/a@2x.webp 2x"）。各候補の先頭を置き換える
    inner = re.sub(
        r'srcset="([^"]*)"',
        lambda m: 'srcset="' + re.sub(r"(^|,)(\s*)images/", lambda c: c.group(1) + c.group(2) + IMG, m.group(1)) + '"',
        inner,
    )
    inner = re.sub(r"\n[ \t]*\n+", "\n", inner).strip("\n")
    lines = inner.split("\n")
    indent = min(len(l) - len(l.lstrip(" ")) for l in lines if l.strip())
    inner = "\n".join(l[indent:] for l in lines)

    urls = []
    for attr, value in re.findall(r'(href|src|srcset)="([^"]+)"', inner):
        if attr == "srcset":
            urls += [c.split()[0] for c in value.split(",") if c.strip()]
        else:
            urls.append(value)

    # LP 側の相対パスが残っていたら止める（置き換え漏れ）
    left = [u for u in urls if not re.match(r"(/|https?:|tel:|mailto:)", u)]
    if left:
        raise SystemExit(f"置き換えていないパスがあります: {left}")

    # テーマに無い画像を指していたら止める。<picture> の WebP が 404 だと <img> の JPEG に戻らず、画像が出ない
    missing = sorted({u for u in urls if u.startswith(IMG) and not (THEME_IMAGES / u[len(IMG):]).is_file()})
    if missing:
        raise SystemExit(f"テーマの assets/images に無い画像があります: {missing}")
    return inner


RECRUIT_SHORTCODES = [
    (r'<ul class="p-recruit__conds[^"]*">.*?</ul>', "[jinfoods_recruit_conds]"),
    (r'<dl class="c-deflist">.*?</dl>', "[jinfoods_recruit_requirements]"),
]


def to_shortcodes(inner: str) -> str:
    for pattern, code in RECRUIT_SHORTCODES:
        inner, n = re.subn(pattern, code, inner, flags=re.S)
        # 静的版の形が変わって置き換えられないと、表が HTML のまま入って入力欄が効かなくなる
        if n != 1:
            raise SystemExit(f"recruit.html で {code} に置き換える箇所が {n} 件です（1件のはず）")
    return inner


# 題名で呼ぶ（CF7 の id はサイトごとに変わるため）。題名は seed-cf7.php と同じにする
CONTACT_FORM = '[contact-form-7 title="お問い合わせ" html_class="p-form"]'


def to_contact_form(inner: str) -> str:
    # class に js-sample-form などが足されても当たるように、p-form のあとに続く class は許す（2026-10-01）
    inner, n = re.subn(r'<form class="p-form(?: [^"]*)?"[^>]*>.*?</form>', CONTACT_FORM, inner, flags=re.S)
    # 置き換えられないと、送れない見本のフォームがそのまま入る
    if n != 1:
        raise SystemExit(f"contact.html でフォームを置き換える箇所が {n} 件です（1件のはず）")
    return inner


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    meta = {}

    for slug in PAGES:
        src = (ROOT / f"{slug}.html").read_text(encoding="utf-8")
        inner = convert(body_of(src))
        if slug == "recruit":
            inner = to_shortcodes(inner)
        if slug == "contact":
            inner = to_contact_form(inner)
        (OUT / f"{slug}.html").write_text(
            "<!-- wp:html -->\n" + inner + "\n<!-- /wp:html -->\n", encoding="utf-8"
        )
        t, d = title_desc(src)
        meta[slug] = {"title": t, "description": d}
        print(f"{slug}: {len(inner)}字 / {t}")

    t, d = title_desc((ROOT / "index.html").read_text(encoding="utf-8"))
    d2 = d.replace("の8施設で稼働中。", "の施設で稼働中。")
    meta["_home"] = {
        "title": t,
        "description": d2,
        "note": "施設数（8か9か）は先方確認待ちなので説明文から外している",
    }
    for cat in ("gallery", "news"):
        t, d = title_desc((ROOT / f"{cat}.html").read_text(encoding="utf-8"))
        meta[f"_cat_{cat}"] = {"title": t, "description": d}

    (OUT / "meta.json").write_text(json.dumps(meta, ensure_ascii=False, indent=2), encoding="utf-8")
    print("meta.json を書き出しました")


if __name__ == "__main__":
    main()

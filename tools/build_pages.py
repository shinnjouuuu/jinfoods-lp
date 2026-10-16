"""Builds the sub-pages (service.html, meals.html, ...) next to index.html.

The header, footer and contact block are copied from index.html, so a change made
there reaches every page the next time this runs:

    python3 tools/build_pages.py

Each page body lives in tools/pages/<name>.html. Its first lines are a comment
with the page settings (title, English label, lead, photo). The meal gallery and
the news list are generated from tools/data/*.json.
"""
from __future__ import annotations

import json
import re
import sys
from html import escape
from pathlib import Path
from facilities import facility_section

ROOT = Path(__file__).resolve().parent.parent
TOOLS = ROOT / 'tools'
PAGES = ['service', 'meals', 'company', 'recruit', 'news', 'contact', 'privacy', 'sitemap']
SITE_NAME = '株式会社JIN foods'


def block(html: str, start: str, end: str) -> str:
    """Returns the text from the first `start` up to and including the next `end`."""
    begin = html.find(start)
    if begin < 0:
        raise ValueError(f'index.html has no {start!r}')
    finish = html.find(end, begin)
    if finish < 0:
        raise ValueError(f'index.html has no {end!r} after {start!r}')
    return html[begin:finish + len(end)]


def read_settings(source: str) -> tuple[dict[str, str], str]:
    match = re.match(r'\s*<!--(.*?)-->\s*', source, re.S)
    if not match:
        raise ValueError('page source must start with a settings comment')
    settings = {}
    for line in match.group(1).strip().splitlines():
        key, _, value = line.partition(':')
        settings[key.strip()] = value.strip()
    return settings, source[match.end():]


def head(template: str, title: str, description: str) -> str:
    full_title = f'{title}｜{SITE_NAME}'
    text = block(template, '<!doctype html>', '</head>')
    text = re.sub(r'<!-- home-facility-assets:start -->.*?<!-- home-facility-assets:end -->', '', text, flags=re.S)
    text = re.sub(r'<title>.*?</title>', f'<title>{escape(full_title)}</title>', text, count=1)
    for name in ('name="description"', 'property="og:description"'):
        text = re.sub(f'(<meta {name} content=")[^"]*', lambda m: m[1] + escape(description, quote=True), text)
    text = re.sub(r'(<meta property="og:title" content=")[^"]*', lambda m: m[1] + escape(full_title, quote=True), text)
    return text.replace('<link rel="stylesheet" href="css/motion.css">',
                        '<link rel="stylesheet" href="css/motion.css">\n  <link rel="stylesheet" href="css/pages.css">')


def header(template: str, page: str) -> str:
    text = block(template, '<header class="site-header">', '</header>')
    # Mark the current page in the menu (nav links only, not the logo).
    return text.replace(f'<li><a href="{page}.html">', f'<li><a href="{page}.html" aria-current="page">', 1)


def page_hero(settings: dict[str, str]) -> str:
    """Page title block. With a photo, the text sits on the photo like site1's
    sub-pages; `tone: light` puts dark text on a pale wash, otherwise white on dark."""
    lead = f'\n        <p class="page-lead">{settings["lead"]}</p>' if settings.get('lead') else ''
    copy = f'''<div class="page-hero-copy">
        <nav class="breadcrumbs" aria-label="パンくずリスト"><a href="index.html">トップ</a><span aria-hidden="true">/</span><span aria-current="page">{escape(settings["title"])}</span></nav>
        <p class="label"><span class="label-en">{escape(settings["en"])}</span></p>
        <h1 class="page-title">{escape(settings["title"])}</h1>{lead}
      </div>'''
    if not settings.get('photo'):
        return f'<header class="page-hero page-hero--plain">\n      {copy}\n    </header>'
    file, width, height, alt = settings['photo'].split('|')
    tone = 'light' if settings.get('tone') == 'light' else 'dark'
    photo = (f'<img class="page-hero-photo" src="assets/{file}" alt="{escape(alt, quote=True)}" '
             f'width="{width}" height="{height}" fetchpriority="high">')
    return f'''<header class="page-hero page-hero--photo page-hero--{tone}">
      {photo}
      {copy}
    </header>'''


def paragraphs(text: str) -> str:
    return ''.join(f'<p>{escape(part).replace(chr(10), "<br>")}</p>' for part in text.split('\n\n') if part.strip())


# The meals page reads like a written menu: one chapter per kind of dish, in this order.
MEAL_CHAPTERS = [('daily', 'Daily'), ('seasonal', 'Seasonal'), ('dessert', 'Dessert'), ('buffet', 'Buffet')]
KANJI_NUMBERS = ['一', '二', '三', '四', '五', '六']


def dish_image(dish: dict) -> tuple[str, int, int]:
    image_name = dish.get('image', f'img_{dish["id"]}.webp')
    width, height = photo_size(image_name) if 'image' in dish else (dish['width'], dish['height'])
    return image_name, width, height


def dish_card(dish: dict, category: str) -> str:
    name = escape(dish['name'])
    note = escape(dish['note'])
    image_name, width, height = dish_image(dish)
    return f'''
          <li class="dish" id="img_{dish["id"]}" data-category="{dish["category"]}">
            <button class="dish-open" type="button" aria-haspopup="dialog">
              <figure class="dish-photo"><img src="assets/{image_name}" alt="{escape(dish["name"], quote=True)}" width="{width}" height="{height}" loading="lazy" decoding="async"></figure>
              <p class="dish-tag">{category}</p>
              <h4 class="dish-name">{name}</h4>
            </button>
            <p class="dish-note" hidden>{note}</p>
          </li>'''


def meal_gallery() -> str:
    data = json.loads((TOOLS / 'data' / 'meals.json').read_text())
    categories = data['categories']
    chapters = [(key, en, [dish for dish in data['dishes'] if dish['category'] == key])
                for key, en in MEAL_CHAPTERS]
    chapters = [chapter for chapter in chapters if chapter[2]]
    links = ''.join(
        f'<li><a href="#chapter-{key}"><span class="dish-index-num">{KANJI_NUMBERS[i]}</span>'
        f'<span class="dish-index-name">{categories[key]}</span><span class="dish-index-count">{len(dishes)}品</span></a></li>'
        for i, (key, _en, dishes) in enumerate(chapters))
    sections = ''.join(f'''
      <section class="dish-chapter" id="chapter-{key}" aria-labelledby="chapter-{key}-title">
        <header class="dish-chapter-head">
          <span class="dish-chapter-num" aria-hidden="true">{KANJI_NUMBERS[i]}</span>
          <h3 id="chapter-{key}-title" class="dish-chapter-title">{categories[key]}</h3>
          <span class="dish-chapter-en" aria-hidden="true">{en}</span>
        </header>
        <ul class="dish-grid">{''.join(dish_card(dish, categories[key]) for dish in dishes)}
        </ul>
      </section>''' for i, (key, en, dishes) in enumerate(chapters))
    return f'''<nav class="dish-index" aria-label="お品書きの目次"><ul>{links}</ul></nav>{sections}'''


def load_posts() -> list[dict]:
    """All posts, newest first, whatever order news.json lists them in."""
    posts = json.loads((TOOLS / 'data' / 'news.json').read_text())
    return sorted(posts, key=lambda post: post['date'], reverse=True)


def post_url(post: dict) -> str:
    return f'news-{post["id"]}.html'


def post_date(post: dict) -> str:
    return post['date'].replace('-', '.')


def photo_size(name: str) -> tuple[int, int]:
    """Pixel size of an image in assets/, so the page reserves the right space."""
    try:
        from PIL import Image
        with Image.open(ROOT / 'assets' / name) as image:
            return image.size
    except (ImportError, OSError):
        return 1200, 900


def news_card_photo(post: dict) -> str:
    """Show the whole lead photo; a post without photos shows the logo."""
    if post['images']:
        photo = post['images'][0]
        width, height = photo_size(photo)
        return (f'<figure class="news-card-photo"><img src="assets/{photo}" alt="" '
                f'width="{width}" height="{height}" loading="lazy" decoding="async"></figure>')
    return ('<figure class="news-card-photo news-card-photo--blank"><img src="assets/jin-logo-dark.png" alt="" '
            'width="532" height="627" loading="lazy" decoding="async"></figure>')


def news_list() -> str:
    cards = ''.join(f'''
        <li class="news-card">
          <a href="{post_url(post)}">
            {news_card_photo(post)}
            <div class="news-card-body">
              <time class="news-card-date" datetime="{post["date"]}">{post_date(post)}</time>
              <h2 class="news-card-title">{escape(post["title"])}</h2>
            </div>
          </a>
        </li>''' for post in load_posts())
    return f'<ul class="news-cards">{cards}\n      </ul>'


def post_neighbours(post: dict, posts: list[dict]) -> str:
    """Links to the newer and older posts, and back to the list."""
    index = posts.index(post)
    newer = posts[index - 1] if index > 0 else None
    older = posts[index + 1] if index + 1 < len(posts) else None
    link = lambda other, cls, label: (
        f'<a class="post-nav-link post-nav-link--{cls}" href="{post_url(other)}">'
        f'<span class="post-nav-label">{label}</span><span class="post-nav-title">{escape(other["title"])}</span></a>'
        if other else '<span></span>')
    return f'''<nav class="post-nav" aria-label="ほかのお知らせ">
        {link(newer, "newer", "新しいお知らせ")}
        <a class="post-nav-back" href="news.html">お知らせ一覧へ</a>
        {link(older, "older", "前のお知らせ")}
      </nav>'''


def post_page(post: dict, posts: list[dict], template: str) -> str:
    title = post['title']
    summary = post['body'].split('\n')[0][:110] if post['body'] else f'{title}のお知らせです。'
    photos = ''.join(
        '<figure><img src="assets/{0}" alt="{1}" width="{2[0]}" height="{2[1]}" loading="lazy" decoding="async"></figure>'
        .format(name, escape(title, quote=True), photo_size(name))
        for name in post['images'])
    body = f'<div class="post-body prose">{paragraphs(post["body"])}</div>' if post['body'] else ''
    footer = block(template, '<footer class="site-footer">', '</html>')
    contact = block(template, '<section class="contact"', '</section>')
    return f'''{head(template, title, summary)}
<body>
  <a class="skip-link" href="#main">本文へスキップ</a>

  {header(template, 'news')}

  <main id="main" class="page page--post">
    <header class="page-hero page-hero--plain post-hero">
      <div class="page-hero-copy">
        <nav class="breadcrumbs" aria-label="パンくずリスト"><a href="index.html">トップ</a><span aria-hidden="true">/</span><a href="news.html">お知らせ</a><span aria-hidden="true">/</span><span aria-current="page">{escape(title)}</span></nav>
        <p class="label"><span class="label-en">News</span></p>
        <time class="post-hero-date" datetime="{post["date"]}">{post_date(post)}</time>
        <h1 class="page-title post-hero-title">{escape(title)}</h1>
      </div>
    </header>
    <article class="page-section post-article">
      {body}
      {f'<div class="post-gallery">{photos}</div>' if photos else ''}
      {post_neighbours(post, posts)}
    </article>
    {contact}
  </main>

  {footer}
'''


def build_posts(template: str) -> list[str]:
    """Writes one page per post (news-<id>.html) and returns their names."""
    posts = load_posts()
    for post in posts:
        (ROOT / post_url(post)).write_text('\n'.join(line.rstrip() for line in post_page(post, posts, template).split('\n')))
    return [post_url(post) for post in posts]


GENERATED = {'{{meal-gallery}}': meal_gallery, '{{news-list}}': news_list, '{{facility-section}}': facility_section}


def build(name: str, template: str) -> str:
    settings, body = read_settings((TOOLS / 'pages' / f'{name}.html').read_text())
    for marker, render in GENERATED.items():
        if marker in body:
            body = body.replace(marker, render())
    contact = block(template, '<section class="contact"', '</section>') if settings.get('contact') == 'yes' else ''
    footer = block(template, '<footer class="site-footer">', '</html>')
    page_head = head(template, settings["title"], settings.get("description") or settings["title"])
    return f'''{page_head}
<body>
  <a class="skip-link" href="#main">本文へスキップ</a>

  {header(template, name)}

  <main id="main" class="page page--{name}">
    {page_hero(settings)}
{body.rstrip()}
    {contact}
  </main>

  {footer}
'''


def update_top_meals() -> None:
    """Keep the flowing top-page photos in sync with every dish in the gallery."""
    data = json.loads((TOOLS / 'data' / 'meals.json').read_text())
    dishes = [dish for key, _ in MEAL_CHAPTERS for dish in data['dishes'] if dish['category'] == key]
    def card(dish: dict) -> str:
        image_name, width, height = dish_image(dish)
        return (f'<li class="meal-card"><a href="meals.html#img_{dish["id"]}">'
                f'<figure><img src="assets/{image_name}" alt="{escape(dish["name"], quote=True)}" '
                f'width="{width}" height="{height}" loading="lazy" decoding="async"></figure>'
                f'<span class="meal-tag">{escape(data["categories"][dish["category"]])}</span>'
                f'<h3>{escape(dish["name"])}</h3></a></li>\n')

    cards = ''.join(card(dish) for dish in dishes)
    markup = (f'<!-- meals-marquee:start -->\n'
              f'          <div class="meals-marquee" aria-label="毎日の食事の写真">\n'
              f'            <div class="meals-marquee-track" data-duration="{len(dishes) * 8}">\n'
              f'              <ul class="meals-set">{cards}</ul>\n'
              f'              <ul class="meals-set" aria-hidden="true" inert>{cards}</ul>\n'
              f'            </div>\n          </div>\n          <!-- meals-marquee:end -->')
    path = ROOT / 'index.html'
    updated, count = re.subn(r'<!-- meals-marquee:start -->.*?<!-- meals-marquee:end -->',
                             lambda _: markup, path.read_text(), count=1, flags=re.S)
    if count != 1:
        raise ValueError('index.html has no meals-marquee markers')
    path.write_text(updated)


def main() -> int:
    path = ROOT / 'index.html'
    path.write_text(re.sub(r'<!-- facility-section:start -->.*?<!-- facility-section:end -->',
                           lambda _: '<!-- facility-section:start -->\n' + facility_section() + '\n    <!-- facility-section:end -->',
                           path.read_text(), flags=re.S))
    update_top_meals()
    template = (ROOT / 'index.html').read_text()
    for name in PAGES:
        try:
            (ROOT / f'{name}.html').write_text(build(name, template))
        except (OSError, ValueError, KeyError) as error:
            print(f'{name}.html: {error}', file=sys.stderr)
            return 1
        print(f'built {name}.html')
    for name in build_posts(template):
        print(f'built {name}')
    return 0


if __name__ == '__main__':
    sys.exit(main())

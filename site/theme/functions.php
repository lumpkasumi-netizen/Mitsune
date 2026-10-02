<?php

/**

 * Mitsune Theme Functions

 */



if ( ! defined( 'ABSPATH' ) ) exit;



// ===========================

// Theme setup

function mitsune_setup() {

    load_theme_textdomain( 'mitsune', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );

    add_theme_support( 'post-thumbnails' );

    add_image_size( 'mitsune-card', 800, 450, true );

    add_image_size( 'mitsune-hero', 1200, 450, true );

    add_image_size( 'mitsune-ogp',  1200, 630, true );

    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ] );

    add_theme_support( 'automatic-feed-links' );

    add_theme_support( 'custom-logo', [

        'height'      => 60,

        'width'       => 200,

        'flex-height' => true,

        'flex-width'  => true,

    ] );

    register_nav_menus( [

        'primary' => 'メインメニュー',

        'footer'  => 'フッターメニュー',

    ] );

    add_theme_support( 'responsive-embeds' );

}

add_action( 'after_setup_theme', 'mitsune_setup' );



// ===========================

// Styles and scripts

// ===========================

function mitsune_scripts() {

    wp_enqueue_style( 'mitsune-fonts',

        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700;800&display=swap',

        [], null );

    wp_enqueue_style( 'mitsune-style', get_stylesheet_uri(), [ 'mitsune-fonts' ], '1.0.13' );

    wp_enqueue_script( 'mitsune-main', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.2', true );

}

add_action( 'wp_enqueue_scripts', 'mitsune_scripts' );



// ===========================

// Widget area

// ===========================

function mitsune_widgets_init() {

    register_sidebar( [

        'name'          => 'サイドバー',

        'id'            => 'sidebar-1',

        'before_widget' => '<div class="widget">',

        'after_widget'  => '</div>',

        'before_title'  => '<h3 class="widget-title">',

        'after_title'   => '</h3>',

    ] );

}

add_action( 'widgets_init', 'mitsune_widgets_init' );



// ===========================

// SEO metadata

// ===========================

function mitsune_seo_meta() {

    global $post;



    // Meta description

    $site_name = get_bloginfo( 'name' );

    $site_desc = get_bloginfo( 'description' );

    $description = '';



    if ( is_singular() && isset( $post ) ) {

        $excerpt = get_the_excerpt();

        if ( $excerpt ) {

            $description = wp_strip_all_tags( $excerpt );

        } else {

            $description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '...' );

        }

    } elseif ( is_home() || is_front_page() ) {

        $description = $site_desc;

    } elseif ( is_category() || is_tag() || is_tax() ) {

        $description = strip_tags( term_description() ) ?: $site_name;

    } else {

        $description = $site_desc;

    }

    $description = esc_attr( trim( $description ) );



    // OGP image

    $og_image = '';

    if ( is_singular() && isset( $post ) && has_post_thumbnail( $post->ID ) ) {

        $img_src  = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), in_array( (int) $post->ID, [ 796, 907, 909, 911, 913, 915, 917, 784, 98, 923, 73, 927, 944, 946, 948 ], true ) ? 'full' : 'mitsune-ogp' );

        $og_image = $img_src ? $img_src[0] : '';

    }

    if ( ! $og_image ) {

        $og_image = get_template_directory_uri() . '/assets/images/ogp-default.png';

    }



    // OG type

    $og_type = is_singular( 'post' ) ? 'article' : 'website';



    /* mitsune-archive-seo:start */

    // Canonical and Open Graph identity for every indexable page type.

    $canonical = '';

    if ( is_singular() && isset( $post ) ) {

        $canonical = get_permalink( $post->ID );

    } elseif ( is_home() || is_front_page() ) {

        $canonical = home_url( '/' );

    } elseif ( is_category() || is_tag() || is_tax() ) {

        $term_url = get_term_link( get_queried_object() );

        if ( ! is_wp_error( $term_url ) ) $canonical = $term_url;

    } elseif ( is_author() ) {

        $canonical = get_author_posts_url( get_queried_object_id() );

    } elseif ( is_post_type_archive() ) {

        $canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );

    }

    if ( $canonical && get_query_var( 'paged' ) > 1 ) {

        $canonical = get_pagenum_link( (int) get_query_var( 'paged' ) );

    }



    if ( is_singular() && isset( $post ) ) {

        $og_title = get_the_title( $post->ID );

    } elseif ( is_category() || is_tag() || is_tax() ) {

        $og_title = single_term_title( '', false ) . '｜' . $site_name;

    } elseif ( is_author() ) {

        $og_title = get_the_author_meta( 'display_name', get_queried_object_id() ) . '｜' . $site_name;

    } elseif ( is_post_type_archive() ) {

        $og_title = post_type_archive_title( '', false ) . '｜' . $site_name;

    } else {

        $og_title = $site_name;

    }

    $og_title = esc_attr( $og_title );

    $og_url   = esc_url( $canonical ?: home_url( '/' ) );

    /* mitsune-archive-seo:end */



    // robots

    $noindex = false;

    if ( is_404() || is_search() || is_author() ) {

        $noindex = true;

    } elseif ( is_singular() && isset( $post ) && get_post_meta( $post->ID, '_mitsune_noindex', true ) ) {

        $noindex = true;

    }



    echo "\n";

    if ( $description ) {

        echo '<meta name="description" content="' . $description . '">' . "\n";

    }

    if ( $canonical && ! is_singular() ) {

        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";

    }

    if ( $noindex ) {

        echo '<meta name="robots" content="noindex,nofollow">' . "\n";

    }



    // OGP

    echo '<meta property="og:title" content="' . $og_title . '">' . "\n";

    echo '<meta property="og:description" content="' . $description . '">' . "\n";

    echo '<meta property="og:url" content="' . $og_url . '">' . "\n";

    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";

    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";

    echo '<meta property="og:locale" content="ja_JP">' . "\n";

    if ( $og_image ) {

        echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";

    }



    // Twitter Card

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

    echo '<meta name="twitter:title" content="' . $og_title . '">' . "\n";

    echo '<meta name="twitter:description" content="' . $description . '">' . "\n";

    if ( $og_image ) {

        echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";

    }



    // JSON-LD

    if ( is_singular( 'post' ) && isset( $post ) ) {

        $categories = get_the_category( $post->ID );

        $cat_names  = $categories ? array_map( function( $c ) { return $c->name; }, $categories ) : [];

        $json = [

            '@context'         => 'https://schema.org',

            '@type'            => 'Article',

            'headline'         => get_the_title( $post->ID ),

            'datePublished'    => get_the_date( 'c', $post->ID ),

            'dateModified'     => get_the_modified_date( 'c', $post->ID ),

            'author'           => [ '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ],

            'publisher'        => [ '@type' => 'Organization', 'name' => $site_name ],

            'articleSection'   => $cat_names,

        ];

        if ( $og_image ) { $json['image'] = $og_image; }

        echo '<script type="application/ld+json">' . wp_json_encode( $json, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

    }

}

add_action( 'wp_head', 'mitsune_seo_meta' );



// ===========================

// Breadcrumbs

function mitsune_breadcrumb() {

    $items = [];

    $items[] = '<a href="' . esc_url( home_url( '/' ) ) . '">&#12507;&#12540;&#12512;</a>';



    if ( is_category() || is_tag() || is_tax() ) {

        $items[] = single_term_title( '', false );

    } elseif ( is_singular( 'post' ) ) {

        $cats = get_the_category();

        if ( $cats ) {

            $items[] = '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';

        }

        $items[] = get_the_title();

    } elseif ( is_page() ) {

        $items[] = get_the_title();

    }



    if ( count( $items ) > 1 ) {

        echo '<nav class="breadcrumb" aria-label="breadcrumb"><ol>' . "\n";

        foreach ( $items as $i => $item ) {

            $current = ( $i === count( $items ) - 1 ) ? ' aria-current="page"' : '';

            echo '<li' . $current . '>' . $item . '</li>' . "\n";

        }

        echo '</ol></nav>' . "\n";

    }

}

// ===========================

// Category badge

// ===========================

function mitsune_the_category_badge() {

    $cats = get_the_category();

    if ( $cats ) {

        echo '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '" class="category-badge">' . esc_html( $cats[0]->name ) . '</a>';

    }

}



// ===========================

// Pagination

// ===========================

function mitsune_pagination() {

    $args = [

        'prev_text' => '&larr; &#21069;&#12398;&#12506;&#12540;&#12472;',

        'next_text' => '&#27425;&#12398;&#12506;&#12540;&#12472; &rarr;',

        'type'      => 'plain',

    ];

    echo paginate_links( $args );

}



// ===========================

// Related posts

function mitsune_related_posts() {

    global $post;

    if ( ! isset( $post ) ) return;

    $cats = get_the_category( $post->ID );

    if ( ! $cats ) return;



    $related = new WP_Query( [

        'category__in'   => [ $cats[0]->term_id ],

        'post__not_in'   => [ $post->ID ],

        'posts_per_page' => 3,

        'orderby'        => 'rand',

    ] );



    if ( ! $related->have_posts() ) return;

    echo '<section class="related-posts"><h3>&#38306;&#36899;&#35352;&#20107;</h3><div class="related-posts-grid">';

    while ( $related->have_posts() ) {

        $related->the_post();

        ?>

        <article class="related-post-card">

            <a href="<?php the_permalink(); ?>">

                <?php if ( has_post_thumbnail() ) : ?>

                    <?php the_post_thumbnail( 'mitsune-card', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>

                <?php endif; ?>

                <div class="related-post-body">

                    <h4><?php the_title(); ?></h4>

                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>

                </div>

            </a>

        </article>

        <?php

    }

    wp_reset_postdata();

    echo '</div></section>';

}



// ===========================

// Disable comments and trackbacks

add_action( 'init', function() {

    foreach ( get_post_types() as $post_type ) {

        if ( post_type_supports( $post_type, 'comments' ) ) {

            remove_post_type_support( $post_type, 'comments' );

            remove_post_type_support( $post_type, 'trackbacks' );

        }

    }

} );

add_filter( 'comments_open', '__return_false', 20, 2 );

add_filter( 'pings_open', '__return_false', 20, 2 );

add_filter( 'comments_array', '__return_empty_array', 10, 2 );

add_action( 'admin_menu', function() {

    remove_menu_page( 'edit-comments.php' );

} );

add_action( 'wp_before_admin_bar_render', function() {

    global $wp_admin_bar;

    $wp_admin_bar->remove_menu( 'comments' );

} );



// Mitsune prompt table copy buttons.

function mitsune_prompt_copy_script() {

    ?>

    <script id="mitsune-copy-analytics-js">
/* mitsune-prompt-copy-analytics */
(function () {
    'use strict';

    function normalize(value) {
        value = String(value || '');
        if (value.normalize) value = value.normalize('NFKC');
        return value.replace(/\s+/g, ' ').trim().toLocaleLowerCase();
    }

    function hashId(prefix, value) {
        var hash = 2166136261;
        for (var i = 0; value.length > i; i += 1) {
            hash ^= value.charCodeAt(i);
            hash = Math.imul(hash, 16777619);
        }
        return prefix + ('00000000' + (hash >>> 0).toString(16)).slice(-8);
    }

    function getCategoryId(button) {
        var card = button.closest('.mitsune-intent-card');
        if (card) {
            var heading = card.querySelector('h3');
            return hashId('c_', normalize(heading ? heading.textContent : 'example'));
        }
        var table = button.closest('.mitsune-prompt-table, .mitsune-codebox');
        var node = table;
        while (node && node.previousElementSibling) {
            node = node.previousElementSibling;
            if (/^H[2-3]$/.test(node.tagName)) {
                return hashId('c_', normalize(node.id || node.textContent || 'uncategorized'));
            }
        }
        return 'c_uncategorized';
    }

    function getPromptId(button, prompt) {
        var canonical = button.getAttribute('data-prompt') || prompt || '';
        return hashId('p_', location.pathname + '|' + normalize(canonical));
    }

    function getCopyMode(button) {
        return button.getAttribute('data-copy-suffix') === 'comma' ? 'comma' : 'plain';
    }

    window.mitsuneTrackPromptCopy = function (button, prompt) {
        if (typeof window.gtag !== 'function') return;
        window.gtag('event', 'mitsune_prompt_copy', {
            prompt_id: getPromptId(button, prompt),
            prompt_category: getCategoryId(button),
            copy_mode: getCopyMode(button)
        });
    };

    function destinationSlug(link) {
        var url;
        try { url = new URL(link.href, location.href); } catch (error) { return ''; }
        if (url.origin !== location.origin) return '';
        var parts = url.pathname.split('/').filter(Boolean);
        return (parts.pop() || 'home').slice(0, 80);
    }

    function positionIn(items, item) {
        return String(Array.prototype.indexOf.call(items, item) + 1);
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.mitsune-intent-related a, .mitsune-seo-block #mitsune-related + ul a, .related-posts a, .mitsune-home-card a');
        if (!link || typeof window.gtag !== 'function') return;
        var slug = destinationSlug(link), group = '', item = null, items = [];
        if (!slug) return;
        if (link.closest('.mitsune-intent-related')) {
            group = 'intent_related';
            item = link.closest('li');
            items = link.closest('.mitsune-intent-related').querySelectorAll('li');
        } else if (link.closest('.mitsune-home-card')) {
            group = 'home_card';
            item = link.closest('.mitsune-home-card');
            items = document.querySelectorAll('.mitsune-home-card');
        } else if (link.closest('.related-posts')) {
            group = 'theme_related';
            item = link.closest('.related-post-card');
            items = link.closest('.related-posts').querySelectorAll('.related-post-card');
        } else {
            group = 'editorial_related';
            item = link.closest('li');
            items = link.closest('.mitsune-seo-block').querySelectorAll('#mitsune-related + ul > li');
        }
        if (!item) return;
        window.gtag('event', 'mitsune_content_click', {
            content_group: group,
            destination_slug: slug,
            item_position: positionIn(items, item)
        });
    }, true);
}());
</script>

    <script id="mitsune-copy-buttons-js">

    /* mitsune-comma-copy */

    function mitsuneAddCommaCopyButtons() {

        document.querySelectorAll('.mitsune-copy[data-prompt]').forEach(function(button) {

            if (button.closest('.mitsune-copy-actions') || !button.getAttribute('data-prompt').trim()) return;

            var actions = document.createElement('span');

            actions.className = 'mitsune-copy-actions';

            var comma = document.createElement('button');

            comma.type = 'button';

            comma.className = 'mitsune-copy mitsune-copy-comma';

            comma.textContent = ',付き';

            comma.setAttribute('data-copy-label', ',付き');

            comma.setAttribute('data-copy-suffix', 'comma');

            comma.setAttribute('data-prompt', button.getAttribute('data-prompt'));

            comma.setAttribute('aria-label', '末尾にコンマと半角スペースを付けてコピー');

            comma.title = '末尾に「, 」（コンマ＋半角スペース）を付けてコピー';

            button.parentNode.insertBefore(actions, button);

            actions.appendChild(button);

            actions.appendChild(comma);

        });

    }

    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', mitsuneAddCommaCopyButtons);

    } else {

        mitsuneAddCommaCopyButtons();

    }



    document.addEventListener('click', function(event) {

        var button = event.target.closest('.mitsune-copy');

        if (!button) return;

        var prompt = button.getAttribute('data-prompt') || '';

        if (button.getAttribute('data-copy-suffix') === 'comma') { prompt = prompt.replace(/[\s,]+$/, '') + ', '; }

        var originalText = button.getAttribute('data-copy-label') || button.textContent || '\u30b3\u30d4\u30fc';

        var showState = function(text, className) {

            button.classList.remove('is-copied', 'is-copy-failed');

            if (className) button.classList.add(className);

            button.textContent = text;

            window.setTimeout(function() {

                button.classList.remove('is-copied', 'is-copy-failed');

                button.textContent = originalText;

            }, 1400);

        };

        var done = function() { showState(String.fromCharCode(28168, 12415), 'is-copied'); if (window.mitsuneTrackPromptCopy) window.mitsuneTrackPromptCopy(button, prompt); };

        var failed = function() { showState('\u5931\u6557', 'is-copy-failed'); };

        var fallbackCopy = function() {

            var textarea = document.createElement('textarea');

            textarea.value = prompt;

            textarea.setAttribute('readonly', 'readonly');

            textarea.style.position = 'fixed';

            textarea.style.top = '0';

            textarea.style.left = '0';

            textarea.style.width = '1px';

            textarea.style.height = '1px';

            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.focus();

            textarea.select();

            textarea.setSelectionRange(0, textarea.value.length);

            var copied = false;

            try { copied = document.execCommand('copy'); } catch (error) { copied = false; }

            document.body.removeChild(textarea);

            if (copied) { done(); } else { failed(); }

        };

        if (navigator.clipboard && navigator.clipboard.writeText) {

            navigator.clipboard.writeText(prompt).then(done).catch(fallbackCopy);

        } else {

            fallbackCopy();

        }

    });

    </script>

    <?php

}

add_action( 'wp_footer', 'mitsune_prompt_copy_script' );

/* mitsune-toc-ad-stack:start */

// Mitsune prompt sidebar TOC.

function mitsune_clothing_prompt_sidebar_toc() {

    if ( ! is_singular( 'post' ) ) {

        return;

    }

    ?>

    <style id="mitsune-prompt-sidebar-toc-css">

    .mitsune-clothing-toc,.mitsune-toc-ad-stack{display:none}

    .entry-content h2[id]{scroll-margin-top:92px}

    @media (min-width:1100px){

        /* Stretch the grid item: native sticky must be bounded by article height. */

        .content-area>.sidebar{align-self:stretch;position:relative}

        .sidebar .mitsune-toc-ad-stack{display:block;position:relative;flex:none;width:100%;box-sizing:border-box;margin:24px 0 0;z-index:3}

        .sidebar .mitsune-toc-ad-stack>.mitsune-clothing-toc{display:block;position:relative;margin:0;padding:16px 16px 14px;border:1px solid #dbeafe;border-radius:8px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.08)}

        .sidebar .mitsune-clothing-toc-title{margin:0 0 10px;font-size:15px;font-weight:900;color:#0f172a;line-height:1.35}

        .sidebar .mitsune-clothing-toc-list{display:grid;gap:3px;max-height:calc(100vh - 164px);overflow:auto;padding-right:2px;scrollbar-width:thin}

        .sidebar .mitsune-clothing-toc-list a{display:block;padding:6px 8px;border-radius:6px;color:#075985;font-size:13px;font-weight:700;line-height:1.35;text-decoration:none}

        .sidebar .mitsune-clothing-toc-list a:hover,.sidebar .mitsune-clothing-toc-list a:focus{background:#e0f2fe;color:#0f172a;outline:none}

        .sidebar .mitsune-clothing-toc-list a.is-active{background:#0ea5e9;color:#fff}

        /* The ad is outside nav. No scaling, clipping or independently sticky ad. */

        .sidebar .mitsune-toc-ad-stack>.mitsune-manual-ad--sidebar{display:block;position:static;box-sizing:border-box;width:100%;min-height:280px;margin:24px 0 0}

    }

    @media (min-width:1100px) and (min-height:720px) and (hover:hover) and (pointer:fine){

        .sidebar .mitsune-toc-ad-stack{position:sticky;top:86px}

        .sidebar .mitsune-toc-ad-stack.is-with-ad .mitsune-clothing-toc-list{max-height:calc(100vh - 630px);max-height:calc(100dvh - 630px)}

    }

    @media (max-width:1099px){.sidebar .mitsune-toc-ad-stack{display:none}}

    </style>

    <script id="mitsune-prompt-sidebar-toc-js">

    document.addEventListener('DOMContentLoaded', function() {

/* mitsune-toc-heading-source:start */

        var sidebar = document.querySelector('.sidebar');

        if (!sidebar || sidebar.querySelector('.mitsune-clothing-toc')) return;

        var contentHeadings = Array.prototype.slice.call(document.querySelectorAll('.entry-content h2')).filter(function(heading) {

            var node = heading.nextElementSibling;

            while (node && node.tagName !== 'H2') {

                if ((node.matches && node.matches('.mitsune-prompt-table')) || (node.querySelector && node.querySelector('.mitsune-prompt-table'))) return true;

                node = node.nextElementSibling;

            }

            return false;

        });

        if (!contentHeadings.length) return;

/* mitsune-toc-heading-source:end */

        var box = document.createElement('nav');

        box.className = 'mitsune-clothing-toc';

        box.setAttribute('aria-label', 'prompt toc');

        box.innerHTML = '<p class="mitsune-clothing-toc-title">&#12503;&#12525;&#12531;&#12503;&#12488;&#30446;&#27425;</p><div class="mitsune-clothing-toc-list"></div>';

        var list = box.querySelector('.mitsune-clothing-toc-list');

/* mitsune-toc-target-fix:start */

        contentHeadings.forEach(function(target, index) {

            if (!target.id) {

                var baseId = 'mitsune-section-' + (index + 1);

                var uniqueId = baseId;

                var suffix = 2;

                while (document.getElementById(uniqueId) && document.getElementById(uniqueId) !== target) {

                    uniqueId = baseId + '-' + suffix++;

                }

                target.id = uniqueId;

            }

            var href = '#' + target.id;

            var link = document.createElement('a');

            link.href = href;

            link.textContent = (target.textContent || '').trim();

            list.appendChild(link);

        });

/* mitsune-toc-target-fix:end */



/* mitsune-toc-ad-stack-layout:start */

        var stack = document.createElement('div');

        stack.className = 'mitsune-toc-ad-stack';

        stack.appendChild(box);

        var sidebarAd = sidebar.querySelector(':scope > .mitsune-manual-ad--sidebar');

        var adHost = sidebarAd && sidebarAd.querySelector('.mitsune-manual-ad-host');

        // This listener is registered at footer priority 20; ad init is priority 25.

        // Move only the still-empty site wrapper, never a submitted Google ad.

        var canAttachAd = adHost

            && adHost.getAttribute('data-mitsune-ad-requested') !== '1'

            && !sidebarAd.querySelector('ins,iframe');

        if (canAttachAd) {

            sidebar.insertBefore(stack, sidebarAd);

            stack.appendChild(sidebarAd);

            stack.classList.add('is-with-ad');

        } else {

            // If already initialized, leave the ad in its non-sticky original place.

            sidebar.appendChild(stack);

        }

/* mitsune-toc-ad-stack-layout:end */



/* mitsune-toc-scroll-sync:start */

        var tocLinks = Array.prototype.slice.call(list.querySelectorAll('a'));

        var sections = tocLinks.map(function(link) {

            var href = link.getAttribute('href') || '';

            return href.length > 1 ? document.getElementById(decodeURIComponent(href.slice(1))) : null;

        });

        tocLinks.forEach(function(link, index) {

            link.addEventListener('click', function(event) {

                var section = sections[index];

                if (!section) return;

                event.preventDefault();

                section.scrollIntoView({ behavior: 'smooth', block: 'start' });

                if (window.history && window.history.replaceState) {

                    window.history.replaceState(null, '', '#' + section.id);

                }

            });

        });

        var scrollTicking = false;

        var activeIndex = -1;

        var keepActiveLinkVisible = function(link) {

            var listRect = list.getBoundingClientRect();

            var linkRect = link.getBoundingClientRect();

            var padding = 8;

            var targetTop = null;

            if (linkRect.top < listRect.top + padding) {

                targetTop = list.scrollTop + linkRect.top - listRect.top - padding;

            } else if (linkRect.bottom > listRect.bottom - padding) {

                targetTop = list.scrollTop + linkRect.bottom - listRect.bottom + padding;

            }

            if (targetTop !== null) list.scrollTop = Math.max(0, targetTop);

        };

        var updateActive = function() {

            scrollTicking = false;

            if (!sections.length) return;

            var currentIndex = 0;

            sections.forEach(function(section, index) {

                if (section && section.getBoundingClientRect().top <= 140) currentIndex = index;

            });

            if (currentIndex === activeIndex) return;

            if (activeIndex >= 0 && tocLinks[activeIndex]) tocLinks[activeIndex].classList.remove('is-active');

            activeIndex = currentIndex;

            var activeLink = tocLinks[activeIndex];

            if (activeLink) {

                activeLink.classList.add('is-active');

                // Do not use Element.scrollIntoView here: it can scroll the page itself

                // back toward the sidebar when the reader has reached the page top.

                keepActiveLinkVisible(activeLink);

            }

        };

        var requestActiveUpdate = function() {

            if (scrollTicking) return;

            scrollTicking = true;

            window.requestAnimationFrame(updateActive);

        };

        window.addEventListener('scroll', requestActiveUpdate, { passive: true });

        window.addEventListener('resize', requestActiveUpdate);

        updateActive();

/* mitsune-toc-scroll-sync:end */

    });

    </script>

    <?php

}

add_action( 'wp_footer', 'mitsune_clothing_prompt_sidebar_toc', 20 );

/* mitsune-toc-ad-stack:end */





/* mitsune-prompt-heading-shared:start */

// Shared visual treatment for prompt-dictionary section headings only.

// Normal article headings (including the examples collection) are unaffected.

function mitsune_prompt_shared_heading_styles() {

    ?>

    <style id="mitsune-prompt-heading-shared-css">

    .entry-content .mitsune-prompt-heading{margin:30px 0 10px;padding:10px 13px;border-left:4px solid #22d3ee;border-radius:6px;background:#f8fafc;font-weight:900;color:#0f172a}

    .entry-content .mitsune-prompt-subheading{margin:18px 0 6px;padding:0 0 5px;border:0;border-bottom:1px solid #dbeafe;background:transparent;border-radius:0;font-size:14px;line-height:1.5;font-weight:800;color:#334155}

    </style>

    <?php

}

add_action( 'wp_head', 'mitsune_prompt_shared_heading_styles', 18 );

/* mitsune-prompt-heading-shared:end */



// Mitsune clothing prompt dense table layout.

function mitsune_clothing_prompt_dense_layout() {

    if ( ! is_singular( 'post' ) ) {

        return;

    }

    ?>

    <style id="mitsune-prompt-dense-css">

    .entry-content .mitsune-prompt-table{margin:10px 0 24px;border:1px solid #b9c9d6;border-radius:4px;overflow:hidden;background:#fff}

    .entry-content .mitsune-prompt-table table{display:block;width:100%}

    .entry-content .mitsune-prompt-table thead{display:none!important}

    .entry-content .mitsune-prompt-table tbody{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:0!important;background:#cbd5e1}

    .entry-content .mitsune-prompt-table tr{display:grid!important;grid-template-columns:minmax(92px,.9fr) minmax(112px,1fr) 48px!important;grid-template-areas:"jp prompt copy"!important;align-items:center!important;gap:5px!important;min-height:36px!important;padding:5px 7px!important;border:0!important;border-right:1px solid #cbd5e1!important;border-bottom:1px solid #cbd5e1!important;border-radius:0!important;background:#fff!important;box-shadow:none!important}

    .entry-content .mitsune-prompt-table tr:nth-child(2n){border-right:0!important}

    .entry-content .mitsune-prompt-table td{line-height:1.35!important}

    .entry-content .mitsune-prompt-table td:nth-child(1){font-size:14px!important;font-weight:700!important;color:#0f172a!important;word-break:keep-all;overflow-wrap:anywhere}

    .entry-content .mitsune-prompt-table td:nth-child(2){min-width:0!important}

    .entry-content .mitsune-prompt-table td:nth-child(3){display:none!important}

    .entry-content .mitsune-prompt-table td:nth-child(4){height:auto!important;align-self:center!important;justify-self:end!important}

    .entry-content .mitsune-prompt-table code{display:inline-block;max-width:100%;padding:2px 5px!important;border-radius:4px!important;color:#075985!important;background:#eef6ff!important;font-size:12px!important;font-weight:700!important;line-height:1.25!important;white-space:normal!important;overflow-wrap:anywhere}

    .entry-content .mitsune-copy{min-width:44px!important;height:28px!important;padding:4px 6px!important;border:0!important;border-radius:5px!important;background:#22d3ee!important;color:#06111f!important;font-size:12px!important;font-weight:800!important;line-height:1!important;white-space:nowrap!important;cursor:pointer}

    .entry-content .mitsune-copy.is-copied{background:#16a34a!important;color:#fff!important}

    @media (max-width:860px){.entry-content .mitsune-prompt-table tbody{grid-template-columns:1fr!important}.entry-content .mitsune-prompt-table tr{border-right:0!important}}

    @media (max-width:520px){.entry-content .mitsune-prompt-table tr{grid-template-columns:minmax(84px,.75fr) minmax(92px,1fr) 44px!important;min-height:38px!important;padding:6px!important}.entry-content .mitsune-prompt-table td:nth-child(1){font-size:13px!important}.entry-content .mitsune-prompt-table code{font-size:11px!important}.entry-content .mitsune-copy{height:28px!important}}

    </style>

    <?php

}

add_action( 'wp_head', 'mitsune_clothing_prompt_dense_layout', 20 );



// Mitsune post view counter for sidebar ranking.

function mitsune_post_view_counter_script() {

    if ( ! is_singular( 'post' ) || is_user_logged_in() ) {

        return;

    }

    $post_id = get_queried_object_id();

    if ( ! $post_id ) {

        return;

    }

    ?>

    <script id="mitsune-post-view-counter-js">

    (function() {

        var postId = <?php echo (int) $post_id; ?>;

        var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

        var nonce = <?php echo wp_json_encode( wp_create_nonce( 'mitsune_count_post_view' ) ); ?>;

        var key = 'mitsune_viewed_' + postId;

        var interval = 6 * 60 * 60 * 1000;

        try {

            var now = Date.now();

            var last = parseInt(window.localStorage.getItem(key) || '0', 10);

            if (last && now - last < interval) return;

            window.localStorage.setItem(key, String(now));

            var data = new FormData();

            data.append('action', 'mitsune_count_post_view');

            data.append('post_id', String(postId));

            data.append('nonce', nonce);

            if (navigator.sendBeacon) {

                navigator.sendBeacon(ajaxUrl, data);

                return;

            }

            fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin', keepalive: true });

        } catch (error) {}

    })();

    </script>

    <?php

}

add_action( 'wp_footer', 'mitsune_post_view_counter_script', 40 );



function mitsune_count_post_view_ajax() {

    check_ajax_referer( 'mitsune_count_post_view', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;

    if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {

        wp_send_json_error( null, 400 );

    }

    $rate_key = 'mitsune_view_rate_' . $post_id . '_' . mitsune_review_client_key();

    if ( get_transient( $rate_key ) ) wp_send_json_success( array( 'duplicate' => true ) );

    set_transient( $rate_key, 1, 6 * HOUR_IN_SECONDS );

    $count = (int) get_post_meta( $post_id, '_mitsune_view_count', true ) + 1;

    update_post_meta( $post_id, '_mitsune_view_count', $count );

    delete_transient( 'mitsune_sidebar_ranking_ids' );

    wp_send_json_success( array( 'count' => $count ) );

}

add_action( 'wp_ajax_nopriv_mitsune_count_post_view', 'mitsune_count_post_view_ajax' );

add_action( 'wp_ajax_mitsune_count_post_view', 'mitsune_count_post_view_ajax' );



// Mitsune dynamic ranking sidebar refresh. Keeps rankings current even when page cache is active.

function mitsune_get_sidebar_ranking_posts() {

    $cached_ids = get_transient( 'mitsune_sidebar_ranking_ids' );

    if ( is_array( $cached_ids ) ) {

        $cached_posts = array_values( array_filter( array_map( 'get_post', $cached_ids ) ) );

        if ( $cached_posts ) return $cached_posts;

    }

    $posts = [];

    $seen  = [];



    $ranked = new WP_Query( [

        'post_type'           => 'post',

        'posts_per_page'      => 5,

        'post_status'         => 'publish',

        'ignore_sticky_posts' => true,

        'meta_key'            => '_mitsune_view_count',

        'meta_query'          => [

            [

                'key'     => '_mitsune_view_count',

                'value'   => 0,

                'compare' => '>',

                'type'    => 'NUMERIC',

            ],

        ],

        'orderby'             => [

            'meta_value_num' => 'DESC',

            'date'           => 'DESC',

        ],

    ] );



    foreach ( $ranked->posts as $ranked_post ) {

        $posts[] = $ranked_post;

        $seen[]  = $ranked_post->ID;

    }

    wp_reset_postdata();



    if ( count( $posts ) < 5 ) {

        $latest = new WP_Query( [

            'post_type'           => 'post',

            'posts_per_page'      => 5 - count( $posts ),

            'post_status'         => 'publish',

            'ignore_sticky_posts' => true,

            'post__not_in'        => $seen,

            'orderby'             => 'date',

            'order'               => 'DESC',

        ] );

        foreach ( $latest->posts as $latest_post ) {

            $posts[] = $latest_post;

        }

        wp_reset_postdata();

    }



    set_transient( 'mitsune_sidebar_ranking_ids', wp_list_pluck( $posts, 'ID' ), 5 * MINUTE_IN_SECONDS );

    return $posts;

}



function mitsune_sidebar_ranking_title( $title ) {

    $title = preg_replace( '/\s*\x{FF5C}.*/u', '', $title );

    $title = preg_replace( '/^Stable Diffusion\s*/u', '', $title );

    $title = preg_replace( '/^(?:[\x{FF08}(]\s*SD\s*[\x{FF09})]|SD)\s*/u', '', $title );

    return trim( $title );

}



function mitsune_render_sidebar_ranking_items() {

    $posts = mitsune_get_sidebar_ranking_posts();

    if ( ! $posts ) {

        return '';

    }



    ob_start();

    $rank = 1;

    foreach ( $posts as $ranking_post ) :

        $title = mitsune_sidebar_ranking_title( get_the_title( $ranking_post ) );

        $url   = get_permalink( $ranking_post );

        ?>

        <li class="mitsune-ranking-item">

            <a class="mitsune-ranking-thumb" href="<?php echo esc_url( $url ); ?>">

                <span class="mitsune-ranking-rank"><?php echo esc_html( $rank ); ?>&#20301;</span>

                <?php if ( has_post_thumbnail( $ranking_post ) ) : ?>

                    <?php echo get_the_post_thumbnail( $ranking_post, 'medium', [ 'alt' => esc_attr( get_the_title( $ranking_post ) ) ] ); ?>

                <?php endif; ?>

            </a>

            <a class="mitsune-ranking-link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>

        </li>

        <?php

        $rank++;

    endforeach;



    return ob_get_clean();

}



function mitsune_get_sidebar_ranking_ajax() {

    echo mitsune_render_sidebar_ranking_items();

    wp_die();

}

add_action( 'wp_ajax_nopriv_mitsune_get_sidebar_ranking', 'mitsune_get_sidebar_ranking_ajax' );

add_action( 'wp_ajax_mitsune_get_sidebar_ranking', 'mitsune_get_sidebar_ranking_ajax' );



function mitsune_sidebar_ranking_refresh_script() {

    if ( ! is_singular( 'post' ) ) {

        return;

    }

    ?>

    <script id="mitsune-sidebar-ranking-refresh-js">

    document.addEventListener('DOMContentLoaded', function() {

        var list = document.querySelector('.mitsune-ranking-list');

        if (!list) return;

        var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php?action=mitsune_get_sidebar_ranking' ) ); ?>;

        window.setTimeout(function() {

            fetch(ajaxUrl, { credentials: 'same-origin' })

                .then(function(response) { return response.text(); })

                .then(function(markup) {

                    if (markup && markup.trim()) list.innerHTML = markup;

                })

                .catch(function() {});

        }, 700);

    });

    </script>

    <?php

}

add_action( 'wp_footer', 'mitsune_sidebar_ranking_refresh_script', 45 );



// Keep the public site free of the WordPress admin toolbar, even for logged-in users.

add_filter( 'show_admin_bar', '__return_false' );



/* mitsune-site-review-fixes:start */

// Security headers that are safe with the existing analytics, font and ad origins.

function mitsune_review_security_headers() {

    if ( headers_sent() ) return;

    header( 'X-Content-Type-Options: nosniff', true );

    header( 'X-Frame-Options: SAMEORIGIN', true );

    header( "Content-Security-Policy: frame-ancestors 'self'", true );

    header( 'Referrer-Policy: strict-origin-when-cross-origin', true );

    header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()', true );

    header( 'X-XSS-Protection: 0', true );

    if ( is_ssl() ) header( 'Strict-Transport-Security: max-age=31536000', true );

}

add_action( 'send_headers', 'mitsune_review_security_headers' );



// Do not publish the thin user archive in the sitemap; its public page is noindex.

function mitsune_review_sitemap_provider( $provider, $name ) {

    return 'users' === $name ? false : $provider;

}

add_filter( 'wp_sitemaps_add_provider', 'mitsune_review_sitemap_provider', 10, 2 );



function mitsune_review_nav_link_attributes( $atts, $item, $args ) {

    $classes = isset( $item->classes ) ? (array) $item->classes : array();

    if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {

        $atts['aria-current'] = 'page';

    }

    return $atts;

}

add_filter( 'nav_menu_link_attributes', 'mitsune_review_nav_link_attributes', 10, 3 );



function mitsune_review_archive_schema() {

    if ( ! ( is_category() || is_tag() || is_tax() ) ) return;

    $term = get_queried_object();

    $url = get_term_link( $term );

    if ( is_wp_error( $url ) ) return;

    if ( get_query_var( 'paged' ) > 1 ) $url = get_pagenum_link( (int) get_query_var( 'paged' ) );

    $schema = array(

        '@context' => 'https://schema.org',

        '@type' => 'CollectionPage',

        'name' => single_term_title( '', false ),

        'url' => $url,

        'description' => wp_strip_all_tags( term_description( $term ) ?: get_bloginfo( 'description' ) ),

    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

}

add_action( 'wp_head', 'mitsune_review_archive_schema', 16 );



// Improve table semantics at render time without rewriting every post or its modified date.

function mitsune_review_content_accessibility( $content ) {

    if ( false !== strpos( $content, 'mitsune-prompt-table' ) && class_exists( 'WP_HTML_Tag_Processor' ) ) {

        $processor = new WP_HTML_Tag_Processor( $content );

        while ( $processor->next_tag( 'TH' ) ) {

            if ( null === $processor->get_attribute( 'scope' ) ) $processor->set_attribute( 'scope', 'col' );

        }

        $content = $processor->get_updated_html();

    }

    if ( false !== strpos( $content, 'id="mitsune-intent-search-js"' ) ) {

        $replacement = <<<'MITSUNE_SEARCH_MARKUP'

<script id="mitsune-intent-search-js">(function(){

'use strict';

function init(){

  var box=document.querySelector('.entry-content .mitsune-search');

  if(!box || box.dataset.ready) return;

  box.dataset.ready='1';

  var input=box.querySelector('input'), clear=box.querySelector('button'), status=box.querySelector('[role="status"]');

  var tables=Array.from(document.querySelectorAll('.entry-content .mitsune-prompt-table'));

  var rows=tables.flatMap(function(t){return Array.from(t.querySelectorAll('tbody tr')).filter(function(r){return !!r.querySelector('code');});});

  function normalize(value){return value.normalize('NFKC').toLocaleLowerCase().trim();}

  var synonyms={"パーカー":["パーカー","フーディー","hoodie"],"フーディー":["フーディー","パーカー","hoodie"],"hoodie":["hoodie","パーカー","フーディー"]};

  function matchesWord(text,word){

    return (synonyms[word]||[word]).some(function(candidate){return text.includes(candidate);});

  }

  function resultBucket(count){

    if(count===0) return '0';

    if(5>=count) return '1-5';

    if(20>=count) return '6-20';

    if(50>=count) return '21-50';

    return '51+';

  }

  function lengthBucket(length){

    if(2>=length) return '1-2';

    if(4>=length) return '3-4';

    if(8>=length) return '5-8';

    if(16>=length) return '9-16';

    return '17+';

  }

  function tokenBucket(count){return count===1?'1':3>=count?'2-3':'4+';}

  function scriptBucket(query){

    var japanese=/[\u3040-\u30ff\u3400-\u9fff]/.test(query), latin=/[a-z]/.test(query);

    return japanese&&latin?'mixed':japanese?'japanese':latin?'latin':'other';

  }

  var searchTimer=0, lastTrackedQuery='';

  function scheduleSearchEvent(query,words,count){

    window.clearTimeout(searchTimer);

    if(!words.length){lastTrackedQuery='';return;}

    searchTimer=window.setTimeout(function(){

      if(query===lastTrackedQuery || typeof window.gtag!=='function') return;

      lastTrackedQuery=query;

      window.gtag('event','mitsune_prompt_search',{

        search_result_state:count===0?'zero':'matched',

        search_result_bucket:resultBucket(count),

        search_query_length_bucket:lengthBucket(query.length),

        search_query_tokens_bucket:tokenBucket(words.length),

        search_query_script:scriptBucket(query)

      });

    },700);

  }

  var index=rows.map(function(row){return {row:row,text:normalize(Array.from(row.querySelectorAll('td')).slice(0,2).map(function(c){return c.textContent;}).join(' '))};});

  var sections=Array.from(document.querySelectorAll('.entry-content h2.mitsune-prompt-heading')).map(function(heading){

    var nodes=[], node=heading.nextElementSibling;

    while(node && node.tagName!=='H2'){nodes.push(node);node=node.nextElementSibling;}

    return {heading:heading,nodes:nodes,tables:nodes.filter(function(n){return n.classList.contains('mitsune-prompt-table');})};

  }).filter(function(s){return s.tables.length;});

  var groups=new Map();

  index.forEach(function(item){

    item.key=normalize(item.row.querySelector('code').textContent);

    if(!groups.has(item.key)) groups.set(item.key,[]);

    groups.get(item.key).push(item);

    var table=item.row.closest('.mitsune-prompt-table'), node=table.previousElementSibling, labels=[];

    while(node){if(node.tagName==='H3' && !labels.length)labels.unshift(node.textContent.trim());if(node.tagName==='H2'){labels.unshift(node.textContent.trim());break;}node=node.previousElementSibling;}

    item.category=labels.join(' / ');

  });

  groups.forEach(function(items){

    if(items.length<2) return;

    var labels=Array.from(new Set(items.map(function(item){return item.category;}).filter(Boolean)));

    items.forEach(function(item){var badge=document.createElement('details');badge.className='mitsune-search-categories';badge.hidden=true;var summary=document.createElement('summary');summary.textContent='掲載分類を確認（'+labels.length+'）';var text=document.createElement('span');text.textContent=labels.join('、');badge.append(summary,text);item.row.querySelector('td').appendChild(badge);item.badge=badge;});

  });

  function update(){

    var query=normalize(input.value), words=query.split(/\s+/).filter(Boolean), count=0;

    document.body.classList.toggle('mitsune-search-active',words.length>0);

    var nav=document.querySelector('.entry-content .mitsune-prompt-nav');

    if(nav) nav.hidden=words.length>0;

    var seen=new Set();

    index.forEach(function(item){

      var match=words.every(function(word){return matchesWord(item.text,word);});

      var show=match && (!words.length || !seen.has(item.key));

      if(show){count++;seen.add(item.key);} item.row.hidden=!show;

      if(item.badge)item.badge.hidden=!words.length || !show;

    });

    tables.forEach(function(table){

      var groupRows=Array.from(table.querySelectorAll('tbody tr'));

      groupRows.forEach(function(row,i){

        if(!row.classList.contains('mitsune-category-row')) return;

        var visible=false;

        for(var j=i+1;groupRows.length>j && !groupRows[j].classList.contains('mitsune-category-row');j++){

          if(groupRows[j].querySelector('code') && !groupRows[j].hidden) visible=true;

        }

        row.hidden=words.length>0 && !visible;

      });

      table.hidden=!Array.from(table.querySelectorAll('tbody tr')).some(function(r){return r.querySelector('code') && !r.hidden;});

    });

    sections.forEach(function(section){

      var hidden=words.length>0 && section.tables.every(function(t){return t.hidden;});

      section.heading.hidden=hidden;

      section.nodes.forEach(function(n){

        if(n.classList.contains('mitsune-prompt-table')) return;

        if(n.tagName==='H3'){

          var next=n.nextElementSibling, any=false;

          while(next && next.tagName!=='H2' && next.tagName!=='H3'){

            if(next.classList.contains('mitsune-prompt-table') && !next.hidden) any=true;

            next=next.nextElementSibling;

          }

          n.hidden=words.length>0 && !any;

        } else if(n.classList.contains('mitsune-section-lead')) {n.hidden=hidden;}

      });

    });

    status.textContent=words.length ? count+' / '+groups.size+'種類を表示'+(count?'':'。別の語句で試すか、クリアしてください。') : '全'+rows.length+'件を表示。日本語・英語で検索できます。';

    clear.disabled=!input.value;

    scheduleSearchEvent(query,words,count);

  }

  input.addEventListener('input',update);

  input.addEventListener('keydown',function(e){if(e.key==='Escape'){input.value='';update();}});

  clear.addEventListener('click',function(){input.value='';update();input.focus();});

  document.addEventListener('click',function(event){

    var link=event.target.closest('a[href^="#"]');

    if(!link || !input.value) return;

    var id; try{id=decodeURIComponent(link.getAttribute('href').slice(1));}catch(error){return;}

    if(sections.some(function(s){return s.heading.id===id;})){input.value='';update();}

  },true);

  box.hidden=false; update();

}

if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',init);}else{init();}

}());</script>

MITSUNE_SEARCH_MARKUP;

        $updated = preg_replace_callback(

            '~<script\s+id=["\']mitsune-intent-search-js["\'][^>]*>.*?</script>~s',

            function () use ( $replacement ) { return $replacement; },

            $content,

            1

        );

        if ( null !== $updated ) $content = $updated;

    }

    return $content;

}

add_filter( 'the_content', 'mitsune_review_content_accessibility', 19 );



function mitsune_review_client_key() {

    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

    return substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 32 );

}



function mitsune_review_contact_is_rate_limited() {

    $key = 'mitsune_contact_rate_' . mitsune_review_client_key();

    $attempts = (int) get_transient( $key );

    if ( $attempts >= 5 ) return true;

    set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );

    return false;

}



function mitsune_review_frontend_runtime() {

    ?>

    <script id="mitsune-review-runtime-js">

    (function () {

        'use strict';

        function init() {

            document.querySelectorAll('.mitsune-prompt-table tr').forEach(function (row) {

                var cell = row.querySelector('td:nth-child(3)');

                if (cell && !(cell.textContent || '').trim()) cell.classList.add('mitsune-empty-cell');

            });

            if (document.documentElement.scrollHeight < 5000 || document.querySelector('.mitsune-back-to-top')) return;

            var button = document.createElement('button');

            button.type = 'button';

            button.className = 'mitsune-back-to-top';

            button.textContent = '上へ';

            button.setAttribute('aria-label', 'ページの先頭へ戻る');

            document.body.appendChild(button);

            var update = function () { button.classList.toggle('is-visible', window.scrollY > 1200); };

            button.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

            window.addEventListener('scroll', update, { passive: true });

            update();

        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);

        else init();

    }());

    </script>

    <?php

}

add_action( 'wp_footer', 'mitsune_review_frontend_runtime', 60 );

/* mitsune-site-review-fixes:end */



/* mitsune-contact-form:start */

function mitsune_contact_recipient() {

    return 'mitsune.ai.site@gmail.com';

}



function mitsune_contact_form_message( $code ) {

    $messages = array(

        'sent'    => 'お問い合わせを送信しました。ご連絡ありがとうございます。',

        'invalid' => '入力内容を確認して、もう一度送信してください。',

        'error'   => '送信できませんでした。時間をおいてもう一度お試しください。',

        'rate'    => '送信回数が多すぎます。15分ほど待ってからお試しください。',

    );

    return isset( $messages[ $code ] ) ? $messages[ $code ] : '';

}



function mitsune_render_contact_form() {

    $notice = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : '';

    ob_start(); ?>

    <div class="mitsune-contact-form-wrap">

        <?php if ( mitsune_contact_form_message( $notice ) ) : ?>

            <p class="mitsune-contact-notice <?php echo 'sent' === $notice ? 'is-success' : 'is-error'; ?>" role="status"><?php echo esc_html( mitsune_contact_form_message( $notice ) ); ?></p>

        <?php endif; ?>

        <form class="mitsune-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

            <input type="hidden" name="action" value="mitsune_contact_submit">

            <?php wp_nonce_field( 'mitsune_contact_submit', 'mitsune_contact_nonce' ); ?>

            <p class="mitsune-contact-honeypot" aria-hidden="true"><label>会社名 <input type="text" name="company" tabindex="-1" autocomplete="off"></label></p>

            <p><label for="mitsune-contact-name">お名前（任意）</label><input id="mitsune-contact-name" type="text" name="name" autocomplete="name" maxlength="100"></p>

            <p><label for="mitsune-contact-email">メールアドレス <span aria-hidden="true">*</span></label><input id="mitsune-contact-email" type="email" name="email" autocomplete="email" maxlength="254" required></p>

            <p><label for="mitsune-contact-subject">件名 <span aria-hidden="true">*</span></label><input id="mitsune-contact-subject" type="text" name="subject" maxlength="150" required></p>

            <p><label for="mitsune-contact-message">お問い合わせ内容 <span aria-hidden="true">*</span></label><textarea id="mitsune-contact-message" name="message" rows="7" maxlength="5000" required></textarea></p>

            <p class="mitsune-contact-consent">送信することで、<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">プライバシーポリシー・免責事項</a>に同意したものとします。</p>

            <p><button type="submit">送信する</button></p>

        </form>

    </div>

    <?php return ob_get_clean();

}

add_shortcode( 'mitsune_contact_form', 'mitsune_render_contact_form' );



function mitsune_handle_contact_submit() {

    $redirect = wp_get_referer() ? wp_get_referer() : home_url( '/contact/' );

    if ( ! isset( $_POST['mitsune_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mitsune_contact_nonce'] ) ), 'mitsune_contact_submit' ) ) {

        wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) ); exit;

    }

    if ( ! empty( $_POST['company'] ) ) {

        wp_safe_redirect( add_query_arg( 'contact', 'sent', $redirect ) ); exit;

    }

    if ( mitsune_review_contact_is_rate_limited() ) {

        wp_safe_redirect( add_query_arg( 'contact', 'rate', $redirect ) ); exit;

    }

    $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';

    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    if ( ! is_email( $email ) || '' === $subject || '' === $message ) {

        wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) ); exit;

    }

    $body = "お名前: " . ( $name ? $name : '未入力' ) . "\nメールアドレス: " . $email . "\n\nお問い合わせ内容:\n" . $message;

    $headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email );

    $sent = wp_mail( mitsune_contact_recipient(), '[Mitsune お問い合わせ] ' . $subject, $body, $headers );

    wp_safe_redirect( add_query_arg( 'contact', $sent ? 'sent' : 'error', $redirect ) ); exit;

}

add_action( 'admin_post_nopriv_mitsune_contact_submit', 'mitsune_handle_contact_submit' );

add_action( 'admin_post_mitsune_contact_submit', 'mitsune_handle_contact_submit' );



function mitsune_contact_form_styles() { ?>

<style id="mitsune-contact-form-css">

.mitsune-legal-page{max-width:860px;margin:0 auto}.mitsune-legal-page h2{margin:2.2em 0 .7em;padding-left:0!important;border-left:0!important;font-size:1.35em}.mitsune-contact-form-wrap{max-width:720px;margin:26px 0;padding:22px;border:1px solid #c8e5df;border-radius:16px;background:#f6fffc}.mitsune-contact-form p{margin:0 0 16px}.mitsune-contact-form label{display:block;margin-bottom:6px;font-weight:700}.mitsune-contact-form input:not([type=hidden]),.mitsune-contact-form textarea{display:block;width:100%;padding:10px 12px;border:1px solid #aebfba;border-radius:8px;background:#fff;color:#17261f;font:inherit}.mitsune-contact-form textarea{resize:vertical}.mitsune-contact-form button{border:0;border-radius:8px;background:#0f766e;color:#fff;padding:11px 20px;font:inherit;font-weight:700;cursor:pointer}.mitsune-contact-consent{font-size:.9em;color:#53635d}.mitsune-contact-honeypot{position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important}.mitsune-contact-notice{padding:12px 14px;border-radius:8px;font-weight:700}.mitsune-contact-notice.is-success{background:#d9f3ed;color:#0f5f57}.mitsune-contact-notice.is-error{background:#fff0ed;color:#a12e21}.footer-legal{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin:0 0 16px;font-size:.9em}.footer-legal a{text-decoration:underline;text-underline-offset:3px}@media(max-width:600px){.mitsune-contact-form-wrap{padding:16px}.footer-legal{gap:12px}}

</style>

<?php }

add_action( 'wp_head', 'mitsune_contact_form_styles', 20 );

/* mitsune-contact-form:end */

/* mitsune-adsense-verification */

add_action('wp_head', function () {

    echo '<meta name="google-adsense-account" content="ca-pub-4543651602515808">' . "\n";

}, 1);



/* mitsune-gtm-analytics-migration:start */

// GTM owns GA4 delivery. Keep Site Kit connected for Search Console and dashboard reports.

add_filter( 'googlesitekit_analytics-4_tag_blocked', '__return_true' );

/* mitsune-gtm-analytics-migration:end */



/* mitsune-ads-txt:start */

// Keep this root endpoint when migrating to another theme, or deploy a physical ads.txt.

add_action('init', function () {

    $path = wp_parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '', PHP_URL_PATH);

    if ('/ads.txt' !== $path) {

        return;

    }

    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

    if ('GET' !== $method && 'HEAD' !== $method) {

        return;

    }

    status_header(200);

    nocache_headers();

    header('Content-Type: text/plain; charset=utf-8');

    if ('HEAD' !== $method) {

        echo "google.com, pub-4543651602515808, DIRECT, f08c47fec0942fa0\n";

    }

    exit;

}, 0);

/* mitsune-ads-txt:end */



/* mitsune-adsense-auto-ads:start */

// Direct Auto ads loader. Site Kit may report data, but must not inject a second tag.

add_filter( 'googlesitekit_adsense_tag_blocked', '__return_true' );



function mitsune_adsense_auto_ads_should_load() {

    if ( is_admin() ) return false;

    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) return false;

    if ( is_page( array( 'privacy-policy', 'contact' ) ) ) return false;

    if ( is_404() || is_search() || is_feed() || is_preview() || is_trackback() || is_embed() ) return false;

    return is_front_page() || is_home() || is_singular( 'post' ) || is_category();

}



function mitsune_adsense_auto_ads_loader() {

    if ( ! mitsune_adsense_auto_ads_should_load() ) return;

    ?>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4543651602515808" crossorigin="anonymous"></script>

    <?php

}

add_action( 'wp_head', 'mitsune_adsense_auto_ads_loader', 2 );

/* mitsune-adsense-auto-ads:end */









/* mitsune-manual-ads:start */

// Non-sticky manual placements; reuse the existing loader and its exclusions.

function mitsune_manual_ads_should_load() {

    return function_exists( 'mitsune_adsense_auto_ads_should_load' )

        && mitsune_adsense_auto_ads_should_load();

}



function mitsune_render_manual_ad( $placement ) {

    if ( ! mitsune_manual_ads_should_load() ) return;

    if ( ! in_array( $placement, array( 'sidebar', 'article-end' ), true ) ) return;

    if ( $placement === 'article-end' && ! is_singular( 'post' ) ) return;

    static $rendered = array();

    if ( isset( $rendered[ $placement ] ) ) return;

    $rendered[ $placement ] = true;

    $slot = $placement === 'sidebar' ? '4747631422' : '3434549750';

    ?>

    <div class="mitsune-manual-ad mitsune-manual-ad--<?php echo esc_attr( $placement ); ?>" aria-label="広告">

        <span class="mitsune-manual-ad-label">広告</span>

        <div class="mitsune-manual-ad-host" data-mitsune-ad-placement="<?php echo esc_attr( $placement ); ?>" data-ad-client="ca-pub-4543651602515808" data-ad-slot="<?php echo esc_attr( $slot ); ?>"></div>

    </div>

    <?php

}



function mitsune_manual_ads_styles() {

    if ( ! mitsune_manual_ads_should_load() ) return;

    ?>

    <style id="mitsune-manual-ads-css">.mitsune-manual-ad{position:static;box-sizing:border-box;clear:both;text-align:center;margin:40px 0;isolation:isolate}

.mitsune-manual-ad-label{display:block;margin:0 0 12px;color:#64748b;font-size:12px;line-height:18px;font-weight:400;letter-spacing:.08em}

.mitsune-manual-ad-host{width:100%;min-width:0}

.mitsune-manual-ad--article-end{min-height:310px}

.mitsune-manual-ad--article-end .mitsune-manual-ad-host{min-height:280px}

.sidebar>.mitsune-manual-ad--sidebar{display:none;flex:0 0 auto}

@media(min-width:1100px){

  .sidebar>.mitsune-manual-ad--sidebar{display:block;position:static;width:100%;min-height:280px;margin:28px 0}

  .mitsune-manual-ad--sidebar .mitsune-manual-ad-host{width:250px;min-height:250px;margin:0 auto}

}</style>

    <?php

}

add_action( 'wp_head', 'mitsune_manual_ads_styles', 3 );



function mitsune_manual_ads_script() {

    if ( ! mitsune_manual_ads_should_load() ) return;

    ?>

    <script id="mitsune-manual-ads-js">(function () {

  'use strict';

  function init() {

    var hosts = document.querySelectorAll('.mitsune-manual-ad-host');

    function requestEligibleAds() {

      hosts.forEach(function (host) {

        if (host.getAttribute('data-mitsune-ad-requested') === '1') return;

        var sidebar = host.getAttribute('data-mitsune-ad-placement') === 'sidebar';

        if (sidebar && !window.matchMedia('(min-width: 1100px)').matches) return;

        // No hidden, zero-width, or undersized ad is ever put in the queue.

        if (!host.getClientRects().length || host.getBoundingClientRect().width < 250) return;

        // Instantiate once in its final location. Never move, clone or refresh ads.

        var ad = document.createElement('ins');

        ad.className = 'adsbygoogle';

        ad.style.display = 'block';

        ad.setAttribute('data-ad-client', host.getAttribute('data-ad-client'));

        ad.setAttribute('data-ad-slot', host.getAttribute('data-ad-slot'));

        if (sidebar) {

          ad.style.width = '250px';

          ad.style.height = '250px';

        } else {

          ad.setAttribute('data-ad-format', 'auto');

          ad.setAttribute('data-full-width-responsive', 'true');

        }

        host.setAttribute('data-mitsune-ad-requested', '1');

        host.appendChild(ad);

        try { (window.adsbygoogle = window.adsbygoogle || []).push({}); }

        catch (error) { /* Do not retry or refresh a possibly submitted ad. */ }

      });

    }

    requestEligibleAds();

    window.addEventListener('resize', requestEligibleAds, { passive: true });

  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);

  else init();

}());</script>

    <?php

}

add_action( 'wp_footer', 'mitsune_manual_ads_script', 25 );

/* mitsune-manual-ads:end */

/* mitsune-conoha-wide:start */

function mitsune_conoha_wide_target() {

    if ( ! is_singular( 'post' ) ) return false;

    $post = get_post( get_queried_object_id() );

    if ( ! $post || $post->post_status !== 'publish' ) return false;

    // Public Stable Diffusion articles use this slug convention.

    // Keep the explicitly approved ConoHa introduction as a separate exception.

    return $post->post_name === 'conoha-ai-canvas-beginner-guide'

        || ( strpos( $post->post_name, 'stable-diffusion-' ) === 0

            && stripos( $post->post_name, 'r18' ) === false );

}

add_action( 'wp_head', function() {

    if ( ! mitsune_conoha_wide_target() ) return;

    ?><style id="mitsune-conoha-wide-css">body .mitsune-conoha-wide,body .mitsune-conoha-guide .cg-banner{max-width:100%;margin:24px auto;text-align:center;position:relative;line-height:1.5}body .mitsune-conoha-wide a,body .mitsune-conoha-guide .cg-banner a{display:block;width:728px;max-width:100%;margin:0 auto;line-height:0}body .mitsune-conoha-wide a img,body .mitsune-conoha-guide .cg-banner a img{display:block;width:728px;max-width:100%;height:auto;margin:0 auto}body .mitsune-conoha-wide>img,body .mitsune-conoha-guide .cg-banner>img{position:absolute;width:1px;height:1px}body .mitsune-conoha-wide .mitsune-conoha-wide-label,body .cg-banner .mitsune-conoha-wide-label{font-size:12px;color:#64748b;margin:0 0 8px;text-align:center}</style><?php

}, 30 );

add_filter( 'the_content', function( $content ) {

    if ( is_admin() || is_feed() || ! in_the_loop() || ! is_main_query() || ! mitsune_conoha_wide_target() ) return $content;

    if ( strpos( $content, 'mitsune-conoha-wide--top' ) !== false ) return $content;

    $banner = <<<'MITSUNE_CONOHA_WIDE'

<section class="mitsune-conoha-wide mitsune-conoha-wide--top" aria-label="広告：ConoHa AI Canvas"><p class="mitsune-conoha-wide-label">広告</p><a href="https://px.a8.net/svt/ejp?a8mat=4BC5IX+F3KS36+50+7RXDHT" rel="nofollow">

<img border="0" width="728" height="90" alt="" src="https://www21.a8.net/svt/bgt?aid=260907513913&wid=001&eno=01&mid=s00000000018047017000&mc=1"></a>

<img border="0" width="1" height="1" src="https://www14.a8.net/0.gif?a8mat=4BC5IX+F3KS36+50+7RXDHT" alt=""></section>

MITSUNE_CONOHA_WIDE;

    $pattern = '~(<p\b[^>]*class=["\x27][^"\x27]*mitsune-prompt-intro[^"\x27]*["\x27][^>]*>.*?</p>)~is';

    if ( get_queried_object_id() === 800 ) $pattern = '~(<p class="cg-note">情報確認日：.*?</p>)~s';

    if ( preg_match( $pattern, $content ) ) {

        return preg_replace_callback( $pattern, function( $m ) use ( $banner ) { return $m[0] . $banner; }, $content, 1 );

    }

    return $banner . $content;

}, 21 );

/* mitsune-conoha-wide:end */



/* mitsune-conoha-interval:start */

add_filter( 'the_content', function( $content ) {

    if ( is_admin() || is_feed() || ! in_the_loop() || ! is_main_query() || ! mitsune_conoha_wide_target() ) return $content;

    if ( strpos( $content, 'mitsune-conoha-wide--interval' ) !== false ) return $content;

    preg_match_all( '~<h2\b[^>]*>.*?</h2>~is', $content, $matches, PREG_OFFSET_CAPTURE );

    $headings = $matches[0];

    $sections = array();

    foreach ( $headings as $i => $heading ) {

        $start = $heading[1] + strlen( $heading[0] );

        $end = isset( $headings[$i + 1] ) ? $headings[$i + 1][1] : strlen( $content );

        $body = substr( $content, $start, $end - $start );

        if ( preg_match( '~<(?:table|div)\b[^>]*class=["\x27][^"\x27]*\bmitsune-prompt-table\b~i', $body ) ) $sections[] = $heading[1];

    }

    $banner = <<<'MITSUNE_INTERVAL'

<section class="mitsune-conoha-wide mitsune-conoha-wide--interval" aria-label="広告：ConoHa AI Canvas"><p class="mitsune-conoha-wide-label">広告</p><a href="https://px.a8.net/svt/ejp?a8mat=4BC5IX+F3KS36+50+7RXDHT" rel="nofollow">

<img border="0" width="728" height="90" alt="" src="https://www21.a8.net/svt/bgt?aid=260907513913&wid=001&eno=01&mid=s00000000018047017000&mc=1"></a>

<img border="0" width="1" height="1" src="https://www14.a8.net/0.gif?a8mat=4BC5IX+F3KS36+50+7RXDHT" alt=""></section>

MITSUNE_INTERVAL;

    // Reverse insertion preserves offsets; indices 3,6,9 are headings 4,7,10.

    for ( $i = 9; $i >= 3; $i -= 3 ) {

        if ( isset( $sections[$i] ) ) $content = substr_replace( $content, $banner, $sections[$i], 0 );

    }

    return $content;

}, 22 );

/* mitsune-conoha-interval:end */





/* mitsune-site-review-20260920:start */

// Preserve the content revision date across category-only changes.

add_action( 'post_updated', function( $id, $after, $before ) {

    if ( 'post' !== $after->post_type || 'publish' !== $after->post_status ) return;

    foreach ( array( 'post_content', 'post_title', 'post_excerpt' ) as $field ) {

        if ( $after->$field !== $before->$field ) {

            update_post_meta( $id, '_mitsune_content_updated', substr( $after->post_modified, 0, 10 ) );

            break;

        }

    }

}, 10, 3 );



add_filter( 'the_content', function( $content ) {

    if ( ! is_front_page() || ! in_the_loop() || ! is_main_query() || is_admin() ) return $content;

    if ( false === strpos( $content, 'mitsune-home-card' ) ) return $content;

    $baseline = json_decode( '{"948":"2026-09-20","946":"2026-09-20","944":"2026-09-20","927":"2026-09-13","923":"2026-09-13","784":"2026-09-13","98":"2026-09-13","73":"2026-09-13","917":"2026-09-13","915":"2026-09-13","913":"2026-09-13","911":"2026-09-13","909":"2026-09-13","907":"2026-09-13","796":"2026-09-13","879":"2026-09-08","877":"2026-09-08","875":"2026-09-08","873":"2026-09-08","871":"2026-09-08","869":"2026-09-08","865":"2026-09-08","861":"2026-09-08","857":"2026-09-08","75":"2026-09-08","810":"2026-09-08","800":"2026-09-08","757":"2026-09-08","755":"2026-09-08","753":"2026-09-08","751":"2026-09-08","734":"2026-09-08","732":"2026-09-08","730":"2026-09-08","728":"2026-09-08","36":"2026-09-08","27":"2026-09-20","709":"2026-09-08","706":"2026-09-08","703":"2026-09-08","700":"2026-09-08","25":"2026-09-04","693":"2026-09-08","80":"2026-09-08","37":"2026-09-13","332":"2026-09-03","42":"2026-09-20","39":"2026-09-13","40":"2026-09-08","41":"2026-09-08","101":"2026-08-29","99":"2026-08-29","81":"2026-08-29","79":"2026-09-08","76":"2026-09-13","38":"2026-09-13"}', true );

    $posts = get_posts( array( 'post_type'=>'post', 'post_status'=>'publish', 'numberposts'=>-1 ) );

    $by_url = array(); $items = array();

    foreach ( $posts as $post ) $by_url[ get_permalink( $post ) ] = $post;

    $content = preg_replace_callback( '~<article class="mitsune-home-card"[^>]*>.*?</article>~s', function( $match ) use ( $by_url, $baseline, &$items ) {

        $card = $match[0];

        if ( ! preg_match( '~<h3><a href="([^"]+)"~', $card, $url ) || ! isset( $by_url[ html_entity_decode( $url[1] ) ] ) ) return $card;

        $post = $by_url[ html_entity_decode( $url[1] ) ]; $title = get_the_title( $post );

        $cats = wp_get_post_categories( $post->ID );

        $card = preg_replace( '~<article[^>]*>~', '<article class="mitsune-home-card" data-category="'.esc_attr( implode( ' ', $cats ) ).'">', $card, 1 );

        $card = preg_replace_callback( '~(<h3><a\b[^>]*>).*?(</a></h3>)~s', function( $m ) use ( $title ) { return $m[1].esc_html( $title ).$m[2]; }, $card, 1 );

        $card = preg_replace_callback( '~\b(?:aria-label|alt)="[^"]*"~', function( $m ) use ( $title ) { return strtok( $m[0], '=' ).'="'.esc_attr( $title ).'"'; }, $card );

        $excerpt = wp_strip_all_tags( $post->post_excerpt );

        if ( $excerpt ) $card = preg_replace_callback( '~(<div class="mitsune-home-card__body">.*?<p>).*?(</p>)~s', function( $m ) use ( $excerpt ) { return $m[1].esc_html( $excerpt ).$m[2]; }, $card, 1 );

        $date = get_post_meta( $post->ID, '_mitsune_content_updated', true );

        if ( ! $date ) $date = isset( $baseline[$post->ID] ) ? $baseline[$post->ID] : substr( $post->post_modified, 0, 10 );

        $card = preg_replace_callback( '~<time class="mitsune-home-card__updated"[^>]*>.*?</time>~s', function() use ( $date ) { return '<time class="mitsune-home-card__updated" datetime="'.esc_attr( $date ).'">更新 '.esc_html( str_replace( '-', '.', $date ) ).'</time>'; }, $card, 1 );

        $image = get_the_post_thumbnail_url( $post, 'full' );

        if ( $image ) $card = preg_replace_callback( '~<img\b[^>]*>~', function() use ( $image, $title ) { return '<img style="object-fit:contain" src="'.esc_url( $image ).'" alt="'.esc_attr( $title ).'" loading="lazy" decoding="async">'; }, $card, 1 );

        $items[] = array( '@type'=>'ListItem', 'position'=>count($items)+1, 'name'=>$title, 'url'=>get_permalink($post) );

        return $card;

    }, $content );

    return preg_replace_callback( '~<script type="application/ld\+json">(.*?)</script>~s', function( $m ) use ( $items ) {

        $data = json_decode( $m[1], true );

        if ( ! is_array($data) || ( $data['@type'] ?? '' ) !== 'ItemList' ) return $m[0];

        $data['itemListElement'] = $items;

        return '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG).'</script>';

    }, $content );

}, 8 );



add_action( 'wp_head', function() { ?>

<style id="mitsune-site-review-css">

.post-card-thumbnail img{object-fit:contain!important;background:#f2f7fd}

.mitsune-article-tools{display:flex;flex-wrap:wrap;gap:12px;margin:16px 0 24px;padding:18px;background:#f2f7fd;border:1px solid #d7e3f2;border-radius:12px;scroll-margin-top:90px}

.mitsune-article-tools[hidden]{display:none}

.mitsune-article-tools label{display:flex;flex-direction:column;gap:6px;flex:1 1 180px;font-size:14px;font-weight:700}

.mitsune-article-tools input,.mitsune-article-tools select{box-sizing:border-box;width:100%;min-width:0;padding:12px;border:1px solid #94a3b8;border-radius:6px;background:white;color:#08182f;font:inherit;font-size:16px}

.mitsune-article-tools button{align-self:flex-end;padding:12px;border:1px solid #94a3b8;border-radius:6px;background:white;color:#08182f;min-height:46px}

.mitsune-article-tools [role=status]{flex-basis:100%;margin:0;font-size:14px}

.mitsune-search-categories{display:block;font-size:12px;color:#475569;margin-top:6px;line-height:1.6;white-space:normal}

.mitsune-search-categories[hidden]{display:none}

.mitsune-search-categories summary{cursor:pointer;color:#086b80}

#mitsune-dictionary-search{scroll-margin-top:90px}

body.mitsune-search-active .entry-content .mitsune-conoha-wide{display:none!important}

.mitsune-search-shortcut{display:inline-block;padding:10px 16px;margin:0 0 16px;border:1px solid #087f99;border-radius:8px;color:#086b80;font-weight:700;background:#f0fbff}

.mitsune-archive-find{margin:0 0 24px;display:block;font-weight:700}

</style>

<?php }, 30 );



add_filter( 'the_content', function( $content ) {

    if ( is_admin() || ! is_singular('post') || ! in_the_loop() || ! is_main_query() || strpos($content,'id="mitsune-dictionary-search"')===false ) return $content;

    return '<a class="mitsune-search-shortcut" href="#mitsune-dictionary-search">このページのプロンプトを検索</a>'.$content;

}, 30 );

/* mitsune-site-review-20260920:end */

/* mitsune-gtag-bridge:start */
add_action( 'wp_head', function () {
    ?>
<script id="mitsune-gtag-bridge-js">
(function () {
    'use strict';
    // GTM owns configuration and page_view. Only provide its command queue API.
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag === 'function') return;
    var debug = new URLSearchParams(location.search).get('mitsune_analytics_debug') === '1';
    if (debug && typeof PerformanceObserver === 'function') {
        new PerformanceObserver(function (list) {
            list.getEntries().forEach(function (entry) {
                var url = new URL(entry.name);
                if (/google-analytics\.com$/.test(url.hostname) && /\/collect$/.test(url.pathname)) {
                    console.info('[mitsune-analytics] request ' + (url.searchParams.get('en') || 'batch') + ' to ' + (url.searchParams.get('tid') || 'unknown'));
                }
            });
        }).observe({type: 'resource', buffered: true});
        console.info('[mitsune-analytics] disabled=' + (window['ga-disable-G-4JPY9KNPPB'] === true));
    }
    window.gtag = function () {
        var args = arguments;
        if (args[0] === 'event' && /^mitsune_(prompt_copy|prompt_search|content_click)$/.test(args[1])) {
            // A GTM Google tag need not join gtag's default destination group.
            args[2] = Object.assign({send_to: 'G-4JPY9KNPPB'}, args[2]);
            if (debug) {
                var name = args[1];
                args[2].debug_mode = true;
                args[2].event_callback = function () { console.info('[mitsune-analytics] processed ' + name); };
                console.info('[mitsune-analytics] queued ' + name);
            }
        }
        window.dataLayer.push(args);
    };
    if (debug) console.info('[mitsune-analytics] gtag bridge initialized');
}());
</script>
    <?php
}, 0 );
/* mitsune-gtag-bridge:end */

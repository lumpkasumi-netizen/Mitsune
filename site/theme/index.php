<?php get_header(); ?>

<?php if ( is_home() && ! is_paged() ) : ?>
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">AI Art Prompts</div>
        <h1 class="hero-title">Stable Diffusion<br><span class="hero-title-accent">プロンプト辞典</span></h1>
            <div class="hero-actions">
            <a href="#posts" class="btn-hero-primary">プロンプトを探す</a>
            <a href="/category/tutorial" class="btn-hero-secondary">使い方を見る</a>
        </div>
    </div>
    <div class="hero-bg"></div>
</section>

<section class="category-feature">
    <div class="container">
        <h2 class="section-title">カテゴリーから探す</h2>
        <div class="category-grid">
            <?php wp_list_categories( [
                'title_li'   => '',
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => 12,
                'hide_empty' => 0,
            ] ); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<main class="site-main" id="main">
    <div class="container">

        <?php if ( ! ( is_home() && ! is_paged() ) ) : ?>
            <?php mitsune_breadcrumb(); ?>
        <?php endif; ?>

        <?php if ( is_home() && ! is_front_page() ) : ?>
            <div class="archive-header">
                <h1><?php single_post_title(); ?></h1>
            </div>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>

            <div id="posts" class="section-header">
                <h2 class="section-title">最新のプロンプト</h2>
                <a href="<?php echo esc_url( home_url( '/archives' ) ); ?>" class="section-more">すべて見る →</a>
            </div>

            <div class="posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>

                        <div class="post-card-thumbnail">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'full', [
                                        'alt' => esc_attr( get_the_title() ),
                                    ] ); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="post-card-no-image" aria-hidden="true">✦</a>
                            <?php endif; ?>
                        </div>

                        <div class="post-card-body">
                            <div class="post-card-meta">
                                <?php mitsune_the_category_badge(); ?>
                                <time class="post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>

                            <h2 class="post-card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <p class="post-card-excerpt"><?php the_excerpt(); ?></p>

                            <a href="<?php the_permalink(); ?>" class="read-more">
                                続きを読む <span aria-hidden="true">→</span>
                            </a>
                        </div>

                    </article>
                <?php endwhile; ?>
            </div>

            <div class="pagination">
                <?php mitsune_pagination(); ?>
            </div>

        <?php else : ?>
            <div class="no-results">
                <p><?php esc_html_e( '記事がまだありません。', 'mitsune' ); ?></p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>

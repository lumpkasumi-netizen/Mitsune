<?php get_header(); ?>

<main class="site-main" id="main">

    <div class="container archive-container">

        <?php mitsune_breadcrumb(); ?>

        <div class="archive-header">
            <div class="container">
                <?php if ( is_category() ) : ?>
                    <h1><?php single_cat_title(); ?></h1>
                <?php else : ?>
                    <?php the_archive_title( '<h1>', '</h1>' ); ?>
                <?php endif; ?>
                <?php the_archive_description( '<p>', '</p>' ); ?>
            </div>
        </div>

        <div class="content-area">

            <div class="primary-content">
                <?php if ( is_category() ) : ?>
                <a class="mitsune-archive-find" href="<?php echo esc_url( add_query_arg( 'category', get_queried_object_id(), home_url('/') ) . '#mitsune-article-tools' ); ?>">このカテゴリの記事を検索 →</a>
                <?php endif; ?>
                <?php if ( have_posts() ) : ?>
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
<h2 class="post-card-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    <a href="<?php the_permalink(); ?>" class="read-more">続きを読む →</a>
                                </div>

                            </article>
                        <?php endwhile; ?>
                    </div>

                    <div class="pagination">
                        <?php mitsune_pagination(); ?>
                    </div>

                <?php else : ?>
                    <div class="no-results">
                        <p><?php esc_html_e( '記事が見つかりませんでした。', 'mitsune' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>

        </div>
    </div>
</main>

<?php get_footer(); ?>

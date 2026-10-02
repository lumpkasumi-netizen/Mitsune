<?php get_header(); ?>

<main class="site-main" id="main">
    <div class="container">
        <div class="content-area">

            <div class="primary-content">
                <?php while ( have_posts() ) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="single-thumbnail">
                                <?php the_post_thumbnail( 'mitsune-hero', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
                            </div>
                        <?php endif; ?>

                        <h1 class="page-title"><?php the_title(); ?></h1>
                        <hr class="single-divider">

                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>

                        <?php
                        wp_link_pages( [
                            'before' => '<div class="page-links">' . esc_html__( 'ページ:', 'mitsune' ),
                            'after'  => '</div>',
                        ] );
                        ?>

                    </article>

                <?php endwhile; ?>
            </div>

            <?php get_sidebar(); ?>

        </div>
    </div>
</main>

<?php get_footer(); ?>

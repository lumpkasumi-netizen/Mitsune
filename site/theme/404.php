<?php get_header(); ?>

<main class="site-main" id="main">
    <div class="container">
        <div class="error-404">
            <div class="error-code">404</div>
            <h2><?php esc_html_e( 'ページが見つかりません', 'mitsune' ); ?></h2>
            <p><?php esc_html_e( 'お探しのページは移動・削除されたか、URLが間違っている可能性があります。', 'mitsune' ); ?></p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
                <?php esc_html_e( 'トップページへ戻る', 'mitsune' ); ?>
            </a>
        </div>
    </div>
</main>

<?php get_footer(); ?>

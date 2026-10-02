<?php
$mitsune_sidebar_posts = [];
$mitsune_sidebar_seen  = [];

$mitsune_ranked_posts = new WP_Query( [
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

foreach ( $mitsune_ranked_posts->posts as $mitsune_ranked_post ) {
    $mitsune_sidebar_posts[] = $mitsune_ranked_post;
    $mitsune_sidebar_seen[]  = $mitsune_ranked_post->ID;
}
wp_reset_postdata();

if ( count( $mitsune_sidebar_posts ) < 5 ) {
    $mitsune_latest_posts = new WP_Query( [
        'post_type'           => 'post',
        'posts_per_page'      => 5 - count( $mitsune_sidebar_posts ),
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'post__not_in'        => $mitsune_sidebar_seen,
        'orderby'             => 'date',
        'order'               => 'DESC',
    ] );
    foreach ( $mitsune_latest_posts->posts as $mitsune_latest_post ) {
        $mitsune_sidebar_posts[] = $mitsune_latest_post;
    }
    wp_reset_postdata();
}
?>
<style id="mitsune-sidebar-thumb-css">
.mitsune-ranking-widget{padding:16px;border:1px solid #dbe7f3;border-radius:8px;background:#fff;box-shadow:0 10px 28px rgba(15,23,42,.06)}
.mitsune-ranking-title{margin:0 0 13px!important;padding:0 0 10px;border-bottom:2px solid #0f766e;color:#0f172a;font-size:17px;line-height:1.35;font-weight:800}
.mitsune-ranking-list{display:grid;gap:12px;margin:0!important;padding:0!important;list-style:none!important}
.mitsune-ranking-item{display:grid;grid-template-columns:76px minmax(0,1fr);gap:10px;align-items:center;margin:0!important;padding:0 0 12px!important;border-bottom:1px solid #e5eef7}
.mitsune-ranking-item:last-child{border-bottom:0;padding-bottom:0!important}
.mitsune-ranking-thumb{position:relative;overflow:hidden;aspect-ratio:16/10;border-radius:7px;background:#f8fafc}
.mitsune-ranking-thumb img{display:block;width:100%;height:100%;object-fit:contain;background:#f8fafc}
.mitsune-ranking-rank{position:absolute;left:5px;top:5px;display:inline-grid;place-items:center;min-width:30px;height:22px;padding:0 6px;border-radius:999px;background:#0f172a;color:#fff;font-size:11px;font-weight:900}
.mitsune-ranking-link{display:block;min-width:0;color:#0f172a;text-decoration:none;font-size:12px;font-weight:800;line-height:1.45}
.mitsune-ranking-link:hover{color:#0369a1;text-decoration:underline;text-underline-offset:3px}
</style>
<aside class="sidebar" id="secondary" role="complementary">
    <div class="widget mitsune-ranking-widget">
        <h2 class="wp-block-heading mitsune-ranking-title">&#12521;&#12531;&#12461;&#12531;&#12464;</h2>
        <?php if ( $mitsune_sidebar_posts ) : ?>
            <ol class="mitsune-ranking-list">
                <?php
                $mitsune_rank = 1;
                foreach ( $mitsune_sidebar_posts as $mitsune_sidebar_post ) :
                    $mitsune_sidebar_title = function_exists( 'mitsune_sidebar_ranking_title' )
                        ? mitsune_sidebar_ranking_title( get_the_title( $mitsune_sidebar_post ) )
                        : preg_replace( '/\s*\x{FF5C}.*/u', '', get_the_title( $mitsune_sidebar_post ) );
                    $mitsune_sidebar_url = get_permalink( $mitsune_sidebar_post );
                ?>
                    <li class="mitsune-ranking-item">
                        <a class="mitsune-ranking-thumb" href="<?php echo esc_url( $mitsune_sidebar_url ); ?>">
                            <span class="mitsune-ranking-rank"><?php echo esc_html( $mitsune_rank ); ?>&#20301;</span>
                            <?php if ( has_post_thumbnail( $mitsune_sidebar_post ) ) : ?>
                                <?php echo get_the_post_thumbnail( $mitsune_sidebar_post, 'medium', [ 'alt' => esc_attr( get_the_title( $mitsune_sidebar_post ) ) ] ); ?>
                            <?php endif; ?>
                        </a>
                        <a class="mitsune-ranking-link" href="<?php echo esc_url( $mitsune_sidebar_url ); ?>"><?php echo esc_html( $mitsune_sidebar_title ); ?></a>
                    </li>
                <?php
                    $mitsune_rank++;
                endforeach;
                ?>
            </ol>
        <?php endif; ?>
    </div>

<!-- mitsune-sidebar-ad:start -->
<?php if ( function_exists( 'mitsune_render_manual_ad' ) ) mitsune_render_manual_ad( 'sidebar' ); ?>
<!-- mitsune-sidebar-ad:end --></aside>

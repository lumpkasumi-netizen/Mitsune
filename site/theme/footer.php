<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-logo"><?php bloginfo( 'name' ); ?></div>
        <p class="footer-tagline"><?php bloginfo( 'description' ); ?></p>

        <nav class="footer-nav" aria-label="フッターカテゴリ">
            <ul>
                <li><a href="<?php echo esc_url( home_url( '/category/getting-started/' ) ); ?>">入門</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/prompt-dictionary/' ) ); ?>">基本・補正</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/character/' ) ); ?>">人物・衣装</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/world-building/' ) ); ?>">背景・場所</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/style/' ) ); ?>">画風・演出</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/themes-motifs/' ) ); ?>">テーマ別プロンプト</a></li>
                <li><a href="<?php echo esc_url( home_url( '/category/templates-examples/' ) ); ?>">テンプレ・実例</a></li>
            </ul>
        </nav>

        <hr class="footer-divider">
        <nav class="footer-legal" aria-label="運営情報">
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a>
            <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">プライバシーポリシー・免責事項</a>
        </nav>

        <p class="footer-copy">
            &copy; <?php echo esc_html( date( 'Y' ) ); ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>.
            All rights reserved.
        </p>
    </div>
</footer>

</div><!-- .site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>

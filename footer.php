<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */
?>
        <footer>
    <?php get_sidebar( 'footer' ); ?>

                            <a href="<?php echo home_url( '/' ) ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                            <a href="http://wordpress.org/" title="Semantic Personal Publishing Platform" rel="generator">Proudly powered by WordPress </a>
        </footer>
    </div>
    <?php
            /* Always have wp_footer() just before the closing </body>
             * tag of your theme, or you will break many plugins, which
             * generally use this hook to reference JavaScript files.
             */

            wp_footer();
    ?>
</body>
</html>
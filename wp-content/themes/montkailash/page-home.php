<?php
/**
 * Template Name: home
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Montkailash
 * @since Montkailash 2.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container-fluid">
            <?php echo do_shortcode('[wonderplugin_slider id="1"]'); ?>
        </div>

        <?php get_template_part('bandeor', 'bandeor'); ?>

        <?php while (have_posts()) {

            the_post();
            the_content();
        }
        ?>

    </main>
    <!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>

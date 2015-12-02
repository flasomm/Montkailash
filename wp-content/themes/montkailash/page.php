<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Montkailash
 * @since Montkailash 1.0
 */

get_header(); ?>

<?php get_template_part('breadcrumb', 'breadcrumb'); ?>

<div id="page-content" class="container pd-b">

    <?php while (have_posts()) {

        the_post();
        the_content();
    }
    ?>

</div>

<?php get_template_part('bandeor', 'bandeor'); ?>
<?php get_footer(); ?>

<?php
/**
 * Front page — composed of template parts so sections can be reused.
 *
 * @package Studie247
 */

get_header();
?>

<?php get_template_part( 'template-parts/hero' ); ?>

<?php get_template_part( 'template-parts/section', 'services' ); ?>

<?php get_template_part( 'template-parts/section', 'studio' ); ?>

<?php get_template_part( 'template-parts/section', 'process' ); ?>

<?php get_template_part( 'template-parts/section', 'cases' ); ?>

<?php get_template_part( 'template-parts/section', 'testimonials' ); ?>

<?php get_template_part( 'template-parts/section', 'faq' ); ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php
get_footer();

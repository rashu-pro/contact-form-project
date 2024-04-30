<?php
/*
 * Template Name: Logout
 */
get_header();

wp_logout();

// Redirect to homepage
wp_redirect(home_url());

get_footer();

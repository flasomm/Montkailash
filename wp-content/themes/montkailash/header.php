<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <title><?php wp_title(); ?></title>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="mont-kaialsh, spa paris, massages, hammam, bien etre, soins, tibet">
    <meta name="author" content="Physalix">
    <meta name="contact" content="fs@physalix.com" />
    <meta name="copyright" content="MontKailash.fr" />
    <meta name="lang" content="fr_FR">
    <meta name="robots" content="index, follow">
    <meta property="fb:admins" content="205725142785023">
    <meta name="google-site-verification" content="8fdLEMmYXOn7nc2iMwQHfGuF5PEjhnLBGhNeIGC2WzQ">
    <meta property="og:site_name" content="Mont Kailash Spa Paris">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:description" content="Reconnu pour ses massages et soins thérapeutiques à Paris, le spa Mont Kailash vous fait voyager au Tibet : massages, hammam, ainsi que des soins corps et visage.">
    <meta property="og:title" content="Spa Mont Kailash : centres bien-être du Tibet à Paris">
    <meta property="og:url" content="http://montkailash-bien-etre.fr/">
    <link rel="shortcut icon" href="/wp-content/themes/montkailash/favicon.ico" type="image/x-icon" />
    <link href="https://plus.google.com/+SpaRitueldesSensParis" rel="publisher">
    <link rel="author" href="https://plus.google.com/+FabriceSommavilla/posts">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,700,300,400" rel="stylesheet" type="text/css">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">

    <?php get_template_part( 'topmenu', 'topmenu' ); ?>

    <div id="content" class="site-content">

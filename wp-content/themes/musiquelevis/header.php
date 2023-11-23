<?php
/**
 * Header template
 *
 * @since Comrad 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#054597" />
    <title><?php wp_title('|', true, 'right'); ?></title>

    <!-- Favicon -->
    <link href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/touch.png" rel="apple-touch-icon-precomposed">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon-16x16.png">
		<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/site.webmanifest">
		<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/safari-pinned-tab.svg" color="#3b29ff">
		<meta name="msapplication-TileColor" content="#3b29ff">
		<meta name="theme-color" content="#ffffff">

    <!-- Inline Style -->
    <link href="https://fonts.googleapis.com/css?family=Oswald|Work+Sans:400,700" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDCINVRmiU2AlezhqfymFgNAkd14yXTRkk"></script>
    

    <!-- Global site tag (gtag.js) - Google Analytics -->
	<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-B0GE29LRFQ"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-B0GE29LRFQ');
	</script> -->
 
	<!-- Enable HTML5 tags -->
	<!--[if lt IE 9]>
		<script type="text/javascript" src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- MatchMedia polyfill (for enquire.js under IE9) -->
	<!--[if lte IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/assets/js/media.match.min.js"></script>
	<![endif]-->

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<header id="header">
		<?php get_template_part( 'views/blocs/main-navigation' ); ?>
		<?php get_template_part( 'views/blocs/header' ); ?>
	</header>
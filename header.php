<?php
/**
 * The Header for our theme.
 *
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo('charset'); ?>" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width">
		<title><?php wp_title('|', true, 'right'); ?></title>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
		<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.js"></script>
		<![endif]-->
		<?php
		/* We add some JavaScript to pages with the comment form
		 * to support sites with threaded comments (when in use).
		 */
		if (is_singular() && get_option('thread_comments'))
			wp_enqueue_script('comment-reply');

		/* Always have wp_head() just before the closing </head>
		 * tag of your theme, or you will break many plugins, which
		 * generally use this hook to add elements to <head> such
		 * as styles, scripts, and meta tags.
		 */
		?>
<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<div class="container">
			<header class="grid12" role="banner">      
				<h1><a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
				<p><?php bloginfo('description'); ?></p>               
			</header>
			<nav role="navigation" class="menu grid12">
				<?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff */ ?>
				<a href="#content" class="access" title="<?php esc_attr_e('Skip to content', TabulaRasa::get_textdomain()); ?>"><?php _e('Skip to content', TabulaRasa::get_textdomain()); ?></a>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
				<?php wp_nav_menu(array('container' => false, 'theme_location' => 'primary')); ?>
			</nav><!-- #access -->
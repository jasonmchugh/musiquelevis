<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?> <?php bloginfo('name'); ?></title>

		<link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/touch.png" rel="apple-touch-icon-precomposed">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/favicon-16x16.png">
		<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/site.webmanifest">
		<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicons/safari-pinned-tab.svg" color="#3b29ff">
		<meta name="msapplication-TileColor" content="#3b29ff">
		<meta name="theme-color" content="#ffffff">

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<?php wp_head(); ?>
		<script>
        
        </script>

	</head>

	<body <?php body_class(); ?>>
		<div id="circle-cursor"></div>

		<header class="head" <?php if ( get_field('option_light_mode') ): ?>data-theme='light'<?php else: ?><?php endif; ?>>
			<div class="container_large">
				<div class="header row">
					<!-- logo -->
					<div class="header_logo">
						<a href="<?php echo home_url(); ?>" class="hoverable">
							<?php
							$logo = get_field('logo', 'option');
							if( !empty($logo) ): ?>
								<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>" class="header_logo_navy" />
							<?php endif; ?>
							<?php
							$menulogo = get_field('menu_logo', 'option');
							if( !empty($menulogo) ): ?>
								<img src="<?php echo $menulogo['url']; ?>" alt="<?php echo $menulogo['alt']; ?>" class="header_logo_white" />
							<?php endif; ?>
						</a>
					</div>
					<!-- /logo -->
					<div class="header_menu">
						<!-- nav -->
						<div class="header_menu_hamburger">
							<div class="header_menu_hamburger_btn hoverable" id="menu-btn">&nbsp;</div>
						</div>
						<!-- /nav -->
					</div>
					<div class="mobileMenu" id="mobileMenu">
						<div class="mobileMenu_wrapper">
							<div class="mobileMenu_row">
								<div class="mobileMenu_row_head">
									<div class="mobileMenu_row_head_logo">
										<a href="<?php echo home_url(); ?>">
											<?php
											$menulogo = get_field('menu_logo', 'option');
											if( !empty($menulogo) ): ?>
												<img src="<?php echo $menulogo['url']; ?>" alt="<?php echo $menulogo['alt']; ?>" class="mobileMenu_row_head_logo_img" />
											<?php endif; ?>
										</a>
									</div>
									<div class="mobileMenu_row_head_close">
										<span id="close-btnMobile"></span>
									</div>
									<div class="mobileMenu_row_head_secondary">
										<div class="mobileMenu_row_head_secondary_nav">
											<?php secondary_nav(); ?>
										</div>
										<div class="mobileMenu_row_head_secondary_langue">
											<?php langue_nav(); ?>
										</div>
									</div>
								</div>
								<div class="mobileMenu_row_navMobile">
									<?php main_nav(); ?>
								</div>
								<div class="mobileMenu_row_bottomMobile">
									<?php if( get_field('slogan_menu', 'option') ): ?>
										<span class="title">
											<?php the_field('slogan_menu', 'option'); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>


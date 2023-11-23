<?php
/**
 * Footer template
 *
 * @since Bravad 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

		<?php
			$blocsuccursales = get_field('afficher_bloc_succursales');
			$blocabout = get_field('afficher_bloc_about');
			$blocnewsletter = get_field('afficher_bloc_infolettre');
		?>

		<?php if( $blocsuccursales == 'oui' ){ ?>
			<?php get_template_part( 'views/modules/succursales' ); ?>
		<?php } ?>


		<?php if( $blocabout == 'oui' ){ ?>
			<?php get_template_part( 'views/modules/about' ); ?>
		<?php } ?>


		<?php if( $blocnewsletter == 'oui' ){ ?>
			<?php get_template_part( 'views/modules/newsletter' ); ?>
		<?php } ?>

		<footer>
			<div class="_container">
				<div class="_row">
					<div class="_col _col--xl-6 _col--md-6 _col--sm-12">
						<?php dynamic_sidebar( 'footer1' ); ?>
					</div>
					<div class="_col _col--xl-3 _col--md-3 _col--sm-12">
						<?php dynamic_sidebar( 'footer2' ); ?>
					</div>
					<div class="_col _col--xl-3 _col--md-3 _col--sm-12">
						<?php dynamic_sidebar( 'footer3' ); ?>
					</div>
				</div>
				<div class="_row copyrights">
					<div class="_col _col--xl-4 _col--md-4 _col--sm-12">
						<?php echo date('Y'); ?> © Musique Lévis <i>-</i> <span><?php _e( 'Tous droits réservés.', 'bravad' ); ?></span>
					</div>
					<div class="_col _col--xl-8 _col--md-8 _col--sm-12 right">
						<?php /*<a class="logo__wrap" href="<?php echo get_home_url(); ?>">
							<?php
								echo file_get_contents(str_replace('/index.php', '', $_SERVER['SCRIPT_FILENAME']) . '/wp-content/themes/musiquelevis/assets/img/instant-comptant.svg');
							?>
						</a>*/ ?>
						<?php dynamic_sidebar( 'footer4' ); ?><a href="javascript:Didomi.preferences.show()" class="consent">Consentement</a>
					</div>
				</div>
			</div>
		</footer>
		<script type="text/javascript">window.gdprAppliesGlobally=true;(function(){(function(e,r){var t=document.createElement("link");t.rel="preconnect";t.as="script";var n=document.createElement("link");n.rel="dns-prefetch";n.as="script";var i=document.createElement("link");i.rel="preload";i.as="script";var o=document.createElement("script");o.id="spcloader";o.type="text/javascript";o["async"]=true;o.charset="utf-8";var a="https://sdk.privacy-center.org/"+e+"/loader.js?target_type=notice&target="+r;if(window.didomiConfig&&window.didomiConfig.user){var c=window.didomiConfig.user;var s=c.country;var d=c.region;if(s){a=a+"&country="+s;if(d){a=a+"&region="+d}}}t.href="https://sdk.privacy-center.org/";n.href="https://sdk.privacy-center.org/";i.href=a;o.src=a;var p=document.getElementsByTagName("script")[0];p.parentNode.insertBefore(t,p);p.parentNode.insertBefore(n,p);p.parentNode.insertBefore(i,p);p.parentNode.insertBefore(o,p)})("e1c09c19-87d7-4c5e-9138-d918ba424799","7TidnPHn")})();</script>

		<?php wp_footer(); ?>
	</body>
</html>

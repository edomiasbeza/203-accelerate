<?php
/**
 * The template part for topbar
 *
 * @package VW Painter 
 * @subpackage vw_painter
 * @since VW Painter 1.0
 */
?>
<?php if( get_theme_mod('vw_painter_topbar_hide_show') != ''){ ?>
	<div id="topbar">
		<div class="container">
			<div class="contact_details">
				<div class="row">
		        	<div class="col-lg-4 col-md-6">
		        		<?php if(get_theme_mod('vw_painter_location') != ''){ ?>
							<i class="<?php echo esc_attr(get_theme_mod('vw_painter_location_icon','fas fa-location-arrow')); ?>"></i><span><?php echo esc_html(get_theme_mod('vw_painter_location',''));?></span>
						<?php }?>
					</div>
		        	<div class="col-lg-3 col-md-6 email">
		        		<?php if(get_theme_mod('vw_painter_email_address') != ''){ ?>
		        			<i class="<?php echo esc_attr(get_theme_mod('vw_painter_email_icon','fas fa-envelope-open')); ?>"></i><span><?php echo esc_html(get_theme_mod('vw_painter_email_address',''));?></span>
		        		<?php }?>
		        	</div>
		        	<div class="col-lg-3 col-md-6">
		        		<?php dynamic_sidebar('social-widget'); ?>
		        	</div>
		        	<div class="col-lg-2 col-md-6">
		        		<?php if(get_theme_mod('vw_painter_button_text') != ''){ ?>
		        			<div class="top-button">
		        				<a href="<?php echo esc_url(get_theme_mod('vw_painter_button_url',''));?>"><?php echo esc_html(get_theme_mod('vw_painter_button_text',''));?><span class="screen-reader-text"><?php esc_html_e( 'REQUEST A QUOTE','vw-painter' );?></span></a>
		        			</div>
		        		<?php }?>
		        	</div>
		        </div>
			</div>
		</div>
	</div>
<?php }?>
<?php
/**
 * The template part for displaying post
 *
 * @package VW Painter
 * @subpackage vw-painter
 * @since VW Painter 1.0
 */
?>
<div class="col-md-6 col-lg-4">
	<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>
	    <div class="post-main-box">
	      	<div class="box-image">
	          	<?php 
		            if(has_post_thumbnail()) { 
		              the_post_thumbnail(); 
		            }
	          	?>
	        </div>
	        <h2 class="section-title"><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo the_title_attribute(); ?>"><?php the_title();?><span class="screen-reader-text"><?php the_title(); ?></span></a></h2>    
	        <div class="new-text">
	          	<div class="entry-content">
	          		<p>
			          <?php $excerpt = get_the_excerpt(); echo esc_html( vw_painter_string_limit_words( $excerpt, esc_attr(get_theme_mod('vw_painter_excerpt_number','30')))); ?> <?php echo esc_html( get_theme_mod('vw_painter_excerpt_suffix','') ); ?>
			        </p>
	          	</div>
	        </div>
	        <?php if( get_theme_mod('vw_painter_blog_button_text','READ MORE') != ''){ ?>
		        <div class="content-bttn">
		          <a href="<?php echo esc_url( get_permalink() );?>" class="blogbutton-small"><?php echo esc_html(get_theme_mod('vw_painter_blog_button_text',__('READ MORE','vw-painter')));?><span class="screen-reader-text"><?php echo esc_html(get_theme_mod('vw_painter_blog_button_text',__('READ MORE','vw-painter')));?></span></a>
		        </div>
		    <?php } ?>
	    </div>
	    <div class="clearfix"></div>
  	</article>
</div>
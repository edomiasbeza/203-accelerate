<?php
/**
 * The template part for header
 *
 * @package VW Painter 
 * @subpackage vw_painter
 * @since VW Painter 1.0
 */
?>

<?php
  $vw_painter_search_hide_show = get_theme_mod( 'vw_painter_search_hide_show' );
  if ( 'Disable' == $vw_painter_search_hide_show ) {
   $colmd = 'col-lg-9 col-md-9';
  } else { 
   $colmd = 'col-lg-8 col-md-6 col-6';
  } 
?>

<div class="container">
  <div id="top-header">
    <div class="header-menu <?php if( get_theme_mod( 'vw_painter_sticky_header') != '') { ?> header-sticky"<?php } else { ?>close-sticky <?php } ?>">
      <div class="row">
        <div class="col-lg-3 col-md-4">
          <div class="logo">
            <?php if ( has_custom_logo() ) : ?>
              <div class="site-logo"><?php the_custom_logo(); ?></div>
            <?php endif; ?>
            <?php $blog_info = get_bloginfo( 'name' ); ?>
              <?php if ( ! empty( $blog_info ) ) : ?>
                <?php if ( is_front_page() && is_home() ) : ?>
                  <?php if( get_theme_mod('vw_painter_logo_title_hide_show',true) != ''){ ?>
                    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                  <?php } ?>
                <?php else : ?>
                  <?php if( get_theme_mod('vw_painter_logo_title_hide_show',true) != ''){ ?>
                    <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                  <?php } ?>
                <?php endif; ?>
              <?php endif; ?>
              <?php
                $description = get_bloginfo( 'description', 'display' );
                if ( $description || is_customize_preview() ) :
              ?>
              <?php if( get_theme_mod('vw_painter_tagline_hide_show',true) != ''){ ?>
                <p class="site-description">
                  <?php echo $description; ?>
                </p>
              <?php } ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="<?php echo esc_html( $colmd ); ?>">
          <?php get_template_part( 'template-parts/header/navigation' ); ?>
        </div>
        <?php if ( 'Disable' != $vw_painter_search_hide_show ) {?>
          <div class="col-lg-1 col-md-2 col-6">
            <div class="search-box">
              <button type="button" data-toggle="modal" data-target="#myModal"><i class="fas fa-search"></i></button>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-body">
          <div class="serach_inner">
            <?php get_search_form(); ?>
          </div>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    </div>
  </div>
</div>
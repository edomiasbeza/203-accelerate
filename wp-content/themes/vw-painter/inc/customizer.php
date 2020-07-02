<?php
/**
 * VW Painter Theme Customizer
 *
 * @package VW Painter
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function vw_painter_custom_controls() {

    load_template( trailingslashit( get_template_directory() ) . '/inc/custom-controls.php' );
}
add_action( 'customize_register', 'vw_painter_custom_controls' );

function vw_painter_customize_register( $wp_customize ) {

	load_template( trailingslashit( get_template_directory() ) . 'inc/customize-homepage/class-customize-homepage.php' );

	load_template( trailingslashit( get_template_directory() ) . '/inc/icon-picker.php' );

	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage'; 
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial( 'blogname', array( 
		'selector' => '.logo .site-title a', 
	 	'render_callback' => 'vw_painter_customize_partial_blogname', 
	)); 

	$wp_customize->selective_refresh->add_partial( 'blogdescription', array( 
		'selector' => 'p.site-description', 
		'render_callback' => 'vw_painter_customize_partial_blogdescription', 
	));

	//add home page setting pannel
	$VWPainterParentPanel = new VW_Painter_WP_Customize_Panel( $wp_customize, 'vw_painter_panel_id', array(
		'capability' => 'edit_theme_options',
		'theme_supports' => '',
		'title' => 'VW Settings',
		'priority' => 10,
	));

	$wp_customize->add_section( 'vw_painter_left_right', array(
    	'title'      => __( 'General Settings', 'vw-painter' ),
		'panel' => 'vw_painter_panel_id'
	) );

	$wp_customize->add_setting('vw_painter_width_option',array(
        'default' => __('Full Width','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control(new VW_Painter_Image_Radio_Control($wp_customize, 'vw_painter_width_option', array(
        'type' => 'select',
        'label' => __('Width Layouts','vw-painter'),
        'description' => __('Here you can change the width layout of Website.','vw-painter'),
        'section' => 'vw_painter_left_right',
        'choices' => array(
            'Full Width' => get_template_directory_uri().'/images/full-width.png',
            'Wide Width' => get_template_directory_uri().'/images/wide-width.png',
            'Boxed' => get_template_directory_uri().'/images/boxed-width.png',
    ))));

	// Add Settings and Controls for Layout
	$wp_customize->add_setting('vw_painter_theme_options',array(
        'default' => __('Right Sidebar','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'	        
	) );
	$wp_customize->add_control('vw_painter_theme_options', array(
        'type' => 'select',
        'label' => __('Post Sidebar Layout','vw-painter'),
        'description' => __('Here you can change the sidebar layout for posts. ','vw-painter'),
        'section' => 'vw_painter_left_right',
        'choices' => array(
            'Left Sidebar' => __('Left Sidebar','vw-painter'),
            'Right Sidebar' => __('Right Sidebar','vw-painter'),
            'One Column' => __('One Column','vw-painter'),
            'Three Columns' => __('Three Columns','vw-painter'),
            'Four Columns' => __('Four Columns','vw-painter'),
            'Grid Layout' => __('Grid Layout','vw-painter')
        ),
	));

	$wp_customize->add_setting('vw_painter_page_layout',array(
        'default' => __('One Column','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control('vw_painter_page_layout',array(
        'type' => 'select',
        'label' => __('Page Sidebar Layout','vw-painter'),
        'description' => __('Here you can change the sidebar layout for pages. ','vw-painter'),
        'section' => 'vw_painter_left_right',
        'choices' => array(
            'Left Sidebar' => __('Left Sidebar','vw-painter'),
            'Right Sidebar' => __('Right Sidebar','vw-painter'),
            'One Column' => __('One Column','vw-painter')
        ),
	) );

	//Woocommerce Shop Page Sidebar
	$wp_customize->add_setting( 'vw_painter_woocommerce_shop_page_sidebar',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_woocommerce_shop_page_sidebar',array(
		'label' => esc_html__( 'Shop Page Sidebar','vw-painter' ),
		'section' => 'vw_painter_left_right'
    )));

    //Woocommerce Single Product page Sidebar
	$wp_customize->add_setting( 'vw_painter_woocommerce_single_product_page_sidebar',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_woocommerce_single_product_page_sidebar',array(
		'label' => esc_html__( 'Single Product Sidebar','vw-painter' ),
		'section' => 'vw_painter_left_right'
    )));

	//Pre-Loader
	$wp_customize->add_setting( 'vw_painter_loader_enable',array(
        'default' => 1,
        'transport' => 'refresh',
        'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_loader_enable',array(
        'label' => esc_html__( 'Pre-Loader','vw-painter' ),
        'section' => 'vw_painter_left_right'
    )));

	$wp_customize->add_setting('vw_painter_loader_icon',array(
        'default' => __('Two Way','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control('vw_painter_loader_icon',array(
        'type' => 'select',
        'label' => __('Pre-Loader Type','vw-painter'),
        'section' => 'vw_painter_left_right',
        'choices' => array(
            'Two Way' => __('Two Way','vw-painter'),
            'Dots' => __('Dots','vw-painter'),
            'Rotate' => __('Rotate','vw-painter')
        ),
	) );

	//Topbar
	$wp_customize->add_section( 'vw_painter_topbar', array(
    	'title'      => __( 'Topbar Settings', 'vw-painter' ),
		'priority'   => 30,
		'panel' => 'vw_painter_panel_id'
	) );

	$wp_customize->add_setting( 'vw_painter_topbar_hide_show',
       array(
      'default' => 0,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_topbar_hide_show',
       array(
      'label' => esc_html__( 'Show / Hide Topbar','vw-painter' ),
      'section' => 'vw_painter_topbar'
    )));

    $wp_customize->add_setting('vw_painter_topbar_padding_top_bottom',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_topbar_padding_top_bottom',array(
		'label'	=> __('Topbar Padding Top Bottom','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

    //Sticky Header
	$wp_customize->add_setting( 'vw_painter_sticky_header',array(
        'default' => 0,
        'transport' => 'refresh',
        'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_sticky_header',array(
        'label' => esc_html__( 'Sticky Header','vw-painter' ),
        'section' => 'vw_painter_topbar'
    )));

	$wp_customize->add_setting( 'vw_painter_search_hide_show',
       array(
      'default' => 1,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_search_hide_show',
       array(
      'label' => esc_html__( 'Show / Hide Search','vw-painter' ),
      'section' => 'vw_painter_topbar'
    )));

    $wp_customize->add_setting('vw_painter_search_font_size',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_search_font_size',array(
		'label'	=> __('Search Font Size','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_search_padding_top_bottom',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_search_padding_top_bottom',array(
		'label'	=> __('Search Padding Top Bottom','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_search_padding_left_right',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_search_padding_left_right',array(
		'label'	=> __('Search Padding Left Right','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting( 'vw_painter_search_border_radius', array(
		'default'              => "",
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_search_border_radius', array(
		'label'       => esc_html__( 'Search Border Radius','vw-painter' ),
		'section'     => 'vw_painter_topbar',
		'type'        => 'range',
		'input_attrs' => array(
			'step'             => 1,
			'min'              => 1,
			'max'              => 50,
		),
	) );

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_location', array( 
		'selector' => '.contact_details span', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_location', 
	));

    $wp_customize->add_setting('vw_painter_location_icon',array(
		'default'	=> 'fas fa-location-arrow',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control(new VW_Painter_Fontawesome_Icon_Chooser(
        $wp_customize,'vw_painter_location_icon',array(
		'label'	=> __('Add Location Icon','vw-painter'),
		'transport' => 'refresh',
		'section'	=> 'vw_painter_topbar',
		'setting'	=> 'vw_painter_location_icon',
		'type'		=> 'icon'
	)));

	$wp_customize->add_setting('vw_painter_location',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control('vw_painter_location',array(
		'label'	=> __('Add Location','vw-painter'),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_email_icon',array(
		'default'	=> 'fas fa-envelope-open',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control(new VW_Painter_Fontawesome_Icon_Chooser(
        $wp_customize,'vw_painter_email_icon',array(
		'label'	=> __('Add Email Icon','vw-painter'),
		'transport' => 'refresh',
		'section'	=> 'vw_painter_topbar',
		'setting'	=> 'vw_painter_email_icon',
		'type'		=> 'icon'
	)));

	$wp_customize->add_setting('vw_painter_email_address',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control('vw_painter_email_address',array(
		'label'	=> __('Add Email Address','vw-painter'),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_button_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control('vw_painter_button_text',array(
		'label'	=> __('Add Button Text','vw-painter'),
		'section'=> 'vw_painter_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_button_url',array(
		'default'=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));	
	$wp_customize->add_control('vw_painter_button_url',array(
		'label'	=> __('Add Button Link','vw-painter'),
		'section'=> 'vw_painter_topbar',
		'type'=> 'url'
	));
    
	//Slider
	$wp_customize->add_section( 'vw_painter_slidersettings' , array(
    	'title'      => __( 'Slider Settings', 'vw-painter' ),
		'priority'   => null,
		'panel' => 'vw_painter_panel_id'
	) );

	$wp_customize->add_setting( 'vw_painter_slider_hide_show',
       array(
      'default' => 0,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_slider_hide_show',
       array(
      'label' => esc_html__( 'Show / Hide Slider','vw-painter' ),
      'section' => 'vw_painter_slidersettings'
    )));

    //Selective Refresh
    $wp_customize->selective_refresh->add_partial('vw_painter_slider_hide_show',array(
		'selector'        => '#slider .inner_carousel h1',
		'render_callback' => 'vw_painter_customize_partial_vw_painter_slider_hide_show',
	));

	for ( $count = 1; $count <= 4; $count++ ) {
		// Add color scheme setting and control.
		$wp_customize->add_setting( 'vw_painter_slider_page' . $count, array(
			'default'           => '',
			'sanitize_callback' => 'vw_painter_sanitize_dropdown_pages'
		) );
		$wp_customize->add_control( 'vw_painter_slider_page' . $count, array(
			'label'    => __( 'Select Slide Image Page', 'vw-painter' ),
			'description' => __('Slider image size (1500 x 590)','vw-painter'),
			'section'  => 'vw_painter_slidersettings',
			'type'     => 'dropdown-pages'
		) );
	}

	//content layout
	$wp_customize->add_setting('vw_painter_slider_content_option',array(
        'default' => __('Right','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control(new VW_Painter_Image_Radio_Control($wp_customize, 'vw_painter_slider_content_option', array(
        'type' => 'select',
        'label' => __('Slider Content Layouts','vw-painter'),
        'section' => 'vw_painter_slidersettings',
        'choices' => array(
            'Left' => get_template_directory_uri().'/images/slider-content1.png',
            'Center' => get_template_directory_uri().'/images/slider-content2.png',
            'Right' => get_template_directory_uri().'/images/slider-content3.png',
    ))));

    //Slider excerpt
	$wp_customize->add_setting( 'vw_painter_slider_excerpt_number', array(
		'default'              => 30,
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_slider_excerpt_number', array(
		'label'       => esc_html__( 'Slider Excerpt length','vw-painter' ),
		'section'     => 'vw_painter_slidersettings',
		'type'        => 'range',
		'settings'    => 'vw_painter_slider_excerpt_number',
		'input_attrs' => array(
			'step'             => 2,
			'min'              => 0,
			'max'              => 50,
		),
	) );

	//Opacity
	$wp_customize->add_setting('vw_painter_slider_opacity_color',array(
      'default'              => 0.5,
      'sanitize_callback' => 'vw_painter_sanitize_choices'
	));

	$wp_customize->add_control( 'vw_painter_slider_opacity_color', array(
	'label'       => esc_html__( 'Slider Image Opacity','vw-painter' ),
	'section'     => 'vw_painter_slidersettings',
	'type'        => 'select',
	'settings'    => 'vw_painter_slider_opacity_color',
	'choices' => array(
      '0' =>  esc_attr('0','vw-painter'),
      '0.1' =>  esc_attr('0.1','vw-painter'),
      '0.2' =>  esc_attr('0.2','vw-painter'),
      '0.3' =>  esc_attr('0.3','vw-painter'),
      '0.4' =>  esc_attr('0.4','vw-painter'),
      '0.5' =>  esc_attr('0.5','vw-painter'),
      '0.6' =>  esc_attr('0.6','vw-painter'),
      '0.7' =>  esc_attr('0.7','vw-painter'),
      '0.8' =>  esc_attr('0.8','vw-painter'),
      '0.9' =>  esc_attr('0.9','vw-painter')
	),
	));

	//Slider height
	$wp_customize->add_setting('vw_painter_slider_height',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_slider_height',array(
		'label'	=> __('Slider Height','vw-painter'),
		'description'	=> __('Specify the slider height (px).','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '500px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_slidersettings',
		'type'=> 'text'
	));

	//Services
	$wp_customize->add_section( 'vw_painter_service_section' , array(
    	'title'      => __( 'Our Services', 'vw-painter' ),
		'priority'   => null,
		'panel' => 'vw_painter_panel_id'
	) );

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial( 'vw_painter_service_title', array( 
		'selector' => '#our_services h2', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_service_title',
	));

	$wp_customize->add_setting( 'vw_painter_service_title', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field'
	) );
	$wp_customize->add_control( 'vw_painter_service_title', array(
		'label'    => __( 'Section Title', 'vw-painter' ),		
		'section'  => 'vw_painter_service_section',
		'type'     => 'text'
	) );

	$wp_customize->add_setting( 'vw_painter_service_text', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field'
	) );
	$wp_customize->add_control( 'vw_painter_service_text', array(
		'label'    => __( 'Section Text', 'vw-painter' ),
		'section'  => 'vw_painter_service_section',
		'type'     => 'text'
	) );

	$categories = get_categories();
	$cat_post = array();
	$cat_post[]= 'select';
	$i = 0;	
	foreach($categories as $category){
		if($i==0){
			$default = $category->slug;
			$i++;
		}
		$cat_post[$category->slug] = $category->name;
	}

	$wp_customize->add_setting('vw_painter_services',array(
		'default'	=> 'select',
		'sanitize_callback' => 'vw_painter_sanitize_choices',
	));
	$wp_customize->add_control('vw_painter_services',array(
		'type'    => 'select',
		'choices' => $cat_post,		
		'label' => __('Select Category to display services','vw-painter'),
		'description' => __('Services Icon size (75 x 65)','vw-painter'),
		'section' => 'vw_painter_service_section',
	));

	//Services excerpt
	$wp_customize->add_setting( 'vw_painter_services_excerpt_number', array(
		'default'              => 30,
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_services_excerpt_number', array(
		'label'       => esc_html__( 'Services Excerpt length','vw-painter' ),
		'section'     => 'vw_painter_service_section',
		'type'        => 'range',
		'settings'    => 'vw_painter_services_excerpt_number',
		'input_attrs' => array(
			'step'             => 2,
			'min'              => 0,
			'max'              => 50,
		),
	) );

	//Blog Post
	$wp_customize->add_panel( $VWPainterParentPanel );

	$BlogPostParentPanel = new VW_Painter_WP_Customize_Panel( $wp_customize, 'blog_post_parent_panel', array(
		'title' => __( 'Blog Post Settings', 'vw-painter' ),
		'panel' => 'vw_painter_panel_id',
	));

	$wp_customize->add_panel( $BlogPostParentPanel );

	// Add example section and controls to the middle (second) panel
	$wp_customize->add_section( 'vw_painter_post_settings', array(
		'title' => __( 'Post Settings', 'vw-painter' ),
		'panel' => 'blog_post_parent_panel',
	));	

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_toggle_postdate', array( 
		'selector' => '.post-main-box h2 a', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_toggle_postdate', 
	));

	$wp_customize->add_setting( 'vw_painter_toggle_postdate',array(
        'default' => 1,
        'transport' => 'refresh',
        'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_toggle_postdate',array(
        'label' => esc_html__( 'Post Date','vw-painter' ),
        'section' => 'vw_painter_post_settings'
    )));

    $wp_customize->add_setting( 'vw_painter_toggle_author',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_toggle_author',array(
		'label' => esc_html__( 'Author','vw-painter' ),
		'section' => 'vw_painter_post_settings'
    )));

    $wp_customize->add_setting( 'vw_painter_toggle_comments',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_toggle_comments',array(
		'label' => esc_html__( 'Comments','vw-painter' ),
		'section' => 'vw_painter_post_settings'
    )));

    $wp_customize->add_setting( 'vw_painter_toggle_tags',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
	));
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_toggle_tags', array(
		'label' => esc_html__( 'Tags','vw-painter' ),
		'section' => 'vw_painter_post_settings'
    )));

    $wp_customize->add_setting( 'vw_painter_excerpt_number', array(
		'default'              => 30,
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_excerpt_number', array(
		'label'       => esc_html__( 'Excerpt length','vw-painter' ),
		'section'     => 'vw_painter_post_settings',
		'type'        => 'range',
		'settings'    => 'vw_painter_excerpt_number',
		'input_attrs' => array(
			'step'             => 2,
			'min'              => 0,
			'max'              => 50,
		),
	) );

	//Blog layout
    $wp_customize->add_setting('vw_painter_blog_layout_option',array(
        'default' => __('Default','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
    ));
    $wp_customize->add_control(new VW_Painter_Image_Radio_Control($wp_customize, 'vw_painter_blog_layout_option', array(
        'type' => 'select',
        'label' => __('Blog Layouts','vw-painter'),
        'section' => 'vw_painter_post_settings',
        'choices' => array(
            'Default' => get_template_directory_uri().'/images/blog-layout1.png',
            'Center' => get_template_directory_uri().'/images/blog-layout2.png',
            'Left' => get_template_directory_uri().'/images/blog-layout3.png',
    ))));

    $wp_customize->add_setting('vw_painter_excerpt_settings',array(
        'default' => __('Excerpt','vw-painter'),
        'transport' => 'refresh',
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control('vw_painter_excerpt_settings',array(
        'type' => 'select',
        'label' => __('Post Content','vw-painter'),
        'section' => 'vw_painter_post_settings',
        'choices' => array(
        	'Content' => __('Content','vw-painter'),
            'Excerpt' => __('Excerpt','vw-painter'),
            'No Content' => __('No Content','vw-painter')
        ),
	) );

	$wp_customize->add_setting('vw_painter_excerpt_suffix',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_excerpt_suffix',array(
		'label'	=> __('Add Excerpt Suffix','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '[...]', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_post_settings',
		'type'=> 'text'
	));

    // Button Settings
	$wp_customize->add_section( 'vw_painter_button_settings', array(
		'title' => __( 'Button Settings', 'vw-painter' ),
		'panel' => 'blog_post_parent_panel',
	));

	$wp_customize->add_setting('vw_painter_button_padding_top_bottom',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_button_padding_top_bottom',array(
		'label'	=> __('Padding Top Bottom','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_button_settings',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_button_padding_left_right',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_button_padding_left_right',array(
		'label'	=> __('Padding Left Right','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_button_settings',
		'type'=> 'text'
	));

	$wp_customize->add_setting( 'vw_painter_button_border_radius', array(
		'default'              => '',
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_button_border_radius', array(
		'label'       => esc_html__( 'Button Border Radius','vw-painter' ),
		'section'     => 'vw_painter_button_settings',
		'type'        => 'range',
		'input_attrs' => array(
			'step'             => 1,
			'min'              => 1,
			'max'              => 50,
		),
	) );

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_blog_button_text', array( 
		'selector' => '.post-main-box .content-bttn a', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_blog_button_text', 
	));

    $wp_customize->add_setting('vw_painter_blog_button_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_blog_button_text',array(
		'label'	=> __('Add Button Text','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( 'READ MORE', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_button_settings',
		'type'=> 'text'
	));

	// Related Post Settings
	$wp_customize->add_section( 'vw_painter_related_posts_settings', array(
		'title' => __( 'Related Posts Settings', 'vw-painter' ),
		'panel' => 'blog_post_parent_panel',
	));

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_related_post_title', array( 
		'selector' => '.related-post h3', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_related_post_title', 
	));

    $wp_customize->add_setting( 'vw_painter_related_post',array(
		'default' => 1,
		'transport' => 'refresh',
		'sanitize_callback' => 'vw_painter_switch_sanitization'
    ) );
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_related_post',array(
		'label' => esc_html__( 'Related Post','vw-painter' ),
		'section' => 'vw_painter_related_posts_settings'
    )));

    $wp_customize->add_setting('vw_painter_related_post_title',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_related_post_title',array(
		'label'	=> __('Add Related Post Title','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( 'Related Post', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_related_posts_settings',
		'type'=> 'text'
	));

   	$wp_customize->add_setting('vw_painter_related_posts_count',array(
		'default'=> '3',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_related_posts_count',array(
		'label'	=> __('Add Related Post Count','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '3', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_related_posts_settings',
		'type'=> 'number'
	));

    //404 Page Setting
	$wp_customize->add_section('vw_painter_404_page',array(
		'title'	=> __('404 Page Settings','vw-painter'),
		'panel' => 'vw_painter_panel_id',
	));	

	$wp_customize->add_setting('vw_painter_404_page_title',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));

	$wp_customize->add_control('vw_painter_404_page_title',array(
		'label'	=> __('Add Title','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '404 Not Found', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_404_page',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_404_page_content',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));

	$wp_customize->add_control('vw_painter_404_page_content',array(
		'label'	=> __('Add Text','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( 'Looks like you have taken a wrong turn, Dont worry, it happens to the best of us.', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_404_page',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_404_page_button_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_404_page_button_text',array(
		'label'	=> __('Add Button Text','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( 'Return to the home page', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_404_page',
		'type'=> 'text'
	));

	//Responsive Media Settings
	$wp_customize->add_section('vw_painter_responsive_media',array(
		'title'	=> __('Responsive Media','vw-painter'),
		'panel' => 'vw_painter_panel_id',
	));

	$wp_customize->add_setting( 'vw_painter_resp_topbar_hide_show',array(
      'default' => 0,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_resp_topbar_hide_show',array(
      'label' => esc_html__( 'Show / Hide Topbar','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

    $wp_customize->add_setting( 'vw_painter_stickyheader_hide_show',array(
      'default' => 0,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_stickyheader_hide_show',array(
      'label' => esc_html__( 'Sticky Header','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

    $wp_customize->add_setting( 'vw_painter_resp_slider_hide_show',array(
      'default' => 0,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_resp_slider_hide_show',array(
      'label' => esc_html__( 'Show / Hide Slider','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

	$wp_customize->add_setting( 'vw_painter_metabox_hide_show',array(
      'default' => 1,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_metabox_hide_show',array(
      'label' => esc_html__( 'Show / Hide Metabox','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

    $wp_customize->add_setting( 'vw_painter_sidebar_hide_show',array(
      'default' => 1,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_sidebar_hide_show',array(
      'label' => esc_html__( 'Show / Hide Sidebar','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

    $wp_customize->add_setting( 'vw_painter_resp_scroll_top_hide_show',array(
      'default' => 1,
      'transport' => 'refresh',
      'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_resp_scroll_top_hide_show',array(
      'label' => esc_html__( 'Show / Hide Scroll To Top','vw-painter' ),
      'section' => 'vw_painter_responsive_media'
    )));

    $wp_customize->add_setting('vw_painter_res_open_menu_icon',array(
		'default'	=> 'fas fa-bars',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control(new VW_Painter_Fontawesome_Icon_Chooser(
        $wp_customize,'vw_painter_res_open_menu_icon',array(
		'label'	=> __('Add Open Menu Icon','vw-painter'),
		'transport' => 'refresh',
		'section'	=> 'vw_painter_responsive_media',
		'setting'	=> 'vw_painter_res_open_menu_icon',
		'type'		=> 'icon'
	)));

	$wp_customize->add_setting('vw_painter_res_close_menus_icon',array(
		'default'	=> 'fas fa-times',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control(new VW_Painter_Fontawesome_Icon_Chooser(
        $wp_customize,'vw_painter_res_close_menus_icon',array(
		'label'	=> __('Add Close Menu Icon','vw-painter'),
		'transport' => 'refresh',
		'section'	=> 'vw_painter_responsive_media',
		'setting'	=> 'vw_painter_res_close_menus_icon',
		'type'		=> 'icon'
	)));

	//Content Craetion
	$wp_customize->add_section( 'vw_painter_content_section' , array(
    	'title' => __( 'Customize Home Page', 'vw-painter' ),
		'priority' => null,
		'panel' => 'vw_painter_panel_id'
	) );

	$wp_customize->add_setting('vw_painter_content_creation_main_control', array(
		'sanitize_callback' => 'esc_html',
	) );

	$homepage= get_option( 'page_on_front' );

	$wp_customize->add_control(	new VW_Painter_Content_Creation( $wp_customize, 'vw_painter_content_creation_main_control', array(
		'options' => array(
			esc_html__( 'First select static page in homepage setting for front page.Below given edit button is to customize Home Page. Just click on the edit option, add whatever elements you want to include in the homepage, save the changes and you are good to go.','vw-painter' ),
		),
		'section' => 'vw_painter_content_section',
		'button_url'  => admin_url( 'post.php?post='.$homepage.'&action=edit'),
		'button_text' => esc_html__( 'Edit', 'vw-painter' ),
	) ) );

	//Footer Text
	$wp_customize->add_section('vw_painter_footer',array(
		'title'	=> __('Footer','vw-painter'),
		'description'=> __('This section will appear in the footer','vw-painter'),
		'panel' => 'vw_painter_panel_id',
	));	

	//Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_footer_text', array( 
		'selector' => '.copyright p', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_footer_text', 
	));
	
	$wp_customize->add_setting('vw_painter_footer_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control('vw_painter_footer_text',array(
		'label'	=> __('Copyright Text','vw-painter'),
		'section'=> 'vw_painter_footer',
		'setting'=> 'vw_painter_footer_text',
		'type'=> 'text'
	));	

	$wp_customize->add_setting('vw_painter_copyright_alingment',array(
        'default' => __('center','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control(new VW_Painter_Image_Radio_Control($wp_customize, 'vw_painter_copyright_alingment', array(
        'type' => 'select',
        'label' => __('Copyright Alignment','vw-painter'),
        'section' => 'vw_painter_footer',
        'settings' => 'vw_painter_copyright_alingment',
        'choices' => array(
            'left' => get_template_directory_uri().'/images/copyright1.png',
            'center' => get_template_directory_uri().'/images/copyright2.png',
            'right' => get_template_directory_uri().'/images/copyright3.png'
    ))));

    $wp_customize->add_setting('vw_painter_copyright_padding_top_bottom',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_copyright_padding_top_bottom',array(
		'label'	=> __('Copyright Padding Top Bottom','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_footer',
		'type'=> 'text'
	));

	$wp_customize->add_setting( 'vw_painter_hide_show_scroll',array(
    	'default' => 1,
      	'transport' => 'refresh',
      	'sanitize_callback' => 'vw_painter_switch_sanitization'
    ));  
    $wp_customize->add_control( new VW_Painter_Toggle_Switch_Custom_Control( $wp_customize, 'vw_painter_hide_show_scroll',array(
      	'label' => esc_html__( 'Show / Hide Scroll To Top','vw-painter' ),
      	'section' => 'vw_painter_footer'
    )));

    //Selective Refresh
	$wp_customize->selective_refresh->add_partial('vw_painter_scroll_to_top_icon', array( 
		'selector' => '.scrollup i', 
		'render_callback' => 'vw_painter_customize_partial_vw_painter_scroll_to_top_icon', 
	));

    $wp_customize->add_setting('vw_painter_scroll_to_top_icon',array(
		'default'	=> 'fas fa-long-arrow-alt-up',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control(new VW_Painter_Fontawesome_Icon_Chooser(
        $wp_customize,'vw_painter_scroll_to_top_icon',array(
		'label'	=> __('Add Scroll to Top Icon','vw-painter'),
		'transport' => 'refresh',
		'section'	=> 'vw_painter_footer',
		'setting'	=> 'vw_painter_scroll_to_top_icon',
		'type'		=> 'icon'
	)));

	$wp_customize->add_setting('vw_painter_scroll_to_top_font_size',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_scroll_to_top_font_size',array(
		'label'	=> __('Icon Font Size','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_footer',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_scroll_to_top_padding',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_scroll_to_top_padding',array(
		'label'	=> __('Icon Top Bottom Padding','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_footer',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_scroll_to_top_width',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_scroll_to_top_width',array(
		'label'	=> __('Icon Width','vw-painter'),
		'description'	=> __('Enter a value in pixels Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_footer',
		'type'=> 'text'
	));

	$wp_customize->add_setting('vw_painter_scroll_to_top_height',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('vw_painter_scroll_to_top_height',array(
		'label'	=> __('Icon Height','vw-painter'),
		'description'	=> __('Enter a value in pixels. Example:20px','vw-painter'),
		'input_attrs' => array(
            'placeholder' => __( '10px', 'vw-painter' ),
        ),
		'section'=> 'vw_painter_footer',
		'type'=> 'text'
	));

	$wp_customize->add_setting( 'vw_painter_scroll_to_top_border_radius', array(
		'default'              => '',
		'type'                 => 'theme_mod',
		'transport' 		   => 'refresh',
		'sanitize_callback'    => 'absint',
		'sanitize_js_callback' => 'absint',
	) );
	$wp_customize->add_control( 'vw_painter_scroll_to_top_border_radius', array(
		'label'       => esc_html__( 'Icon Border Radius','vw-painter' ),
		'section'     => 'vw_painter_footer',
		'type'        => 'range',
		'input_attrs' => array(
			'step'             => 1,
			'min'              => 1,
			'max'              => 50,
		),
	) );

	$wp_customize->add_setting('vw_painter_scroll_top_alignment',array(
        'default' => __('Right','vw-painter'),
        'sanitize_callback' => 'vw_painter_sanitize_choices'
	));
	$wp_customize->add_control(new VW_Painter_Image_Radio_Control($wp_customize, 'vw_painter_scroll_top_alignment', array(
        'type' => 'select',
        'label' => __('Scroll To Top','vw-painter'),
        'section' => 'vw_painter_footer',
        'settings' => 'vw_painter_scroll_top_alignment',
        'choices' => array(
            'Left' => get_template_directory_uri().'/images/layout1.png',
            'Center' => get_template_directory_uri().'/images/layout2.png',
            'Right' => get_template_directory_uri().'/images/layout3.png'
    ))));

    // Has to be at the top
	$wp_customize->register_panel_type( 'VW_Painter_WP_Customize_Panel' );
	$wp_customize->register_section_type( 'VW_Painter_WP_Customize_Section' );
}

add_action( 'customize_register', 'vw_painter_customize_register' );

load_template( trailingslashit( get_template_directory() ) . '/inc/logo/logo-resizer.php' );

if ( class_exists( 'WP_Customize_Panel' ) ) {
  	class VW_Painter_WP_Customize_Panel extends WP_Customize_Panel {
	    public $panel;
	    public $type = 'vw_painter_panel';
	    public function json() {

	      $array = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel', ) );
	      $array['title'] = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
	      $array['content'] = $this->get_content();
	      $array['active'] = $this->active();
	      $array['instanceNumber'] = $this->instance_number;
	      return $array;
    	}
  	}
}

if ( class_exists( 'WP_Customize_Section' ) ) {
  	class VW_Painter_WP_Customize_Section extends WP_Customize_Section {
	    public $section;
	    public $type = 'vw_painter_section';
	    public function json() {

	      $array = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'panel', 'type', 'description_hidden', 'section', ) );
	      $array['title'] = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
	      $array['content'] = $this->get_content();
	      $array['active'] = $this->active();
	      $array['instanceNumber'] = $this->instance_number;

	      if ( $this->panel ) {
	        $array['customizeAction'] = sprintf( 'Customizing &#9656; %s', esc_html( $this->manager->get_panel( $this->panel )->title ) );
	      } else {
	        $array['customizeAction'] = 'Customizing';
	      }
	      return $array;
    	}
 	}
}

// Enqueue our scripts and styles
function vw_painter_customize_controls_scripts() {
  wp_enqueue_script( 'customizer-controls', get_theme_file_uri( '/js/customizer-controls.js' ), array(), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'vw_painter_customize_controls_scripts' );

/**
 * Singleton class for handling the theme's customizer integration.
 *
 * @since  1.0.0
 * @access public
 */
final class VW_Painter_Customize {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register panels, sections, settings, controls, and partials.
		add_action( 'customize_register', array( $this, 'sections' ) );

		// Register scripts and styles for the controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
	}

	/**
	 * Sets up the customizer sections.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @return void
	 */
	public function sections( $manager ) {

		// Load custom sections.
		load_template( trailingslashit( get_template_directory() ) . '/inc/section-pro.php' );

		// Register custom section types.
		$manager->register_section_type( 'VW_Painter_Customize_Section_Pro' );

		// Register sections.
		$manager->add_section(new VW_Painter_Customize_Section_Pro($manager,'example_1',array(
			'priority'   => 1,
			'title'    => esc_html__( 'Painter Pro Theme', 'vw-painter' ),
			'pro_text' => esc_html__( 'UPGRADE PRO', 'vw-painter' ),
			'pro_url'  => esc_url('https://www.vwthemes.com/themes/painter-wordpress-theme/'),
		)));

		// Register sections.
		$manager->add_section(new VW_Painter_Customize_Section_Pro($manager,'example_2',array(
			'priority'   => 1,
			'title'    => esc_html__( 'DOCUMENTATION', 'vw-painter' ),
			'pro_text' => esc_html__( 'DOCS', 'vw-painter' ),
			'pro_url'  => admin_url( 'themes.php?page=vw_painter_guide' )
		)));
	}

	/**
	 * Loads theme customizer CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_control_scripts() {

		wp_enqueue_script( 'vw-painter-customize-controls', trailingslashit( get_template_directory_uri() ) . '/js/customize-controls.js', array( 'customize-controls' ) );

		wp_enqueue_style( 'vw-painter-customize-controls', trailingslashit( get_template_directory_uri() ) . '/css/customize-controls.css' );
	}
}

// Doing this customizer thang!
VW_Painter_Customize::get_instance();
<?php
	
	/*---------------------------First highlight color-------------------*/

	$vw_painter_first_color = get_theme_mod('vw_painter_first_color');

	$vw_painter_custom_css = '';

	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='.cat-btn a:hover, #footer input[type="submit"], #sidebar .tagcloud a:hover, #footer .tagcloud a:hover, .scrollup i, input[type="submit"], #comments a.comment-reply-link, #sidebar .widget_price_filter .ui-slider .ui-slider-range, #sidebar .widget_price_filter .ui-slider .ui-slider-handle, #sidebar .woocommerce-product-search button, #footer .widget_price_filter .ui-slider .ui-slider-range, #footer .widget_price_filter .ui-slider .ui-slider-handle, #footer .woocommerce-product-search button, #footer #respond input#submit, #footer a.button, #footer button.button, #footer input.button, #footer #respond input#submit.alt, #footer a.button.alt, #footer button.button.alt, #footer input.button.alt, #footer a.custom_read_more:hover{';
			$vw_painter_custom_css .='background-color: '.esc_html($vw_painter_first_color).';';
		$vw_painter_custom_css .='}';
	}
	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='#comments input[type="submit"].submit{';
			$vw_painter_custom_css .='background-color: '.esc_html($vw_painter_first_color).'!important;';
		$vw_painter_custom_css .='}';
	}
	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='a, .post-navigation a:hover .post-title, .post-navigation a:focus .post-title, .woocommerce-message::before, #footer li a:hover, .main-navigation a:hover, .main-navigation ul.sub-menu a:hover, .entry-content a, #sidebar .textwidget p a, .textwidget p a, #comments p a, .slider .inner_carousel p a, #footer a.custom_read_more{';
			$vw_painter_custom_css .='color: '.esc_html($vw_painter_first_color).';';
		$vw_painter_custom_css .='}';
	}
	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='.cat-btn a:hover, #footer a.custom_read_more{';
			$vw_painter_custom_css .='border-color: '.esc_html($vw_painter_first_color).'!important;';
		$vw_painter_custom_css .='}';
	}
	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='.post-info hr, .woocommerce-message, .main-navigation ul ul{';
			$vw_painter_custom_css .='border-top-color: '.esc_html($vw_painter_first_color).';';
		$vw_painter_custom_css .='}';
	}
	if($vw_painter_first_color != false){
		$vw_painter_custom_css .='.main-navigation ul ul{';
			$vw_painter_custom_css .='border-bottom-color: '.esc_html($vw_painter_first_color).';';
		$vw_painter_custom_css .='}';
	}

	/*---------------------------Second highlight color-------------------*/

	$vw_painter_second_color = get_theme_mod('vw_painter_second_color');

	$vw_painter_third_color = get_theme_mod('vw_painter_third_color');

	$vw_painter_fourth_color = get_theme_mod('vw_painter_fourth_color');

	if($vw_painter_second_color != false || $vw_painter_first_color != false || $vw_painter_third_color != false || $vw_painter_fourth_color != false){
		$vw_painter_custom_css .='#topbar, #footer-2, .home-page-header{
		background-image: linear-gradient(to right, '.esc_html($vw_painter_second_color).', '.esc_html($vw_painter_first_color).', '.esc_html($vw_painter_third_color).', '.esc_html($vw_painter_fourth_color).');
		}';
	}

	if($vw_painter_second_color != false || $vw_painter_first_color != false || $vw_painter_third_color != false || $vw_painter_fourth_color != false){
		$vw_painter_custom_css .='#slider .carousel-caption{
		border-image: linear-gradient(to bottom, '.esc_html($vw_painter_second_color).', '.esc_html($vw_painter_first_color).', '.esc_html($vw_painter_third_color).', '.esc_html($vw_painter_fourth_color).')1 100%;
		}';
	}
	
	/*---------------------------Width Layout -------------------*/

	$vw_painter_theme_lay = get_theme_mod( 'vw_painter_width_option','Full Width');
    if($vw_painter_theme_lay == 'Boxed'){
		$vw_painter_custom_css .='body{';
			$vw_painter_custom_css .='max-width: 1140px; width: 100%; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto;';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Wide Width'){
		$vw_painter_custom_css .='body{';
			$vw_painter_custom_css .='width: 100%;padding-right: 15px;padding-left: 15px;margin-right: auto;margin-left: auto;';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Full Width'){
		$vw_painter_custom_css .='body{';
			$vw_painter_custom_css .='max-width: 100%;';
		$vw_painter_custom_css .='}';
	}

	/*--------------------------- Slider Opacity -------------------*/

	$vw_painter_theme_lay = get_theme_mod( 'vw_painter_slider_opacity_color','0.5');
	if($vw_painter_theme_lay == '0'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.1'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.1';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.2'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.2';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.3'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.3';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.4'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.4';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.5'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.5';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.6'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.6';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.7'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.7';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.8'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.8';
		$vw_painter_custom_css .='}';
		}else if($vw_painter_theme_lay == '0.9'){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='opacity:0.9';
		$vw_painter_custom_css .='}';
		}

	/*---------------------------Slider Content Layout -------------------*/

	$vw_painter_theme_lay = get_theme_mod( 'vw_painter_slider_content_option','Right');
    if($vw_painter_theme_lay == 'Left'){
		$vw_painter_custom_css .='#slider .carousel-caption, #slider .inner_carousel, #slider .inner_carousel h1{';
			$vw_painter_custom_css .='text-align:left; left:15%; right:45%;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='#slider .carousel-caption{';
			$vw_painter_custom_css .='padding-left: 15px; border-left: solid 12px; border-right: none;';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Center'){
		$vw_painter_custom_css .='#slider .carousel-caption, #slider .inner_carousel, #slider .inner_carousel h1{';
			$vw_painter_custom_css .='text-align:center; left:20%; right:20%;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='#slider .carousel-caption{';
			$vw_painter_custom_css .='border-left: none; border-right: none;';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Right'){
		$vw_painter_custom_css .='#slider .carousel-caption, #slider .inner_carousel, #slider .inner_carousel h1{';
			$vw_painter_custom_css .='text-align:right; left:45%; right:15%;';
		$vw_painter_custom_css .='}';
	}

	/*---------------------------Slider Height ------------*/

	$vw_painter_slider_height = get_theme_mod('vw_painter_slider_height');
	if($vw_painter_slider_height != false){
		$vw_painter_custom_css .='#slider img{';
			$vw_painter_custom_css .='height: '.esc_html($vw_painter_slider_height).';';
		$vw_painter_custom_css .='}';
	}

	/*--------------------------- Slider -------------------*/

	$vw_painter_slider = get_theme_mod('vw_painter_slider_hide_show');
	if($vw_painter_slider == false){
		$vw_painter_custom_css .='.page-template-custom-home-page #top-header{';
			$vw_painter_custom_css .='position: static; margin-top: 0em;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='.page-template-custom-home-page .home-page-header{';
			$vw_painter_custom_css .='padding: 15px 0;';
		$vw_painter_custom_css .='}';
	}

	/*---------------------------Blog Layout -------------------*/

	$vw_painter_theme_lay = get_theme_mod( 'vw_painter_blog_layout_option','Default');
    if($vw_painter_theme_lay == 'Default'){
		$vw_painter_custom_css .='.post-main-box{';
			$vw_painter_custom_css .='';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Center'){
		$vw_painter_custom_css .='.post-main-box, .post-main-box h2, .post-info, .new-text p, .content-bttn, #our-services p{';
			$vw_painter_custom_css .='text-align:center;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='.post-info{';
			$vw_painter_custom_css .='margin-top:10px;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='.post-info hr{';
			$vw_painter_custom_css .='margin:15px auto;';
		$vw_painter_custom_css .='}';
	}else if($vw_painter_theme_lay == 'Left'){
		$vw_painter_custom_css .='.post-main-box, .post-main-box h2, .post-info, .new-text p, .content-bttn, #our-services p{';
			$vw_painter_custom_css .='text-align:Left;';
		$vw_painter_custom_css .='}';
		$vw_painter_custom_css .='.post-main-box h2{';
			$vw_painter_custom_css .='margin-top:10px;';
		$vw_painter_custom_css .='}';
	}

	/*------------------------------Responsive Media -----------------------*/

	$vw_painter_resp_topbar = get_theme_mod( 'vw_painter_resp_topbar_hide_show',false);
    if($vw_painter_resp_topbar == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#topbar{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_resp_topbar == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#topbar{';
			$vw_painter_custom_css .='display:none;';
		$vw_painter_custom_css .='} }';
	}

	$vw_painter_resp_stickyheader = get_theme_mod( 'vw_painter_stickyheader_hide_show',false);
    if($vw_painter_resp_stickyheader == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.header-fixed{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_resp_stickyheader == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.header-fixed{';
			$vw_painter_custom_css .='display:none;';
		$vw_painter_custom_css .='} }';
	}

	$vw_painter_resp_slider = get_theme_mod( 'vw_painter_resp_slider_hide_show',false);
    if($vw_painter_resp_slider == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#slider{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_resp_slider == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#slider{';
			$vw_painter_custom_css .='display:none;';
		$vw_painter_custom_css .='} }';
	}

	$vw_painter_metabox = get_theme_mod( 'vw_painter_metabox_hide_show',true);
    if($vw_painter_metabox == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.post-info{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_metabox == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.post-info{';
			$vw_painter_custom_css .='display:none;';
		$vw_painter_custom_css .='} }';
	}

	$vw_painter_sidebar = get_theme_mod( 'vw_painter_sidebar_hide_show',true);
    if($vw_painter_sidebar == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#sidebar{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_sidebar == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='#sidebar{';
			$vw_painter_custom_css .='display:none;';
		$vw_painter_custom_css .='} }';
	}

	$vw_painter_resp_scroll_top = get_theme_mod( 'vw_painter_resp_scroll_top_hide_show',true);
    if($vw_painter_resp_scroll_top == true){
    	$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='display:block;';
		$vw_painter_custom_css .='} }';
	}else if($vw_painter_resp_scroll_top == false){
		$vw_painter_custom_css .='@media screen and (max-width:575px) {';
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='display:none !important;';
		$vw_painter_custom_css .='} }';
	}

	/*------------- Top Bar Settings ------------------*/

	$vw_painter_topbar_padding_top_bottom = get_theme_mod('vw_painter_topbar_padding_top_bottom');
	if($vw_painter_topbar_padding_top_bottom != false){
		$vw_painter_custom_css .='#topbar{';
			$vw_painter_custom_css .='padding-top: '.esc_html($vw_painter_topbar_padding_top_bottom).'; padding-bottom: '.esc_html($vw_painter_topbar_padding_top_bottom).';';
		$vw_painter_custom_css .='}';
	}

	/*------------------ Search Settings -----------------*/
	
	$vw_painter_search_padding_top_bottom = get_theme_mod('vw_painter_search_padding_top_bottom');
	$vw_painter_search_padding_left_right = get_theme_mod('vw_painter_search_padding_left_right');
	$vw_painter_search_font_size = get_theme_mod('vw_painter_search_font_size');
	$vw_painter_search_border_radius = get_theme_mod('vw_painter_search_border_radius');
	if($vw_painter_search_padding_top_bottom != false || $vw_painter_search_padding_left_right != false || $vw_painter_search_font_size != false || $vw_painter_search_border_radius != false){
		$vw_painter_custom_css .='.search-box i{';
			$vw_painter_custom_css .='padding-top: '.esc_html($vw_painter_search_padding_top_bottom).'; padding-bottom: '.esc_html($vw_painter_search_padding_top_bottom).';padding-left: '.esc_html($vw_painter_search_padding_left_right).';padding-right: '.esc_html($vw_painter_search_padding_left_right).';font-size: '.esc_html($vw_painter_search_font_size).';border-radius: '.esc_html($vw_painter_search_border_radius).'px;';
		$vw_painter_custom_css .='}';
	}

	/*---------------- Button Settings ------------------*/

	$vw_painter_button_padding_top_bottom = get_theme_mod('vw_painter_button_padding_top_bottom');
	$vw_painter_button_padding_left_right = get_theme_mod('vw_painter_button_padding_left_right');
	if($vw_painter_button_padding_top_bottom != false || $vw_painter_button_padding_left_right != false){
		$vw_painter_custom_css .='.post-main-box .content-bttn a{';
			$vw_painter_custom_css .='padding-top: '.esc_html($vw_painter_button_padding_top_bottom).'; padding-bottom: '.esc_html($vw_painter_button_padding_top_bottom).';padding-left: '.esc_html($vw_painter_button_padding_left_right).';padding-right: '.esc_html($vw_painter_button_padding_left_right).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_button_border_radius = get_theme_mod('vw_painter_button_border_radius');
	if($vw_painter_button_border_radius != false){
		$vw_painter_custom_css .='.post-main-box .content-bttn a{';
			$vw_painter_custom_css .='border-radius: '.esc_html($vw_painter_button_border_radius).'px;';
		$vw_painter_custom_css .='}';
	}

	/*-------------- Copyright Alignment ----------------*/

	$vw_painter_copyright_alingment = get_theme_mod('vw_painter_copyright_alingment');
	if($vw_painter_copyright_alingment != false){
		$vw_painter_custom_css .='.copyright p{';
			$vw_painter_custom_css .='text-align: '.esc_html($vw_painter_copyright_alingment).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_copyright_padding_top_bottom = get_theme_mod('vw_painter_copyright_padding_top_bottom');
	if($vw_painter_copyright_padding_top_bottom != false){
		$vw_painter_custom_css .='#footer-2{';
			$vw_painter_custom_css .='padding-top: '.esc_html($vw_painter_copyright_padding_top_bottom).'; padding-bottom: '.esc_html($vw_painter_copyright_padding_top_bottom).';';
		$vw_painter_custom_css .='}';
	}

	/*----------------Sroll to top Settings ------------------*/

	$vw_painter_scroll_to_top_font_size = get_theme_mod('vw_painter_scroll_to_top_font_size');
	if($vw_painter_scroll_to_top_font_size != false){
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='font-size: '.esc_html($vw_painter_scroll_to_top_font_size).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_scroll_to_top_padding = get_theme_mod('vw_painter_scroll_to_top_padding');
	$vw_painter_scroll_to_top_padding = get_theme_mod('vw_painter_scroll_to_top_padding');
	if($vw_painter_scroll_to_top_padding != false){
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='padding-top: '.esc_html($vw_painter_scroll_to_top_padding).';padding-bottom: '.esc_html($vw_painter_scroll_to_top_padding).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_scroll_to_top_width = get_theme_mod('vw_painter_scroll_to_top_width');
	if($vw_painter_scroll_to_top_width != false){
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='width: '.esc_html($vw_painter_scroll_to_top_width).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_scroll_to_top_height = get_theme_mod('vw_painter_scroll_to_top_height');
	if($vw_painter_scroll_to_top_height != false){
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='height: '.esc_html($vw_painter_scroll_to_top_height).';';
		$vw_painter_custom_css .='}';
	}

	$vw_painter_scroll_to_top_border_radius = get_theme_mod('vw_painter_scroll_to_top_border_radius');
	if($vw_painter_scroll_to_top_border_radius != false){
		$vw_painter_custom_css .='.scrollup i{';
			$vw_painter_custom_css .='border-radius: '.esc_html($vw_painter_scroll_to_top_border_radius).'px;';
		$vw_painter_custom_css .='}';
	}

	
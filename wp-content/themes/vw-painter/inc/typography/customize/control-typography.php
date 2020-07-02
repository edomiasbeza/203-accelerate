<?php
/**
 * Typography control class.
 *
 * @since  1.0.0
 * @access public
 */

class VW_Painter_Control_Typography extends WP_Customize_Control {

	/**
	 * The type of customize control being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'typography';

	/**
	 * Array 
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $l10n = array();

	/**
	 * Set up our control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @param  string  $id
	 * @param  array   $args
	 * @return void
	 */
	public function __construct( $manager, $id, $args = array() ) {

		// Let the parent class do its thing.
		parent::__construct( $manager, $id, $args );

		// Make sure we have labels.
		$this->l10n = wp_parse_args(
			$this->l10n,
			array(
				'color'       => esc_html__( 'Font Color', 'vw-painter' ),
				'family'      => esc_html__( 'Font Family', 'vw-painter' ),
				'size'        => esc_html__( 'Font Size',   'vw-painter' ),
				'weight'      => esc_html__( 'Font Weight', 'vw-painter' ),
				'style'       => esc_html__( 'Font Style',  'vw-painter' ),
				'line_height' => esc_html__( 'Line Height', 'vw-painter' ),
				'letter_spacing' => esc_html__( 'Letter Spacing', 'vw-painter' ),
			)
		);
	}

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_script( 'vw-painter-ctypo-customize-controls' );
		wp_enqueue_style(  'vw-painter-ctypo-customize-controls' );
	}

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function to_json() {
		parent::to_json();

		// Loop through each of the settings and set up the data for it.
		foreach ( $this->settings as $setting_key => $setting_id ) {

			$this->json[ $setting_key ] = array(
				'link'  => $this->get_link( $setting_key ),
				'value' => $this->value( $setting_key ),
				'label' => isset( $this->l10n[ $setting_key ] ) ? $this->l10n[ $setting_key ] : ''
			);

			if ( 'family' === $setting_key )
				$this->json[ $setting_key ]['choices'] = $this->get_font_families();

			elseif ( 'weight' === $setting_key )
				$this->json[ $setting_key ]['choices'] = $this->get_font_weight_choices();

			elseif ( 'style' === $setting_key )
				$this->json[ $setting_key ]['choices'] = $this->get_font_style_choices();
		}
	}

	/**
	 * Underscore JS template to handle the control's output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function content_template() { ?>

		<# if ( data.label ) { #>
			<span class="customize-control-title">{{ data.label }}</span>
		<# } #>

		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>

		<ul>

		<# if ( data.family && data.family.choices ) { #>

			<li class="typography-font-family">

				<# if ( data.family.label ) { #>
					<span class="customize-control-title">{{ data.family.label }}</span>
				<# } #>

				<select {{{ data.family.link }}}>

					<# _.each( data.family.choices, function( label, choice ) { #>
						<option value="{{ choice }}" <# if ( choice === data.family.value ) { #> selected="selected" <# } #>>{{ label }}</option>
					<# } ) #>

				</select>
			</li>
		<# } #>

		<# if ( data.weight && data.weight.choices ) { #>

			<li class="typography-font-weight">

				<# if ( data.weight.label ) { #>
					<span class="customize-control-title">{{ data.weight.label }}</span>
				<# } #>

				<select {{{ data.weight.link }}}>

					<# _.each( data.weight.choices, function( label, choice ) { #>

						<option value="{{ choice }}" <# if ( choice === data.weight.value ) { #> selected="selected" <# } #>>{{ label }}</option>

					<# } ) #>

				</select>
			</li>
		<# } #>

		<# if ( data.style && data.style.choices ) { #>

			<li class="typography-font-style">

				<# if ( data.style.label ) { #>
					<span class="customize-control-title">{{ data.style.label }}</span>
				<# } #>

				<select {{{ data.style.link }}}>

					<# _.each( data.style.choices, function( label, choice ) { #>

						<option value="{{ choice }}" <# if ( choice === data.style.value ) { #> selected="selected" <# } #>>{{ label }}</option>

					<# } ) #>

				</select>
			</li>
		<# } #>

		<# if ( data.size ) { #>

			<li class="typography-font-size">

				<# if ( data.size.label ) { #>
					<span class="customize-control-title">{{ data.size.label }} (px)</span>
				<# } #>

				<input type="number" min="1" {{{ data.size.link }}} value="{{ data.size.value }}" />

			</li>
		<# } #>

		<# if ( data.line_height ) { #>

			<li class="typography-line-height">

				<# if ( data.line_height.label ) { #>
					<span class="customize-control-title">{{ data.line_height.label }} (px)</span>
				<# } #>

				<input type="number" min="1" {{{ data.line_height.link }}} value="{{ data.line_height.value }}" />

			</li>
		<# } #>

		<# if ( data.letter_spacing ) { #>

			<li class="typography-letter-spacing">

				<# if ( data.letter_spacing.label ) { #>
					<span class="customize-control-title">{{ data.letter_spacing.label }} (px)</span>
				<# } #>

				<input type="number" min="1" {{{ data.letter_spacing.link }}} value="{{ data.letter_spacing.value }}" />

			</li>
		<# } #>

		</ul>
	<?php }

	/**
	 * Returns the available fonts.  Fonts should have available weights, styles, and subsets.
	 *
	 * @todo Integrate with Google fonts.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_fonts() { return array(); }

	/**
	 * Returns the available font families.
	 *
	 * @todo Pull families from `get_fonts()`.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	function get_font_families() {

		return array(
			'' => __( 'No Fonts', 'vw-painter' ),
        'Abril Fatface' => __( 'Abril Fatface', 'vw-painter' ),
        'Acme' => __( 'Acme', 'vw-painter' ),
        'Anton' => __( 'Anton', 'vw-painter' ),
        'Architects Daughter' => __( 'Architects Daughter', 'vw-painter' ),
        'Arimo' => __( 'Arimo', 'vw-painter' ),
        'Arsenal' => __( 'Arsenal', 'vw-painter' ),
        'Arvo' => __( 'Arvo', 'vw-painter' ),
        'Alegreya' => __( 'Alegreya', 'vw-painter' ),
        'Alfa Slab One' => __( 'Alfa Slab One', 'vw-painter' ),
        'Averia Serif Libre' => __( 'Averia Serif Libre', 'vw-painter' ),
        'Bangers' => __( 'Bangers', 'vw-painter' ),
        'Boogaloo' => __( 'Boogaloo', 'vw-painter' ),
        'Bad Script' => __( 'Bad Script', 'vw-painter' ),
        'Bitter' => __( 'Bitter', 'vw-painter' ),
        'Bree Serif' => __( 'Bree Serif', 'vw-painter' ),
        'BenchNine' => __( 'BenchNine', 'vw-painter' ),
        'Cabin' => __( 'Cabin', 'vw-painter' ),
        'Cardo' => __( 'Cardo', 'vw-painter' ),
        'Courgette' => __( 'Courgette', 'vw-painter' ),
        'Cherry Swash' => __( 'Cherry Swash', 'vw-painter' ),
        'Cormorant Garamond' => __( 'Cormorant Garamond', 'vw-painter' ),
        'Crimson Text' => __( 'Crimson Text', 'vw-painter' ),
        'Cuprum' => __( 'Cuprum', 'vw-painter' ),
        'Cookie' => __( 'Cookie', 'vw-painter' ),
        'Chewy' => __( 'Chewy', 'vw-painter' ),
        'Days One' => __( 'Days One', 'vw-painter' ),
        'Dosis' => __( 'Dosis', 'vw-painter' ),
        'Droid Sans' => __( 'Droid Sans', 'vw-painter' ),
        'Economica' => __( 'Economica', 'vw-painter' ),
        'Fredoka One' => __( 'Fredoka One', 'vw-painter' ),
        'Fjalla One' => __( 'Fjalla One', 'vw-painter' ),
        'Francois One' => __( 'Francois One', 'vw-painter' ),
        'Frank Ruhl Libre' => __( 'Frank Ruhl Libre', 'vw-painter' ),
        'Gloria Hallelujah' => __( 'Gloria Hallelujah', 'vw-painter' ),
        'Great Vibes' => __( 'Great Vibes', 'vw-painter' ),
        'Handlee' => __( 'Handlee', 'vw-painter' ),
        'Hammersmith One' => __( 'Hammersmith One', 'vw-painter' ),
        'Inconsolata' => __( 'Inconsolata', 'vw-painter' ),
        'Indie Flower' => __( 'Indie Flower', 'vw-painter' ),
        'IM Fell English SC' => __( 'IM Fell English SC', 'vw-painter' ),
        'Julius Sans One' => __( 'Julius Sans One', 'vw-painter' ),
        'Josefin Slab' => __( 'Josefin Slab', 'vw-painter' ),
        'Josefin Sans' => __( 'Josefin Sans', 'vw-painter' ),
        'Kanit' => __( 'Kanit', 'vw-painter' ),
        'Lobster' => __( 'Lobster', 'vw-painter' ),
        'Lato' => __( 'Lato', 'vw-painter' ),
        'Lora' => __( 'Lora', 'vw-painter' ),
        'Libre Baskerville' => __( 'Libre Baskerville', 'vw-painter' ),
        'Lobster Two' => __( 'Lobster Two', 'vw-painter' ),
        'Merriweather' => __( 'Merriweather', 'vw-painter' ),
        'Monda' => __( 'Monda', 'vw-painter' ),
        'Montserrat' => __( 'Montserrat', 'vw-painter' ),
        'Muli' => __( 'Muli', 'vw-painter' ),
        'Marck Script' => __( 'Marck Script', 'vw-painter' ),
        'Noto Serif' => __( 'Noto Serif', 'vw-painter' ),
        'Open Sans' => __( 'Open Sans', 'vw-painter' ),
        'Overpass' => __( 'Overpass', 'vw-painter' ),
        'Overpass Mono' => __( 'Overpass Mono', 'vw-painter' ),
        'Oxygen' => __( 'Oxygen', 'vw-painter' ),
        'Orbitron' => __( 'Orbitron', 'vw-painter' ),
        'Patua One' => __( 'Patua One', 'vw-painter' ),
        'Pacifico' => __( 'Pacifico', 'vw-painter' ),
        'Padauk' => __( 'Padauk', 'vw-painter' ),
        'Playball' => __( 'Playball', 'vw-painter' ),
        'Playfair Display' => __( 'Playfair Display', 'vw-painter' ),
        'PT Sans' => __( 'PT Sans', 'vw-painter' ),
        'Philosopher' => __( 'Philosopher', 'vw-painter' ),
        'Permanent Marker' => __( 'Permanent Marker', 'vw-painter' ),
        'Poiret One' => __( 'Poiret One', 'vw-painter' ),
        'Quicksand' => __( 'Quicksand', 'vw-painter' ),
        'Quattrocento Sans' => __( 'Quattrocento Sans', 'vw-painter' ),
        'Raleway' => __( 'Raleway', 'vw-painter' ),
        'Rubik' => __( 'Rubik', 'vw-painter' ),
        'Rokkitt' => __( 'Rokkitt', 'vw-painter' ),
        'Russo One' => __( 'Russo One', 'vw-painter' ),
        'Righteous' => __( 'Righteous', 'vw-painter' ),
        'Slabo' => __( 'Slabo', 'vw-painter' ),
        'Source Sans Pro' => __( 'Source Sans Pro', 'vw-painter' ),
        'Shadows Into Light Two' => __( 'Shadows Into Light Two', 'vw-painter'),
        'Shadows Into Light' => __( 'Shadows Into Light', 'vw-painter' ),
        'Sacramento' => __( 'Sacramento', 'vw-painter' ),
        'Shrikhand' => __( 'Shrikhand', 'vw-painter' ),
        'Tangerine' => __( 'Tangerine', 'vw-painter' ),
        'Ubuntu' => __( 'Ubuntu', 'vw-painter' ),
        'VT323' => __( 'VT323', 'vw-painter' ),
        'Varela Round' => __( 'Varela Round', 'vw-painter' ),
        'Vampiro One' => __( 'Vampiro One', 'vw-painter' ),
        'Vollkorn' => __( 'Vollkorn', 'vw-painter' ),
        'Volkhov' => __( 'Volkhov', 'vw-painter' ),
        'Yanone Kaffeesatz' => __( 'Yanone Kaffeesatz', 'vw-painter' )
		);
	}

	/**
	 * Returns the available font weights.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_font_weight_choices() {

		return array(
			'' => esc_html__( 'No Fonts weight', 'vw-painter' ),
			'100' => esc_html__( 'Thin',       'vw-painter' ),
			'300' => esc_html__( 'Light',      'vw-painter' ),
			'400' => esc_html__( 'Normal',     'vw-painter' ),
			'500' => esc_html__( 'Medium',     'vw-painter' ),
			'700' => esc_html__( 'Bold',       'vw-painter' ),
			'900' => esc_html__( 'Ultra Bold', 'vw-painter' ),
		);
	}

	/**
	 * Returns the available font styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_font_style_choices() {

		return array(
			'normal'  => esc_html__( 'Normal', 'vw-painter' ),
			'italic'  => esc_html__( 'Italic', 'vw-painter' ),
			'oblique' => esc_html__( 'Oblique', 'vw-painter' )
		);
	}
}

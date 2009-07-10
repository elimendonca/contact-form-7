<?php
/**
** A base module for [captchac] and [captchar]
**/

function wpcf7_captcha_shortcode_handler( $tag ) {
	global $wpcf7_contact_form;

	if ( ! is_array( $tag ) )
		return '';

	$type = $tag['type'];
	$name = $tag['name'];
	$options = (array) $tag['options'];
	$values = (array) $tag['values'];

	$validation_error = '';
	if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) )
		$validation_error = $wpcf7_contact_form->validation_error( $name );

	$atts = '';

	$id_array = preg_grep( '%^id:[-0-9a-zA-Z_]+$%', $options );
	if ( $id = array_shift( $id_array ) ) {
		preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $id, $id_matches );
		if ( $id = $id_matches[1] )
			$atts .= ' id="' . $id . '"';
	}

	$class_att = "";
	$class_array = preg_grep( '%^class:[-0-9a-zA-Z_]+$%', $options );
	foreach ( $class_array as $class ) {
		preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $class, $class_matches );
		if ( $class = $class_matches[1] )
			$class_att .= ' ' . $class;
	}

	if ( 'captchac' == $type )
		$class_att .= ' wpcf7-captcha-' . $name;

	if ( $class_att )
		$atts .= ' class="' . trim( $class_att ) . '"';

	// Value.
	if ( is_object( $wpcf7_contact_form ) && $wpcf7_contact_form->is_posted() ) {
		if ( isset( $_POST['_wpcf7_mail_sent'] ) && $_POST['_wpcf7_mail_sent']['ok'] )
			$value = '';
		elseif ( 'captchar' == $type )
			$value = '';
		else
			$value = $_POST[$name];
	} else {
		$value = $values[0];
	}

	if ( 'captchac' == $type ) {
		if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
			return '<em>' . __( 'To use CAPTCHA, you need <a href="http://wordpress.org/extend/plugins/really-simple-captcha/">Really Simple CAPTCHA</a> plugin installed.', 'wpcf7' ) . '</em>';
		}

		$op = array();
		// Default
		$op['img_size'] = array( 72, 24 );
		$op['base'] = array( 6, 18 );
		$op['font_size'] = 14;
		$op['font_char_width'] = 15;

		$op = array_merge( $op, wpcf7_captchac_options( $options ) );

		if ( ! $filename = wpcf7_generate_captcha( $op ) )
			return '';

		if ( is_array( $op['img_size'] ) )
			$atts .= ' width="' . $op['img_size'][0] . '" height="' . $op['img_size'][1] . '"';

		$captcha_url = trailingslashit( wpcf7_captcha_tmp_url() ) . $filename;
		$html = '<img alt="captcha" src="' . $captcha_url . '"' . $atts . ' />';
		$ref = substr( $filename, 0, strrpos( $filename, '.' ) );
		$html = '<input type="hidden" name="_wpcf7_captcha_challenge_' . $name . '" value="' . $ref . '" />' . $html;

		return $html;

	} elseif ( 'captchar' == $type ) {
		$size_maxlength_array = preg_grep( '%^[0-9]*[/x][0-9]*$%', $options );
		if ( $size_maxlength = array_shift( $size_maxlength_array ) ) {
			preg_match( '%^([0-9]*)[/x]([0-9]*)$%', $size_maxlength, $sm_matches );
			if ( $size = (int) $sm_matches[1] )
				$atts .= ' size="' . $size . '"';
			else
				$atts .= ' size="40"';
			if ( $maxlength = (int) $sm_matches[2] )
				$atts .= ' maxlength="' . $maxlength . '"';
		} else {
			$atts .= ' size="40"';
		}

		$html = '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '"' . $atts . ' />';
		$html = '<span class="wpcf7-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

		return $html;
	}
}

wpcf7_add_shortcode( 'captchac', 'wpcf7_captcha_shortcode_handler', true );
wpcf7_add_shortcode( 'captchar', 'wpcf7_captcha_shortcode_handler', true );

?>
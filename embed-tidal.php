<?php
/**
 * Plugin Name: Embed Tidal
 * Version: 0.1.1
 * Description: Embed the Tidal web player via pasting a URL or using a shortcode. Works well with the Shortcake shortcode UI.
 * Author: Bjørn Johansen
 * Author URI: https://bjornjohansen.no
 * Text Domain: embed-tidal
 * Domain Path: /languages
 * License: GPL v2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'embed-tidal', false, basename( dirname( __FILE__ ) ) . '/languages' );
} );


/**
 * Register the tidal shortcode
 *
 * @since 0.0.1
 */
function embed_tidal_register_shortcode() {
	add_shortcode( 'tidal', 'embed_tidal_shortcode' );
}
add_action( 'init', 'embed_tidal_register_shortcode' );


/**
 * Shortcode UI setup for the tidal shortcode
 *
 * @since 0.0.1
 */
function embed_tidal_shortcode_ui() {
	/*
	 * Define the UI for attributes of the shortcode.
	 */
	$fields = array(
		array(
			/* translators: Type of embed. I.e. album, playlist, track or video */
			'label'   => esc_html_x( 'Type', 'noun', 'embed-tidal' ),
			'attr'    => 'type',
			'type'    => 'select',
			'options' => array(
				'a' => esc_html__( 'Album', 'embed-tidal' ),
				'p' => esc_html__( 'Playlist', 'embed-tidal' ),
				't' => esc_html_x( 'Track', 'embed type', 'embed-tidal' ),
				'v' => esc_html__( 'Video', 'embed-tidal' ),
			),
		),
		array(
			'label' => esc_html__( 'Tidal ID', 'embed-tidal' ),
			'attr'  => 'id',
			'type'  => 'text',
		),
		array(
			'label' => esc_html__( 'Tidal ID for related album', 'embed-tidal' ),
			'attr'  => 'related-id',
			'type'  => 'text',
		),
	);

	/*
	 * Define the Shortcode UI arguments.
	 */
	$shortcode_ui_args = array(

		/*
		 * How the shortcode should be labeled in the UI.
		 */
		'label' => esc_html__( 'Embed from Tidal', 'embed-tidal' ),

		/*
		 * Include an icon with your shortcode.
		 * Use a dashicon, or full URL to image.
		 */
		'listItemImage' => 'dashicons-format-audio',

		/*
		 * Define the UI for attributes of the shortcode.
		 *
		 * See above, to where the the assignment to the $fields variable was made.
		 */
		'attrs' => $fields,
	);

	shortcode_ui_register_for_shortcode( 'tidal', $shortcode_ui_args );
}
add_action( 'register_shortcode_ui', 'embed_tidal_shortcode_ui' );


/**
 * Callback for the tidal shortcode.
 *
 * @since 0.0.1
 *
 * @param array  $attr Provided attributes.
 * @param string $content The shortcode content.
 * @param string $shortcode_tag The shortcode tag.
 * @return string The rendered HTML.
 */
function embed_tidal_shortcode( $attr, $content, $shortcode_tag ) {

	$attr = shortcode_atts( array(
		'type'       => 'a',
		'id'         => null,
		'related-id' => null,
	), $attr, $shortcode_tag );

	$html = '';

	if ( strlen( $attr['type'] ) || strlen( $attr['id'] ) ) {

		$related_attr = '';
		if ( 'v' === $attr['type'] && strlen( $attr['related-id'] ) ) {
			$related_attr = sprintf( ' data-related-id="%s"', esc_attr( $attr['related-id'] ) );
		}

		$html = sprintf( '<div class="tidal-embed" data-type="%s" data-id="%s"%s></div>', esc_attr( $attr['type'] ), esc_attr( $attr['id'] ), $related_attr );

		if ( is_admin() || is_feed() ) {
			$html .= '<script src="https://embed.tidal.com/tidal-embed.js"></script>';
		} else {
			wp_enqueue_script( 'embed-tidal', 'https://embed.tidal.com/tidal-embed.js', array(), null, true );
		}
	}

	/**
	 * Filters the tidal shortcode output.
	 *
	 * @since 0.0.1
	 *
	 * @param string $html Tidal shortcode HTML output.
	 * @param array  $attr Array of tidal shortcode attributes.
	 */
	return apply_filters( 'tidal_shortcode', $html, $attr );
}


/*
 * Include a script for working with the shortcode UI
 */
add_action( 'enqueue_shortcode_ui', function() {
	wp_enqueue_script( 'tidal-embed-shortcode-ui', plugin_dir_url( __FILE__ ) . 'tidal-embed-shortcode-ui.js' );
} );


/**
 * The custom embed handler for Tidal URLs
 *
 * @since 0.0.2
 *
 * @param array  $matches The RegEx matches from the provided regex when calling wp_embed_register_handler().
 * @param array  $attr Embed attributes.
 * @param string $url The original URL that was matched by the regex.
 * @param array  $rawattr The original unmodified attributes.
 * @return string The embed HTML (or the original URL if we can’t handle it after all).
 */
function embed_tidal_url_handler( $matches, $attr, $url, $rawattr ) {

	$tidal_id = $matches[3];

	switch ( $matches[2] ) {
		case 'track':
			$tidal_type = 't';
			break;

		case 'playlist':
			$tidal_type = 'p';
			break;

		case 'video':
			$tidal_type = 'v';
			break;

		case 'album':
			$tidal_type = 'a';
			break;

		default:
			return $url;
			break;
	}

	$embed = sprintf( '<div class="tidal-embed" data-type="%s" data-id="%s"></div>', esc_attr( $tidal_type ), esc_attr( $tidal_id ) );

	if ( is_admin() || is_feed() ) {
		$embed .= '<script src="https://embed.tidal.com/tidal-embed.js"></script>';
	} else {
		wp_enqueue_script( 'embed-tidal', 'https://embed.tidal.com/tidal-embed.js', array(), null, true );
	}

	/**
	 * Filters the Tidal URL embed output.
	 *
	 * @since 0.0.2
	 *
	 * @param string $embed   Embed HTML output.
	 * @param array  $matches The RegEx matches from the provided regex when calling wp_embed_register_handler().
	 * @param array  $attr Embed attributes.
	 * @param string $url The original URL that was matched by the regex.
	 * @param array  $rawattr The original unmodified attributes.
	 * @return string The embed HTML.
	 */
	return apply_filters( 'embed_tidal', $embed, $matches, $attr, $url, $rawattr );
}

/**
 * Register the custom embed handler for Tidal URLs
 *
 * @since 0.0.2
 */
function embed_tidal_wp_embed_register_handler() {
	wp_embed_register_handler( 'tidal', '/^https?:\/\/(listen\.)?tidal.com\/(playlist|track|album|video)\/(.*)$/', 'embed_tidal_url_handler' );
}
add_action( 'init', 'embed_tidal_wp_embed_register_handler' );


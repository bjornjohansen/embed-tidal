<?php
/**
 * Embed Tidal plugin.
 *
 * @package EmbedTidal
 */

/**
 * The Embed_Tidal class.
 */
class Embed_Tidal {
	/**
	 * Load the Tidal JavaScript only when a post needs it.
	 *
	 * @var bool
	 */
	public static $load_script = false;

	/**
	 * Key => value array of the different supported embed types.
	 *
	 * @var array
	 */
	public static $embed_type = [];

	/**
	 * Setup hooks. Register handlers etc.
	 */
	public static function hooks() {
		self::$embed_type = [
			'a' => __( 'Album', 'embed-tidal' ),
			'p' => __( 'Playlist', 'embed-tidal' ),
			't' => _x( 'Track', 'embed type', 'embed-tidal' ),
			'v' => __( 'Video', 'embed-tidal' ),
		];

		add_shortcode( 'tidal', [ __CLASS__, 'shortcode_handler' ] );
		wp_embed_register_handler( 'tidal', '/^https?:\/\/(listen\.)?tidal.com\/(playlist|track|album|video)\/(.*)$/', [ __CLASS__, 'embed_handler' ] );

		add_action( 'wp_footer', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );

		add_action(
			'plugins_loaded', function () {
				load_plugin_textdomain( 'embed-tidal', false, basename( dirname( __FILE__ ) ) . '/languages' );
			}
		);

		/*
		 * Include a script for working with the shortcode UI
		 */
		add_action(
			'enqueue_shortcode_ui', function() {
				wp_enqueue_script( 'tidal-embed-shortcode-ui', plugin_dir_url( __FILE__ ) . 'tidal-embed-shortcode-ui.js' );
			}
		);

		add_action( 'register_shortcode_ui', [ __CLASS__, 'embed_tidal_shortcode_ui' ] );
	}

	/**
	 * Builds the content
	 *
	 * @since 0.1.2
	 *
	 * @param array  $attr Provided attributes.
	 * @param string $content The shortcode content.
	 * @param string $shortcode_tag The shortcode tag.
	 * @return string The rendered HTML.
	 */
	public static function get_content( $attr, $content = null, $shortcode_tag = null ) {
		if ( is_admin() ) {
			return 'Tidal ' . esc_html( self::$embed_type[ $attr['type'] ] ) . ' Embed (#' . esc_html( $attr['id'] ) . ')';
		}

		$result = sprintf( '<div class="tidal-embed" data-type="%s" data-id="%s"', esc_attr( $attr['type'] ), esc_attr( $attr['id'] ) );
		if ( 'v' === $attr['type'] && isset( $attr['related_id'] ) && $attr['related_id'] ) {
			$result .= sprintf( ' data-related-id="%s"', esc_attr( $attr['related_id'] ) );
		}
		$result .= '></div>';

		return $result;
	}

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
	public static function shortcode_handler( $attr, $content, $shortcode_tag ) {
		self::$load_script = true;

		$attr = shortcode_atts(
			[
				'type'       => 'a',
				'id'         => null,
				'related_id' => null,
			], $attr, $shortcode_tag
		);

		/**
		 * Filters the tidal shortcode output.
		 *
		 * @since 0.0.1
		 *
		 * @param string $html Tidal shortcode HTML output.
		 * @param array  $attr Array of tidal shortcode attributes.
		 */
		return apply_filters( 'tidal_shortcode', self::get_content( $attr, $content, $shortcode_tag ), $attr );
	}


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
	public static function embed_handler( $matches, $attr, $url, $rawattr ) {
		self::$load_script = true;

		$attr = [ 'id' => $matches[3] ];

		switch ( $matches[2] ) {
			case 'track':
				$attr['type'] = 't';
				break;

			case 'playlist':
				$attr['type'] = 'p';
				break;

			case 'video':
				$attr['type'] = 'v';
				break;

			case 'album':
				$attr['type'] = 'a';
				break;

			default:
				return $url;
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
		return apply_filters( 'embed_tidal', self::get_content( $attr ), $matches, $attr, $url, $rawattr );
	}

	/**
	 * Enqueue the Tidal JavaScript
	 *
	 * @since 0.1.2
	 */
	public static function enqueue_scripts() {
		if ( ! self::$load_script ) {
			return;
		}

		wp_enqueue_script( 'embed-tidal', 'https://embed.tidal.com/tidal-embed.js', [], null, true );
	}

	/**
	 * Enqueue the Tidal JavaScript on admin pages
	 *
	 * @param string $hook The admin page hook.
	 */
	public static function admin_enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_script( 'embed-tidal', 'https://embed.tidal.com/tidal-embed.js' );
	}

	/**
	 * Shortcode UI setup for the tidal shortcode
	 *
	 * @since 0.0.1
	 */
	public static function embed_tidal_shortcode_ui() {
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
				'attr'  => 'related_id',
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
			'label'         => esc_html__( 'Embed from Tidal', 'embed-tidal' ),

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
			'attrs'         => $fields,
		);

		shortcode_ui_register_for_shortcode( 'tidal', $shortcode_ui_args );
	}
}

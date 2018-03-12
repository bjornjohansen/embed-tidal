<?php
/**
 * Embed Tidal plugin.
 *
 * @package EmbedTidal
 *
 * Plugin Name: Embed Tidal
 * Version: 0.2.0
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

// Require the class where the action happens.
require_once 'class-embed-tidal.php';

// Initialize.
add_action( 'init', [ 'Embed_Tidal', 'hooks' ] );

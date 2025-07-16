<?php
/**
 * URL Utility Class
 *
 * Provides utility functions for working with URLs in WordPress,
 * including validation, manipulation, and WordPress-specific operations.
 *
 * @package ArrayPress\URLUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\URLUtils;

/**
 * URL Class
 *
 * Core operations for working with URLs.
 */
class URL {

	/**
	 * Check if a URL is valid.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return bool True if the URL is valid.
	 */
	public static function is_valid( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Check if a URL is external (not from current site).
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if external.
	 */
	public static function is_external( string $url ): bool {
		if ( empty( $url ) || ! self::is_valid( $url ) ) {
			return false;
		}

		$site_host = parse_url( home_url(), PHP_URL_HOST );
		$url_host  = parse_url( $url, PHP_URL_HOST );

		return $url_host && $url_host !== $site_host;
	}

	/**
	 * Check if a URL is from the same domain as current site.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if same domain.
	 */
	public static function is_same_domain( string $url ): bool {
		return ! self::is_external( $url );
	}

	/**
	 * Check if a URL is HTTPS.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if HTTPS.
	 */
	public static function is_https( string $url ): bool {
		return parse_url( $url, PHP_URL_SCHEME ) === 'https';
	}

	/**
	 * Get the domain from a URL.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string The domain or empty string.
	 */
	public static function get_domain( string $url ): string {
		return parse_url( $url, PHP_URL_HOST ) ?: '';
	}

	/**
	 * Get the path from a URL.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string The path or empty string.
	 */
	public static function get_path( string $url ): string {
		return parse_url( $url, PHP_URL_PATH ) ?: '';
	}

	/**
	 * Get the query string from a URL.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string The query string or empty string.
	 */
	public static function get_query( string $url ): string {
		return parse_url( $url, PHP_URL_QUERY ) ?: '';
	}

	/**
	 * Get the file extension from a URL.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string The file extension or empty string.
	 */
	public static function get_extension( string $url ): string {
		$path = self::get_path( $url );

		return strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	}

	/**
	 * Add query parameters to a URL.
	 *
	 * @param string $url    The URL.
	 * @param array  $params Query parameters to add.
	 *
	 * @return string URL with added parameters.
	 */
	public static function add_params( string $url, array $params ): string {
		return add_query_arg( $params, $url );
	}

	/**
	 * Remove query parameters from a URL.
	 *
	 * @param string       $url    The URL.
	 * @param array|string $params Parameters to remove.
	 *
	 * @return string URL with parameters removed.
	 */
	public static function remove_params( string $url, $params ): string {
		return remove_query_arg( $params, $url );
	}

	/**
	 * Remove the scheme from a URL.
	 *
	 * @param string $url The URL.
	 *
	 * @return string URL without scheme.
	 */
	public static function remove_scheme( string $url ): string {
		return preg_replace( '/^https?:/', '', $url );
	}

	/**
	 * Convert URL to HTTPS.
	 *
	 * @param string $url The URL to convert.
	 *
	 * @return string HTTPS URL.
	 */
	public static function to_https( string $url ): string {
		return preg_replace( '/^http:/', 'https:', $url );
	}

	/**
	 * Convert URL to HTTP.
	 *
	 * @param string $url The URL to convert.
	 *
	 * @return string HTTP URL.
	 */
	public static function to_http( string $url ): string {
		return preg_replace( '/^https:/', 'http:', $url );
	}

	/**
	 * Make a URL relative (remove domain if same as current site).
	 *
	 * @param string $url The URL to make relative.
	 *
	 * @return string Relative URL or original if external.
	 */
	public static function make_relative( string $url ): string {
		if ( self::is_external( $url ) ) {
			return $url;
		}

		$home_url   = home_url();
		$home_parts = wp_parse_url( $home_url );
		$url_parts  = wp_parse_url( $url );

		if ( ! isset( $url_parts['path'] ) ) {
			return '/';
		}

		$relative_url = $url_parts['path'];

		// Remove home path if it exists
		if ( isset( $home_parts['path'] ) ) {
			$home_path = rtrim( $home_parts['path'], '/' );
			if ( strpos( $relative_url, $home_path ) === 0 ) {
				$relative_url = substr( $relative_url, strlen( $home_path ) );
			}
		}

		// Add query and fragment
		if ( isset( $url_parts['query'] ) ) {
			$relative_url .= '?' . $url_parts['query'];
		}
		if ( isset( $url_parts['fragment'] ) ) {
			$relative_url .= '#' . $url_parts['fragment'];
		}

		return '/' . ltrim( $relative_url, '/' );
	}

	/**
	 * Convert relative URL to absolute.
	 *
	 * @param string $url      The relative URL.
	 * @param string $base_url Base URL (defaults to home_url).
	 *
	 * @return string Absolute URL.
	 */
	public static function to_absolute( string $url, string $base_url = '' ): string {
		if ( self::is_valid( $url ) ) {
			return $url; // Already absolute
		}

		if ( empty( $base_url ) ) {
			$base_url = home_url();
		}

		return trailingslashit( $base_url ) . ltrim( $url, '/' );
	}

	/**
	 * Generate UTM tracking URL.
	 *
	 * @param string $url    Base URL.
	 * @param array  $params UTM parameters.
	 *
	 * @return string URL with UTM parameters.
	 */
	public static function add_utm( string $url, array $params = [] ): string {
		$defaults = [
			'source'   => '',
			'medium'   => '',
			'campaign' => '',
			'content'  => '',
			'term'     => ''
		];

		$utm_params = wp_parse_args( $params, $defaults );
		$query_args = [];

		foreach ( $utm_params as $key => $value ) {
			if ( ! empty( $value ) ) {
				$query_args["utm_{$key}"] = sanitize_text_field( $value );
			}
		}

		return self::add_params( $url, $query_args );
	}

	/**
	 * Generate smart UTM tracking URL with automatic medium detection.
	 *
	 * @param string $url        Base URL.
	 * @param array  $params     UTM parameters.
	 * @param bool   $escape_url Whether to escape the final URL.
	 *
	 * @return string URL with UTM parameters.
	 */
	public static function add_utm_smart( string $url, array $params = [], bool $escape_url = true ): string {
		$defaults = [
			'source'   => 'WordPress',
			'medium'   => '',
			'campaign' => 'default',
			'content'  => '',
			'term'     => ''
		];

		$utm_params = wp_parse_args( $params, $defaults );

		// Auto-detect medium if not provided
		if ( empty( $utm_params['medium'] ) ) {
			if ( is_admin() ) {
				$screen               = get_current_screen();
				$utm_params['medium'] = $screen ? $screen->id : 'admin';
			} else {
				$template = '';

				if ( is_home() ) {
					$template = get_home_template();
				} elseif ( is_front_page() ) {
					$template = get_front_page_template();
				} elseif ( is_search() ) {
					$template = get_search_template();
				} elseif ( is_single() ) {
					$template = get_single_template();
				} elseif ( is_page() ) {
					$template = get_page_template();
				} elseif ( is_post_type_archive() ) {
					$template = get_post_type_archive_template();
				} elseif ( is_archive() ) {
					$template = get_archive_template();
				}

				$utm_params['medium'] = $template ? wp_basename( $template, '.php' ) : 'frontend';
			}
		}

		// Clean up medium and content
		$utm_params['medium']  = str_replace( '_', '-', sanitize_title( $utm_params['medium'] ) );
		$utm_params['content'] = str_replace( '_', '-', sanitize_title( $utm_params['content'] ) );

		// Preserve anchor fragment
		$anchor = '';
		if ( strpos( $url, '#' ) !== false ) {
			$parts  = explode( '#', $url, 2 );
			$url    = $parts[0];
			$anchor = '#' . $parts[1];
		}

		// Build query args
		$query_args = [];
		foreach ( $utm_params as $key => $value ) {
			if ( ! empty( $value ) ) {
				$query_args["utm_{$key}"] = $value;
			}
		}

		$final_url = add_query_arg( $query_args, trailingslashit( $url ) ) . $anchor;

		return $escape_url ? esc_url( $final_url ) : $final_url;
	}

	/**
	 * Safe redirect to URL.
	 *
	 * @param string $url    URL to redirect to.
	 * @param int    $status HTTP status code.
	 */
	public static function redirect( string $url = '', int $status = 302 ): void {
		if ( empty( $url ) ) {
			$url = is_admin() ? admin_url() : home_url();
		}

		wp_safe_redirect( esc_url_raw( $url ), $status );
		exit;
	}

	/**
	 * Check if URL is an image.
	 *
	 * @param string $url        The URL to check.
	 * @param array  $extensions Custom extensions (optional).
	 *
	 * @return bool True if image URL.
	 */
	public static function is_image( string $url, array $extensions = [] ): bool {
		$default_extensions = [
			'jpg',
			'jpeg',
			'png',
			'gif',
			'webp',
			'svg',
			'bmp',
			'tiff',
			'tif',
			'ico',
			'psd',
			'ai',
			'eps',
			'raw',
			'cr2',
			'nef',
			'orf',
			'sr2',
			'avif',
			'heic',
			'heif',
			'jfif',
			'pjpeg',
			'pjp'
		];

		$check_extensions = empty( $extensions ) ? $default_extensions : $extensions;
		$extension        = self::get_extension( $url );

		return in_array( $extension, $check_extensions, true );
	}

	/**
	 * Check if URL is a video.
	 *
	 * @param string $url        The URL to check.
	 * @param array  $extensions Custom extensions (optional).
	 *
	 * @return bool True if video URL.
	 */
	public static function is_video( string $url, array $extensions = [] ): bool {
		$default_extensions = [
			'mp4',
			'avi',
			'mov',
			'wmv',
			'flv',
			'webm',
			'mkv',
			'm4v',
			'mpg',
			'mpeg',
			'mpe',
			'mp2',
			'3gp',
			'3g2',
			'f4v',
			'asf',
			'rm',
			'rmvb',
			'vob',
			'ogv',
			'drc',
			'mng',
			'qt',
			'yuv',
			'viv',
			'amv',
			'divx'
		];

		$check_extensions = empty( $extensions ) ? $default_extensions : $extensions;
		$extension        = self::get_extension( $url );

		return in_array( $extension, $check_extensions, true );
	}

	/**
	 * Check if URL is an audio file.
	 *
	 * @param string $url        The URL to check.
	 * @param array  $extensions Custom extensions (optional).
	 *
	 * @return bool True if audio URL.
	 */
	public static function is_audio( string $url, array $extensions = [] ): bool {
		$default_extensions = [
			'mp3',
			'wav',
			'ogg',
			'flac',
			'aac',
			'm4a',
			'wma',
			'aiff',
			'au',
			'ra',
			'ape',
			'opus',
			'gsm',
			'dts',
			'amr',
			'awb',
			'dvf',
			'dss',
			'msv',
			'nmf',
			'sln',
			'mp2',
			'mpc',
			'aif',
			'aifc',
			'3ga'
		];

		$check_extensions = empty( $extensions ) ? $default_extensions : $extensions;
		$extension        = self::get_extension( $url );

		return in_array( $extension, $check_extensions, true );
	}

	/**
	 * Check if URL supports oEmbed.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if oEmbed supported.
	 */
	public static function supports_oembed( string $url ): bool {
		return (bool) wp_oembed_get( $url );
	}

	/**
	 * Get current page URL.
	 *
	 * @return string Current page URL.
	 */
	public static function current(): string {
		return home_url( add_query_arg( null, null ) );
	}

	/**
	 * Get current page URL without query parameters.
	 *
	 * @return string Current page URL without query.
	 */
	public static function current_clean(): string {
		return strtok( self::current(), '?' );
	}

	/**
	 * Sanitize a URL for safe output.
	 *
	 * @param string $url The URL to sanitize.
	 *
	 * @return string Sanitized URL.
	 */
	public static function sanitize( string $url ): string {
		return esc_url_raw( $url );
	}

	/**
	 * Sanitize a URL for HTML output.
	 *
	 * @param string $url The URL to sanitize.
	 *
	 * @return string Sanitized URL for HTML.
	 */
	public static function sanitize_for_html( string $url ): string {
		return esc_url( $url );
	}

}
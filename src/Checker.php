<?php
/**
 * URL Checker Class
 *
 * Provides utility functions for checking URL accessibility and status,
 * including reachability tests and response validation.
 *
 * @package ArrayPress\URLUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\URLUtils;

/**
 * Checker Class
 *
 * HTTP operations for checking URL status and accessibility.
 */
class Checker {

	/**
	 * Default timeout for HTTP requests (in seconds).
	 *
	 * @var int
	 */
	private static int $default_timeout = 10;

	/**
	 * Check if a URL is reachable (returns 2xx status code).
	 *
	 * @param string $url     The URL to check.
	 * @param int    $timeout Request timeout in seconds.
	 *
	 * @return bool True if URL is reachable.
	 */
	public static function is_reachable( string $url, int $timeout = 0 ): bool {
		if ( ! URL::is_valid( $url ) ) {
			return false;
		}

		$timeout  = $timeout ?: self::$default_timeout;
		$response = wp_remote_head( $url, [
			'timeout'     => $timeout,
			'redirection' => 3,
			'user-agent'  => self::get_user_agent()
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		return $status_code >= 200 && $status_code < 300;
	}

	/**
	 * Get the HTTP status code of a URL.
	 *
	 * @param string $url     The URL to check.
	 * @param int    $timeout Request timeout in seconds.
	 *
	 * @return int|null HTTP status code or null on error.
	 */
	public static function get_status_code( string $url, int $timeout = 0 ): ?int {
		if ( ! URL::is_valid( $url ) ) {
			return null;
		}

		$timeout  = $timeout ?: self::$default_timeout;
		$response = wp_remote_head( $url, [
			'timeout'     => $timeout,
			'redirection' => 3,
			'user-agent'  => self::get_user_agent()
		] );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Check if a URL redirects to another URL.
	 *
	 * @param string $url     The URL to check.
	 * @param int    $timeout Request timeout in seconds.
	 *
	 * @return string|null Final URL after redirects, or null on error.
	 */
	public static function get_final_url( string $url, int $timeout = 0 ): ?string {
		if ( ! URL::is_valid( $url ) ) {
			return null;
		}

		$timeout  = $timeout ?: self::$default_timeout;
		$response = wp_remote_head( $url, [
			'timeout'     => $timeout,
			'redirection' => 5,
			'user-agent'  => self::get_user_agent()
		] );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$final_url = wp_remote_retrieve_header( $response, 'location' );

		return $final_url ?: $url;
	}

	/**
	 * Check if a URL is downloadable (has downloadable content).
	 *
	 * @param string $url     The URL to check.
	 * @param int    $timeout Request timeout in seconds.
	 *
	 * @return bool True if URL appears to be downloadable.
	 */
	public static function is_downloadable( string $url, int $timeout = 0 ): bool {
		if ( ! URL::is_valid( $url ) ) {
			return false;
		}

		$timeout  = $timeout ?: self::$default_timeout;
		$response = wp_remote_head( $url, [
			'timeout'     => $timeout,
			'redirection' => 3,
			'user-agent'  => self::get_user_agent()
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$content_type        = wp_remote_retrieve_header( $response, 'content-type' );
		$content_disposition = wp_remote_retrieve_header( $response, 'content-disposition' );

		// Check for explicit download header
		if ( strpos( $content_disposition, 'attachment' ) !== false ) {
			return true;
		}

		// Check if content type suggests downloadable content (not HTML/text)
		if ( empty( $content_type ) ) {
			return false;
		}

		// Consider it downloadable if it's NOT web content
		$web_content_types = [
			'text/html',
			'text/xml',
			'application/xml',
			'application/xhtml+xml',
			'text/plain'
		];

		$content_type = strtolower( trim( explode( ';', $content_type )[0] ) );

		return ! in_array( $content_type, $web_content_types, true );
	}

	/**
	 * Get basic info about a URL (status, final URL, content type).
	 *
	 * @param string $url     The URL to analyze.
	 * @param int    $timeout Request timeout in seconds.
	 *
	 * @return array|null URL info or null on error.
	 */
	public static function get_info( string $url, int $timeout = 0 ): ?array {
		if ( ! URL::is_valid( $url ) ) {
			return null;
		}

		$timeout  = $timeout ?: self::$default_timeout;
		$response = wp_remote_head( $url, [
			'timeout'     => $timeout,
			'redirection' => 5,
			'user-agent'  => self::get_user_agent()
		] );

		if ( is_wp_error( $response ) ) {
			return [
				'reachable'    => false,
				'status_code'  => null,
				'final_url'    => null,
				'content_type' => null,
				'error'        => $response->get_error_message()
			];
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$headers     = wp_remote_retrieve_headers( $response );

		return [
			'reachable'    => $status_code >= 200 && $status_code < 300,
			'status_code'  => $status_code,
			'final_url'    => $headers['location'] ?? $url,
			'content_type' => $headers['content-type'] ?? null,
			'error'        => null
		];
	}

	/**
	 * Check multiple URLs for reachability.
	 *
	 * @param array $urls    Array of URLs to check.
	 * @param int   $timeout Request timeout in seconds.
	 *
	 * @return array Array with URLs as keys and reachability as values.
	 */
	public static function check_multiple( array $urls, int $timeout = 0 ): array {
		$results = [];

		foreach ( $urls as $url ) {
			$results[ $url ] = self::is_reachable( $url, $timeout );
		}

		return $results;
	}

	/**
	 * Filter URLs to only reachable ones.
	 *
	 * @param array $urls    Array of URLs to filter.
	 * @param int   $timeout Request timeout in seconds.
	 *
	 * @return array Array of reachable URLs.
	 */
	public static function filter_reachable( array $urls, int $timeout = 0 ): array {
		$reachable = [];

		foreach ( $urls as $url ) {
			if ( self::is_reachable( $url, $timeout ) ) {
				$reachable[] = $url;
			}
		}

		return $reachable;
	}

	/**
	 * Set default timeout for all requests.
	 *
	 * @param int $timeout Timeout in seconds.
	 */
	public static function set_default_timeout( int $timeout ): void {
		self::$default_timeout = max( 1, $timeout );
	}

	/**
	 * Get the current default timeout.
	 *
	 * @return int Default timeout in seconds.
	 */
	public static function get_default_timeout(): int {
		return self::$default_timeout;
	}

	/**
	 * Get user agent string for requests.
	 *
	 * @return string User agent string.
	 */
	private static function get_user_agent(): string {
		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url();

		return "WordPress/{$site_name} (+{$site_url})";
	}

}
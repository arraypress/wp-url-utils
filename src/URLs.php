<?php
/**
 * URLs Utility Class
 *
 * Provides utility functions for working with multiple URLs,
 * including bulk validation, filtering, and analysis operations.
 *
 * @package ArrayPress\URLUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\URLUtils;

/**
 * URLs Class
 *
 * Bulk operations for working with multiple URLs.
 */
class URLs {

	/**
	 * Validate multiple URLs.
	 *
	 * @param array $urls Array of URLs to validate.
	 *
	 * @return array Array with URLs as keys and validation results as values.
	 */
	public static function validate( array $urls ): array {
		$results = [];
		foreach ( $urls as $url ) {
			$url             = trim( $url );
			$results[ $url ] = URL::is_valid( $url );
		}

		return $results;
	}

	/**
	 * Filter and return only valid URLs.
	 *
	 * @param array $urls Array of URLs to filter.
	 *
	 * @return array Array of valid URLs.
	 */
	public static function filter_valid( array $urls ): array {
		$valid = [];
		foreach ( $urls as $url ) {
			$url = trim( $url );
			if ( URL::is_valid( $url ) ) {
				$valid[] = $url;
			}
		}

		return $valid;
	}

	/**
	 * Filter and return only invalid URLs.
	 *
	 * @param array $urls Array of URLs to filter.
	 *
	 * @return array Array of invalid URLs.
	 */
	public static function filter_invalid( array $urls ): array {
		$invalid = [];
		foreach ( $urls as $url ) {
			$url = trim( $url );
			if ( ! URL::is_valid( $url ) ) {
				$invalid[] = $url;
			}
		}

		return $invalid;
	}

	/**
	 * Filter URLs by external/internal status.
	 *
	 * @param array  $urls Array of URLs.
	 * @param string $type Type to filter ('external' or 'internal').
	 *
	 * @return array Filtered URLs.
	 */
	public static function filter_by_location( array $urls, string $type ): array {
		$filtered = [];

		foreach ( $urls as $url ) {
			$include = false;

			switch ( strtolower( $type ) ) {
				case 'external':
					$include = URL::is_external( $url );
					break;
				case 'internal':
					$include = URL::is_same_domain( $url );
					break;
			}

			if ( $include ) {
				$filtered[] = $url;
			}
		}

		return $filtered;
	}

	/**
	 * Filter URLs by protocol.
	 *
	 * @param array  $urls     Array of URLs.
	 * @param string $protocol Protocol to filter ('http' or 'https').
	 *
	 * @return array Filtered URLs.
	 */
	public static function filter_by_protocol( array $urls, string $protocol ): array {
		$filtered = [];

		foreach ( $urls as $url ) {
			$url_protocol = parse_url( $url, PHP_URL_SCHEME );
			if ( $url_protocol === strtolower( $protocol ) ) {
				$filtered[] = $url;
			}
		}

		return $filtered;
	}

	/**
	 * Filter URLs by file type.
	 *
	 * @param array  $urls Array of URLs.
	 * @param string $type File type ('image', 'video', 'audio').
	 *
	 * @return array Filtered URLs.
	 */
	public static function filter_by_type( array $urls, string $type ): array {
		$filtered = [];

		foreach ( $urls as $url ) {
			$include = false;

			switch ( strtolower( $type ) ) {
				case 'image':
					$include = URL::is_image( $url );
					break;
				case 'video':
					$include = URL::is_video( $url );
					break;
				case 'audio':
					$include = URL::is_audio( $url );
					break;
			}

			if ( $include ) {
				$filtered[] = $url;
			}
		}

		return $filtered;
	}

	/**
	 * Convert URLs to HTTPS.
	 *
	 * @param array $urls Array of URLs to convert.
	 *
	 * @return array Array of HTTPS URLs.
	 */
	public static function to_https( array $urls ): array {
		$https_urls = [];
		foreach ( $urls as $url ) {
			$https_urls[] = URL::to_https( $url );
		}

		return $https_urls;
	}

	/**
	 * Convert URLs to HTTP.
	 *
	 * @param array $urls Array of URLs to convert.
	 *
	 * @return array Array of HTTP URLs.
	 */
	public static function to_http( array $urls ): array {
		$http_urls = [];
		foreach ( $urls as $url ) {
			$http_urls[] = URL::to_http( $url );
		}

		return $http_urls;
	}

	/**
	 * Make URLs relative.
	 *
	 * @param array $urls Array of URLs to make relative.
	 *
	 * @return array Array of relative URLs.
	 */
	public static function make_relative( array $urls ): array {
		$relative_urls = [];
		foreach ( $urls as $url ) {
			$relative_urls[] = URL::make_relative( $url );
		}

		return $relative_urls;
	}

	/**
	 * Extract domains from URLs.
	 *
	 * @param array $urls Array of URLs.
	 *
	 * @return array Array of unique domains.
	 */
	public static function get_domains( array $urls ): array {
		$domains = [];
		foreach ( $urls as $url ) {
			$domain = URL::get_domain( $url );
			if ( ! empty( $domain ) ) {
				$domains[] = $domain;
			}
		}

		return array_unique( $domains );
	}

	/**
	 * Extract URLs from text.
	 *
	 * @param string $text Text to extract URLs from.
	 *
	 * @return array Array of extracted URLs.
	 */
	public static function extract( string $text ): array {
		$pattern = '/https?:\/\/[^\s<>"]+/i';
		preg_match_all( $pattern, $text, $matches );

		$urls = array_unique( $matches[0] );

		return array_values( array_filter( $urls, [ URL::class, 'is_valid' ] ) );
	}

	/**
	 * Remove duplicate URLs.
	 *
	 * @param array $urls Array of URLs.
	 *
	 * @return array Array of unique URLs.
	 */
	public static function remove_duplicates( array $urls ): array {
		$unique = [];
		foreach ( $urls as $url ) {
			$url = trim( $url );
			if ( ! in_array( $url, $unique, true ) ) {
				$unique[] = $url;
			}
		}

		return $unique;
	}

	/**
	 * Sanitize multiple URLs.
	 *
	 * @param array $urls Array of URLs to sanitize.
	 *
	 * @return array Array of sanitized URLs.
	 */
	public static function sanitize( array $urls ): array {
		$sanitized = [];
		foreach ( $urls as $url ) {
			$clean_url = URL::sanitize( $url );
			if ( ! empty( $clean_url ) ) {
				$sanitized[] = $clean_url;
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize multiple URLs for HTML output.
	 *
	 * @param array $urls Array of URLs to sanitize.
	 *
	 * @return array Array of sanitized URLs for HTML.
	 */
	public static function sanitize_for_html( array $urls ): array {
		$sanitized = [];
		foreach ( $urls as $url ) {
			$clean_url = URL::sanitize_for_html( $url );
			if ( ! empty( $clean_url ) ) {
				$sanitized[] = $clean_url;
			}
		}

		return $sanitized;
	}

}
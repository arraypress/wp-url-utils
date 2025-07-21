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
	 * Add or replace the scheme in a URL.
	 *
	 * @param string $url    The URL to modify.
	 * @param string $scheme The scheme to add/replace (http, https).
	 *
	 * @return string URL with the specified scheme.
	 */
	public static function add_scheme( string $url, string $scheme = 'https' ): string {
		// Validate scheme
		if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			$scheme = 'https';
		}

		// If no scheme, add the specified one
		if ( ! preg_match( '/^https?:\/\//', $url ) ) {
			return $scheme . '://' . $url;
		}

		// Replace existing scheme
		return preg_replace( '/^https?:/', $scheme . ':', $url );
	}

	/**
	 * Convert URL to HTTPS.
	 *
	 * @param string $url The URL to convert.
	 *
	 * @return string HTTPS URL.
	 */
	public static function to_https( string $url ): string {
		return self::add_scheme( $url, 'https' );
	}

	/**
	 * Convert URL to HTTP.
	 *
	 * @param string $url The URL to convert.
	 *
	 * @return string HTTP URL.
	 */
	public static function to_http( string $url ): string {
		return self::add_scheme( $url, 'http' );
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
	 * Check if URL is from a video platform.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if video platform URL.
	 */
	public static function is_video_platform( string $url ): bool {
		$patterns = [
			// YouTube
			'/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)/',
			'/(?:m\.youtube\.com\/watch\?v=)/',

			// Vimeo
			'/(?:vimeo\.com\/|player\.vimeo\.com\/)/',

			// TikTok
			'/(?:tiktok\.com\/@[\w.-]+\/video\/|vm\.tiktok\.com\/|tiktok\.com\/t\/)/',
			'/(?:m\.tiktok\.com\/)/',

			// Instagram
			'/(?:instagram\.com\/(?:p|reel|tv)\/|instagr\.am\/p\/)/',

			// Facebook/Meta
			'/(?:facebook\.com\/watch\?v=|fb\.watch\/|facebook\.com\/.*\/videos\/)/',
			'/(?:m\.facebook\.com\/watch\/)/',

			// Twitch
			'/(?:twitch\.tv\/videos\/|clips\.twitch\.tv\/)/',
			'/(?:m\.twitch\.tv\/)/',

			// Twitter/X
			'/(?:twitter\.com\/.*\/status\/.*\/video\/|x\.com\/.*\/status\/)/',

			// Dailymotion
			'/(?:dailymotion\.com\/video\/|dai\.ly\/)/',

			// Rumble
			'/(?:rumble\.com\/|rumble\.com\/embed\/)/',

			// BitChute
			'/(?:bitchute\.com\/video\/)/',

			// Wistia
			'/(?:wistia\.com\/medias\/|.*\.wistia\.com\/)/',

			// JW Player
			'/(?:jwplayer\.com\/)/',

			// Brightcove
			'/(?:players\.brightcove\.net\/)/',

			// Vidyard
			'/(?:vidyard\.com\/watch\/)/',

			// Loom
			'/(?:loom\.com\/share\/)/',

			// Streamable
			'/(?:streamable\.com\/)/',

			// Giphy (videos)
			'/(?:giphy\.com\/gifs\/|media\.giphy\.com\/)/',

			// Reddit (videos)
			'/(?:v\.redd\.it\/)/',

			// LinkedIn (videos)
			'/(?:linkedin\.com\/posts\/.*-activity-.*video)/',

			// Coub
			'/(?:coub\.com\/view\/)/',

			// 9GAG (videos)
			'/(?:9gag\.com\/gag\/)/',

			// Metacafe
			'/(?:metacafe\.com\/watch\/)/',

			// Internet Archive
			'/(?:archive\.org\/details\/)/',

			// Chinese platforms
			'/(?:youku\.com\/|v\.youku\.com\/)/',
			'/(?:bilibili\.com\/video\/|b23\.tv\/)/',
			'/(?:weibo\.com\/tv\/show\/)/',

			// Other international
			'/(?:ok\.ru\/video\/)/',
			'/(?:vk\.com\/video)/',
		];

		return self::matches_patterns( $url, $patterns );
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
	 * Check if URL is from an audio platform.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if audio platform URL.
	 */
	public static function is_audio_platform( string $url ): bool {
		$patterns = [
			// Spotify
			'/(?:spotify\.com\/track\/|open\.spotify\.com\/(?:track|episode|show)\/|spotify\.link\/)/',

			// SoundCloud
			'/(?:soundcloud\.com\/|on\.soundcloud\.com\/)/',

			// Apple Music/Podcasts
			'/(?:music\.apple\.com\/|podcasts\.apple\.com\/)/',

			// YouTube Music
			'/(?:music\.youtube\.com\/)/',

			// Bandcamp
			'/(?:.*\.bandcamp\.com\/|bandcamp\.com\/)/',

			// Anchor/Spotify for Podcasters
			'/(?:anchor\.fm\/)/',

			// Google Podcasts
			'/(?:podcasts\.google\.com\/)/',

			// AudioMack
			'/(?:audiomack\.com\/)/',

			// Deezer
			'/(?:deezer\.com\/track\/|deezer\.page\.link\/)/',

			// Tidal
			'/(?:tidal\.com\/track\/|tidal\.com\/album\/)/',

			// Amazon Music
			'/(?:music\.amazon\.com\/)/',

			// Pandora
			'/(?:pandora\.com\/)/',

			// Last.fm
			'/(?:last\.fm\/music\/)/',

			// Mixcloud
			'/(?:mixcloud\.com\/)/',

			// Podcast platforms
			'/(?:podcasts\.google\.com\/|castbox\.fm\/|player\.fm\/)/',
			'/(?:overcast\.fm\/|pocketcasts\.com\/)/',
			'/(?:stitcher\.com\/podcast\/|tunein\.com\/)/',
			'/(?:iheart\.com\/podcast\/|podbean\.com\/)/',
			'/(?:spreaker\.com\/|buzzsprout\.com\/)/',

			// Radio platforms
			'/(?:radiocut\.fm\/|radio\.com\/)/',
			'/(?:iheart\.com\/live\/|tunein\.com\/radio\/)/',

			// Audioboom
			'/(?:audioboom\.com\/)/',

			// Vocaroo
			'/(?:vocaroo\.com\/)/',

			// Clyp
			'/(?:clyp\.it\/)/',

			// Archive.org audio
			'/(?:archive\.org\/details\/.*\.(mp3|flac|ogg))/',

			// Chinese platforms
			'/(?:music\.163\.com\/|y\.qq\.com\/)/',
			'/(?:kugou\.com\/|kuwo\.cn\/)/',
		];

		return self::matches_patterns( $url, $patterns );
	}

	/**
	 * Check if URL is from a social media platform.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if social media platform URL.
	 */
	public static function is_social_platform( string $url ): bool {
		$patterns = [
			// Facebook/Meta
			'/(?:facebook\.com\/|fb\.com\/|m\.facebook\.com\/|fb\.me\/)/',

			// Twitter/X
			'/(?:twitter\.com\/|x\.com\/|t\.co\/|mobile\.twitter\.com\/)/',

			// Instagram
			'/(?:instagram\.com\/|instagr\.am\/)/',

			// LinkedIn
			'/(?:linkedin\.com\/|lnkd\.in\/)/',

			// TikTok
			'/(?:tiktok\.com\/|vm\.tiktok\.com\/|tiktok\.com\/t\/|m\.tiktok\.com\/)/',

			// Snapchat
			'/(?:snapchat\.com\/|snap\.com\/)/',

			// Reddit
			'/(?:reddit\.com\/|redd\.it\/|old\.reddit\.com\/)/',

			// Pinterest
			'/(?:pinterest\.com\/|pin\.it\/|pinterest\.ca\/|pinterest\.co\.uk\/)/',

			// Discord
			'/(?:discord\.gg\/|discord\.com\/|discordapp\.com\/)/',

			// WhatsApp
			'/(?:wa\.me\/|api\.whatsapp\.com\/|web\.whatsapp\.com\/)/',

			// Telegram
			'/(?:t\.me\/|telegram\.me\/|telegram\.dog\/)/',

			// YouTube (social aspects)
			'/(?:youtube\.com\/c\/|youtube\.com\/user\/|youtube\.com\/channel\/)/',

			// Tumblr
			'/(?:tumblr\.com\/|.*\.tumblr\.com\/)/',

			// Mastodon (various instances)
			'/(?:mastodon\.social\/|mastodon\.online\/|.*\.social\/@)/',

			// Threads
			'/(?:threads\.net\/)/',

			// BeReal
			'/(?:bere\.al\/)/',

			// Clubhouse
			'/(?:clubhouse\.com\/)/',

			// Twitch (social aspects)
			'/(?:twitch\.tv\/(?!videos)[\w-]+$)/',

			// VKontakte
			'/(?:vk\.com\/|vkontakte\.ru\/)/',

			// Weibo
			'/(?:weibo\.com\/|weibo\.cn\/)/',

			// WeChat
			'/(?:wechat\.com\/)/',

			// LINE
			'/(?:line\.me\/)/',

			// Viber
			'/(?:viber\.com\/)/',

			// QQ
			'/(?:qq\.com\/)/',

			// Nextdoor
			'/(?:nextdoor\.com\/)/',

			// Medium (social aspects)
			'/(?:medium\.com\/@|.*\.medium\.com\/)/',

			// Quora
			'/(?:quora\.com\/)/',

			// Stack Overflow
			'/(?:stackoverflow\.com\/users\/)/',

			// Flickr
			'/(?:flickr\.com\/photos\/)/',

			// DeviantArt
			'/(?:deviantart\.com\/|.*\.deviantart\.com\/)/',

			// Behance
			'/(?:behance\.net\/)/',

			// Dribbble
			'/(?:dribbble\.com\/)/',

			// Foursquare/Swarm
			'/(?:foursquare\.com\/|swarmapp\.com\/)/',

			// Meetup
			'/(?:meetup\.com\/)/',

			// Eventbrite
			'/(?:eventbrite\.com\/)/',

			// Yelp
			'/(?:yelp\.com\/)/',

			// Goodreads
			'/(?:goodreads\.com\/)/',

			// MySpace (legacy)
			'/(?:myspace\.com\/)/',

			// Chinese platforms
			'/(?:xiaohongshu\.com\/|douyin\.com\/)/',
			'/(?:zhihu\.com\/|baidu\.com\/tieba\/)/',
		];

		return self::matches_patterns( $url, $patterns );
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

	/**
	 * Helper method to check if URL matches any of the given patterns.
	 *
	 * @param string $url      The URL to check.
	 * @param array  $patterns Array of regex patterns.
	 *
	 * @return bool True if URL matches any pattern.
	 */
	private static function matches_patterns( string $url, array $patterns ): bool {
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $url ) ) {
				return true;
			}
		}

		return false;
	}

}
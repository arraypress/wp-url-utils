# WordPress URL Utilities

Comprehensive URL manipulation, validation, and connectivity checking for WordPress. Clean APIs for single URLs, bulk operations, and HTTP status checking.

## Installation

```bash
composer require arraypress/wp-url-utils
```

## Usage

### Single URL Operations

```php
use ArrayPress\URLUtils\URL;

// Validation and checks
$valid = URL::is_valid( 'https://example.com' );
$external = URL::is_external( 'https://google.com' );
$https = URL::is_https( 'https://example.com' );

// URL parts
$domain = URL::get_domain( 'https://example.com/path' ); // example.com
$path = URL::get_path( 'https://example.com/path' ); // /path
$extension = URL::get_extension( 'https://example.com/file.pdf' ); // pdf

// Manipulation
$https_url = URL::to_https( 'http://example.com' );
$relative = URL::make_relative( 'https://mysite.com/page' ); // /page
$with_params = URL::add_params( $url, [ 'utm_source' => 'email' ] );

// File type detection
$is_image = URL::is_image( 'photo.jpg' );
$is_video = URL::is_video( 'movie.mp4', [ 'mp4', 'avi' ] ); // custom extensions

// UTM tracking
$tracked = URL::add_utm( $url, [
    'source' => 'newsletter',
    'campaign' => 'summer'
]);

$smart_utm = URL::add_utm_smart( $url, [ 'campaign' => 'promo' ] );
// Auto-detects WordPress context (admin screen, template, etc.)

// Sanitization
$clean = URL::sanitize( $_POST['website'] );
$html_safe = URL::sanitize_for_html( $user_url );

// Current page
$current = URL::current();
$clean_current = URL::current_clean(); // without query params
```

### Bulk URL Operations

```php
use ArrayPress\URLUtils\URLs;

$url_list = [
    'https://example.com',
    'http://test.com',
    'invalid-url',
    'https://mysite.com/page'
];

// Filtering
$valid_urls = URLs::filter_valid( $url_list );
$external = URLs::filter_by_location( $url_list, 'external' );
$https_only = URLs::filter_by_protocol( $url_list, 'https' );
$images = URLs::filter_by_type( $url_list, 'image' );

// Conversion
$all_https = URLs::to_https( $url_list );
$all_relative = URLs::make_relative( $url_list );

// Analysis
$domains = URLs::get_domains( $url_list );
$found_urls = URLs::extract( $text_content );
$unique = URLs::remove_duplicates( $url_list );

// Sanitization
$clean_urls = URLs::sanitize( $url_list );
$html_safe_urls = URLs::sanitize_for_html( $url_list );
```

### HTTP Connectivity Checking

```php
use ArrayPress\URLUtils\Checker;

// Single URL checks
$reachable = Checker::is_reachable( 'https://example.com' );
$status = Checker::get_status_code( 'https://example.com' ); // 200, 404, etc.
$final_url = Checker::get_final_url( 'https://bit.ly/short' ); // follows redirects
$downloadable = Checker::is_downloadable( 'https://example.com/file.pdf' );

// Comprehensive info
$info = Checker::get_info( 'https://example.com' );
// Returns: reachable, status_code, final_url, content_type, error

// Bulk checking
$results = Checker::check_multiple( $url_list );
$working_urls = Checker::filter_reachable( $url_list );

// Configuration
Checker::set_default_timeout( 15 ); // seconds
```

## Common Use Cases

**Form validation:**
```php
$website = URL::sanitize( $_POST['website'] );
if ( ! URL::is_valid( $website ) ) {
    $errors[] = 'Invalid website URL';
}
```

**Link checking:**
```php
$broken_links = [];
foreach ( $links as $link ) {
    if ( ! Checker::is_reachable( $link, 5 ) ) {
        $broken_links[] = $link;
    }
}
```

**Content processing:**
```php
$found_urls = URLs::extract( $post_content );
$external_urls = URLs::filter_by_location( $found_urls, 'external' );
$safe_urls = URLs::sanitize_for_html( $external_urls );
```

**UTM campaign tracking:**
```php
$campaign_url = URL::add_utm_smart( 'https://mysite.com/sale', [
    'source' => 'email',
    'campaign' => 'black-friday'
]);
// Automatically adds medium based on WordPress context
```

## All Methods

### URL Class
- `is_valid()`, `is_external()`, `is_https()`, `is_same_domain()`
- `get_domain()`, `get_path()`, `get_query()`, `get_extension()`
- `add_params()`, `remove_params()`, `to_https()`, `make_relative()`
- `is_image()`, `is_video()`, `is_audio()` (with custom extensions)
- `add_utm()`, `add_utm_smart()`, `sanitize()`, `current()`

### URLs Class
- `validate()`, `filter_valid()`, `filter_invalid()`
- `filter_by_location()`, `filter_by_protocol()`, `filter_by_type()`
- `to_https()`, `make_relative()`, `get_domains()`
- `extract()`, `remove_duplicates()`, `sanitize()`

### Checker Class
- `is_reachable()`, `get_status_code()`, `get_final_url()`
- `is_downloadable()`, `get_info()`
- `check_multiple()`, `filter_reachable()`
- `set_default_timeout()`

## Requirements

- PHP 7.4+
- WordPress 5.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/wp-url-utils)
- [Issue Tracker](https://github.com/arraypress/wp-url-utils/issues)
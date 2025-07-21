<?php
/**
 * URL Cleaner Class
 *
 * Remove tracking parameters from URLs for cleaner links and better privacy.
 *
 * @package ArrayPress\URLUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\URLUtils;

/**
 * Cleaner Class
 *
 * Simple tracking parameter removal for URLs.
 */
class Cleaner {

	/**
	 * Comprehensive list of tracking parameters to remove.
	 * Based on ClearURLs, tracking-query-params-registry, and industry research.
	 *
	 * @var array
	 */
	private static array $tracking_params = [
		// Google Analytics & Ads
		'gclid',
		'gclsrc',
		'gbraid',
		'wbraid',
		'gad_source',
		'srsltid',
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'utm_id',
		'utm_source_platform',
		'utm_creative_format',
		'utm_marketing_tactic',
		'_ga',
		'_gl',
		'ei',
		'oq',
		'esrc',
		'uact',
		'cd',
		'cad',
		'aqs',
		'sourceid',
		'sxsrf',
		'rlz',
		'pcampaignid',
		'iflsig',
		'fbs',
		'ictx',
		'cshid',
		'i-would-rather-use-firefox',

		// Google DoubleClick & Merchant Centre
		'dclid',
		'gPromoCode',
		'gQT',

		// Social Media Platforms
		'fbclid',
		'twclid',
		'ttclid',
		'igshid',
		'igsh',
		'ScCid',
		'ndclid',
		'li_fat_id',

		// Search Engines
		'yclid',
		'msclkid',
		'sk',
		'sp',
		'sc',
		'qs',
		'qp',

		// Email Marketing
		'mc_cid',
		'mc_eid',
		'mc_tc',
		'_ke',
		'_kx',
		'dm_i',
		'ml_subscriber',
		'ml_subscriber_hash',
		'mkt_tok',

		// Marketing Automation
		'_bta_tid',
		'_bta_c',
		'trk_contact',
		'trk_msg',
		'trk_module',
		'trk_sid',
		'mkwid',
		'pcrid',
		'ef_id',
		's_kwcid',
		'rb_clickid',
		'wickedid',
		'vero_conv',
		'vero_id',
		'oly_anon_id',
		'oly_enc_id',

		// Analytics Platforms - Piwik
		'pk_campaign',
		'pk_kwd',
		'pk_keyword',
		'piwik_campaign',
		'piwik_kwd',
		'piwik_keyword',

		// Analytics Platforms - Matomo
		'mtm_campaign',
		'mtm_keyword',
		'mtm_source',
		'mtm_medium',
		'mtm_content',
		'mtm_cid',
		'mtm_group',
		'mtm_placement',
		'matomo_campaign',
		'matomo_keyword',
		'matomo_source',
		'matomo_medium',
		'matomo_content',
		'matomo_cid',
		'matomo_group',
		'matomo_placement',

		// HubSpot
		'hsa_cam',
		'hsa_grp',
		'hsa_mt',
		'hsa_src',
		'hsa_ad',
		'hsa_acc',
		'hsa_net',
		'hsa_kw',
		'hsa_tgt',
		'hsa_ver',
		'__hssc',
		'__hstc',
		'__hsfp',
		'_hsenc',
		'hsCtaTracking',
		'_hsmi',

		// E-commerce & Affiliate
		'mkevt',
		'mkcid',
		'mkrid',
		'campid',
		'toolid',
		'customid',
		'_trksid',
		'_trkparms',
		'_from',
		'hash',
		'_branch_match_id',
		'irclickid',
		'irgwc',
		'epik',
		'tag',
		'ref',
		'source',
		'campaign',
		'ad_id',
		'click_id',
		'campaign_id',
		'affiliate_id',
		'partner_id',
		'referrer',
		'tracking_id',
		'k_clickid',
		'aff_request_id',

		// Amazon tracking
		'qid',
		'sr',
		'sprefix',
		'crid',
		'keywords',
		'ref_',
		'th',
		'linkCode',
		'creativeASIN',
		'ascsubtag',
		'aaxitk',
		'hsa_cr_id',
		'dchild',
		'camp',
		'creative',
		'content-id',
		'dib',
		'dib_tag',
		'social_share',
		'starsLeft',
		'skipTwisterOG',
		'_encoding',
		'smid',
		'field-lbr_brands_browse-bin',
		'qualifier',
		'spIA',
		'ms3_c',
		'refRID',

		// Media Platforms
		'si',
		'feature',
		'kw',
		'pp',
		'u_code',
		'preview_pb',
		'_d',
		'_t',
		'_r',
		'timestamp',
		'user_id',
		'share_app_name',
		'share_iid',

		// Social Media Extended
		'__tn__',
		'eid',
		'__cft__',
		'__xts__',
		'comment_tracking',
		'dti',
		'app',
		'video_source',
		'ftentidentifier',
		'pageid',
		'padding',
		'ls_ref',
		'action_history',
		'tracking',
		'referral_code',
		'referral_story_type',
		'eav',
		'sfnsn',
		'idorvanity',
		'wtsid',
		'rdc',
		'rdr',
		'paipv',
		'_nc_x',
		'_rdr',
		'mibextid',

		// Twitter/X Extended
		'cn',
		'ref_url',
		't',
		's',

		// Reddit
		'%24deep_link',
		'correlation_id',
		'ref_campaign',
		'ref_source',
		'%243p',
		'%24original_url',
		'share_id',

		// SMS & Marketing
		'sms_source',
		'sms_click',
		'sms_uph',

		// Other Platforms
		'rtid',
		'vmcid',
		'tw_source',
		'tw_campaign',
		'tw_term',
		'tw_content',
		'tw_adid',
		'cvid',
		'ocid',
		'__twitter_impression',
		'Echobox',
		'spm',
		'ceneo_spo',
		'_openstat',
		'os_ehash',
		'cmpid',
		'tracking_source',

		// GoDataFeed
		'gdfms',
		'gdftrk',
		'gdffi',

		// Springbot
		'redirect_log_mongo_id',
		'redirect_mongo_id',
		'sb_referer_host',

		// Drip
		'__s',

		// Seznam (Czech search engine)
		'sznclid',

		// Chinese platforms (Alibaba, Taobao, etc.)
		'price',
		'sourceType',
		'suid',
		'ut_sk',
		'un',
		'share_crt_v',
		'sp_tk',
		'cpp',
		'shareurl',
		'short_name',
		'app',
		'pvid',
		'algo_expid',
		'algo_pvid',
		'ns',
		'abbucket',
		'ali_refid',
		'ali_trackid',
		'acm',
		'utparam',
		'pos',
		'abtest',
		'trackInfo',
		'utkn',
		'scene',
		'mytmenu',
		'turing_bucket',
		'lygClk',
		'impid',
		'bftTag',
		'bftRwd',
		'activity_id',
		'user_number_id',

		// Bilibili (Chinese video platform)
		'callback',
		'spm_id_from',
		'from_source',
		'from',
		'seid',
		'mid',
		'share_source',
		'msource',
		'refer_from',
		'share_from',
		'share_medium',
		'share_plat',
		'share_tag',
		'share_session_id',
		'unique_k',
		'vd_source',
		'plat_id',
		'buvid',
		'is_story_h5',
		'up_id',
		'bbid',
		'ts',
		'visit_id',
		'session_id',
		'broadcast_type',
		'is_room_feed',

		// Xiaohongshu (Little Red Book)
		'xhsshare',
		'author_share',
		'type',
		'xsec_source',
		'share_from_user_hidden',
		'app_version',
		'ignoreEngage',
		'app_platform',
		'apptime',
		'appuid',
		'shareRedId',
		'share_id',
		'exSource',
		'verifyUuid',
		'verifyType',
		'verifyBiz',

		// News & Media
		'ftag',
		'intcid',
		'smid',
		'CMP',
		'sh',
		'ito',
		'shareToken',
		'taid',
		'__source',
		'ncid',
		'sr',
		'sr_share',
		'guccounter',
		'guce_referrer',
		'guce_referrer_sig',

		// E-commerce Extended
		'irclickid',
		'loc',
		'acampID',
		'mpid',
		'intl',
		'u1',
		'_requestid',
		'cid',
		'dl',
		'di',
		'sd',
		'bi',
		'partner',
		'rtoken',
		'ex',
		'identityID',
		'MID',
		'RID',
		'riftinfo',
		'epic_affiliate',
		'epic_gameId',
		'istCompanyId',
		'istFeedId',
		'istItemId',
		'istBid',
		'clickOrigin',
		'clickTrackInfo',
		'abid',
		'ad_src',
		'scm',
		'src',
		'from',
		'pa',
		'pid_pvid',
		'did',
		'mp',
		'cid',
		'impsrc',
		'pos',
		'publish_id',
		'sp_atk',
		'xptdk',

		// Travel & Booking
		'federated_search_id',
		'search_type',
		'source_impression_id',

		// Other Services
		'refPageId',
		'trackId',
		'tctx',
		'refer_method',
		'from_search',
		'from_srp',
		'qid',
		'rank',
		'ac',
		'context_referrer',
		'ref_ctx_id',
		'funnel',
		'click_key',
		'click_sum',
		'organic_search_click',
		'source_location',
		'psf_variant',
		'share_intent',
		'funnelUUID',
		'email_token',
		'email_source',
		'form_type',
		'as',
		'platform',
		'redirect_source',
		'src',
		'x',
		'_returnURL',
		'redirectedFrom',
		'share',
		'origin',
		'ecid',
		'PostType',
		'ServiceType',
		'UniqueID',
		'TheTime',
		'trkid',
		'whid',
		'ddw',
		'ds_ch',
		'medium',
		'content',
		'snr',
		'u',
		'tt_medium',
		'tt_content',
		'from',
		'alid',
		'vss',
		't',
		'swnt',
		'grpos',
		'ptl',
		'stl',
		'exp',
		'plim',
		'nb',
		'wbdcd',
		'tpa',
		'webUserId',
		'spMailingID',
		'spUserID',
		'spJobID',
		'spReportId',
		'cm_lm',
		'cm_mmc',
		'int_campaign',
		'lr',
		'redircnt',
		'ecp',
		'm_bt',
		'iref',
		'sc_referrer',
		'sc_ua',
		'email_referrer',
		'email_subject',
		'link_id',
		'can_id',
		'refId',
		'trk',
		'trackingId',
		'b',
		'h',
		'cuid',

		// Session & misc
		'sessionid',
		'session_id',
		'_',
		'v',
		't',
		'r',
	];

	/**
	 * Strip tracking parameters from a URL.
	 *
	 * @param string $url    The URL to clean.
	 * @param array  $custom Additional parameters to remove.
	 * @param array  $keep   Parameters to keep (whitelist).
	 *
	 * @return string Cleaned URL.
	 */
	public static function strip( string $url, array $custom = [], array $keep = [] ): string {
		if ( ! URL::is_valid( $url ) ) {
			return $url;
		}

		$params_to_remove = array_merge( self::$tracking_params, $custom );

		// Remove parameters that aren't in the keep list
		if ( ! empty( $keep ) ) {
			$params_to_remove = array_diff( $params_to_remove, $keep );
		}

		return remove_query_arg( $params_to_remove, $url );
	}

	/**
	 * Strip tracking parameters from multiple URLs.
	 *
	 * @param array $urls   Array of URLs to clean.
	 * @param array $custom Additional parameters to remove.
	 * @param array $keep   Parameters to keep.
	 *
	 * @return array Array of cleaned URLs.
	 */
	public static function strip_multiple( array $urls, array $custom = [], array $keep = [] ): array {
		$cleaned = [];

		foreach ( $urls as $url ) {
			$cleaned[] = self::strip( $url, $custom, $keep );
		}

		return $cleaned;
	}

	/**
	 * Check if URL has tracking parameters.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool True if tracking parameters found.
	 */
	public static function has_tracking( string $url ): bool {
		if ( ! URL::is_valid( $url ) ) {
			return false;
		}

		$query = parse_url( $url, PHP_URL_QUERY );

		if ( empty( $query ) ) {
			return false;
		}

		parse_str( $query, $params );

		foreach ( self::$tracking_params as $tracking_param ) {
			if ( isset( $params[ $tracking_param ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add custom tracking parameters to the default list.
	 *
	 * @param array $params Parameters to add.
	 */
	public static function add_params( array $params ): void {
		self::$tracking_params = array_unique( array_merge( self::$tracking_params, $params ) );
	}

	/**
	 * Sanitize URLs - strip tracking, validate, and remove duplicates.
	 *
	 * @param array $urls   Array of URLs to sanitize.
	 * @param array $custom Additional parameters to remove.
	 * @param array $keep   Parameters to keep.
	 *
	 * @return array Array of clean, valid, unique URLs.
	 */
	public static function sanitize( array $urls, array $custom = [], array $keep = [] ): array {
		// Filter to only valid URLs
		$valid_urls = URLs::filter_valid( $urls );

		// Strip tracking parameters
		$clean_urls = self::strip_multiple( $valid_urls, $custom, $keep );

		// Remove duplicates
		return URLs::remove_duplicates( $clean_urls );
	}

	/**
	 * Get all tracking parameters.
	 *
	 * @return array Default tracking parameters.
	 */
	public static function get_params(): array {
		return self::$tracking_params;
	}

}
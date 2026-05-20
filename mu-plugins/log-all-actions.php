<?php
/**
 * Plugin Name: Log All Actions
 * Description: Logs all actions and filters fired during a page load to wp-content/logs/actions.log.
 * Version: 1.1.0
 * Author: Developer
 */

if (!defined("ABSPATH")) {
    exit;
}

// ──────────────────────────────────────────────
// Configuration
// ──────────────────────────────────────────────

// Master switch: set to false to disable logging without deleting the file.
if ( ! defined( 'LAA_ENABLED' ) ) {
	define( 'LAA_ENABLED', true );
}

// Directory where log files are stored (WP_CONTENT_DIR points to wp-content).
if ( ! defined( 'LAA_LOG_DIR' ) ) {
	define( 'LAA_LOG_DIR', WP_CONTENT_DIR . '/logs' );
}

// Log file name.
if ( ! defined( 'LAA_LOG_FILE' ) ) {
	define( 'LAA_LOG_FILE', 'actions.log' );
}

// Maximum log file size in bytes before rotation (5 MB).
if ( ! defined( 'LAA_MAX_SIZE' ) ) {
	define( 'LAA_MAX_SIZE', 5 * 1024 * 1024 );
}

// Timezone for logs (e.g., 'Asia/Singapore', 'Asia/Manila', 'America/New_York').
if ( ! defined( 'LAA_TIMEZONE' ) ) {
	define( 'LAA_TIMEZONE', 'Asia/Singapore' );
}

// List of high-frequency/internal translation hooks to exclude to eliminate log bloat and CPU overhead.
if ( ! defined( 'LAA_BLACKLIST' ) ) {
	define( 'LAA_BLACKLIST', array(
		'gettext',
		'gettext_with_context',
		'ngettext',
		'ngettext_with_context',
		'locale',
		'sanitize_key',
		'translations_opened',
		'override_load_textdomain',
		'plugin_locale',
		'theme_locale',
		'determine_current_user'
	) );
}

// Maximum number of hooks captured per page request to prevent memory/CPU bloating.
if ( ! defined( 'LAA_MAX_HOOKS_PER_REQUEST' ) ) {
	define( 'LAA_MAX_HOOKS_PER_REQUEST', 2000 );
}

// Set to true to only log when query parameter 'laa_debug' or cookie 'laa_debug' is present.
if ( ! defined( 'LAA_REQUIRE_TRIGGER' ) ) {
	define( 'LAA_REQUIRE_TRIGGER', false );
}


if ( ! LAA_ENABLED ) {
	return;
}


// Logger Class

final class LAA_Action_Logger {

	/** @var LAA_Action_Logger|null Singleton instance. */
	private static $instance = null;

	/** @var string[] Buffered log lines waiting to be written to disk. */
	private $buffer = array();

	/** @var int Sequential counter for each captured hook. */
	private $counter = 0;

	/** @var float Request start time with microsecond precision. */
	private $start_time;

	/** @var DateTimeZone|null Cached timezone object. */
	private $tz_object = null;

	
	// Get (or create) the singleton instance and start logging.
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// Constructor — sets up hooks.
	private function __construct() {
		// If trigger is required, verify presence of parameter or cookie
		if ( LAA_REQUIRE_TRIGGER ) {
			$has_trigger = isset( $_GET['laa_debug'] ) || isset( $_COOKIE['laa_debug'] );
			if ( ! $has_trigger ) {
				return;
			}
		}

		// Record when this page request started
		$this->start_time = microtime( true );

		// Record the starting info (URL, IP, Method)
		$this->buffer_request_header();

		// Hook into the special 'all' hook
		add_action( 'all', array( $this, 'capture_hook' ) );

		// Register the shutdown writer with the lowest priority (run last)
		add_action( 'shutdown', array( $this, 'flush_to_disk' ), PHP_INT_MAX );

		// Fallback: in case a fatal error occurs, flush via PHP's native shutdown
		register_shutdown_function( array( $this, 'emergency_flush' ) );
	}

	// Writes a visual separator and request metadata into the buffer.
	private function buffer_request_header() {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
		$uri    = isset( $_SERVER['REQUEST_URI'] )    ? $_SERVER['REQUEST_URI']    : '(none)';
		$ip     = isset( $_SERVER['REMOTE_ADDR'] )    ? $_SERVER['REMOTE_ADDR']    : '(none)';
		$time   = substr( $this->get_local_timestamp(), 0, 19 );

		$this->buffer[] = '';
		$this->buffer[] = '=====================================';
		$this->buffer[] = "REQUEST: {$method} {$uri}";
		$this->buffer[] = "TIME:    {$time}";
		$this->buffer[] = "IP:      {$ip}";
		$this->buffer[] = '=====================================';
	}

	// Callback attached to the 'all' hook.
	public function capture_hook() {
		$hook_name = current_filter();

		// Prevent infinite loop if we capture our own shutdown handler
		if ( 'shutdown' === $hook_name && doing_action( 'shutdown' ) ) {
			return;
		}

		// 1. Blacklist check to filter out extreme high-frequency translation/internal hooks
		if ( in_array( $hook_name, LAA_BLACKLIST, true ) ) {
			return;	
		}

		// 2. Cap the buffer to prevent memory exhaustion
		if ( $this->counter >= LAA_MAX_HOOKS_PER_REQUEST ) {
			if ( LAA_MAX_HOOKS_PER_REQUEST === $this->counter ) {
				$this->buffer[] = sprintf(
					'#%04d | +%8sms | [WARNING] Max hook logging limit reached (%d). Capped to prevent memory overhead.',
					$this->counter + 1,
					number_format( round( ( microtime( true ) - $this->start_time ) * 1000, 2 ), 2 ),
					LAA_MAX_HOOKS_PER_REQUEST
				);
				$this->counter++; // Increment past limit so warning fires only once
			}
			return;
		}

		$this->counter++;

		// Number of arguments passed to this hook
		$num_args = func_num_args();

		// Elapsed time since request start in milliseconds
		$elapsed = round( ( microtime( true ) - $this->start_time ) * 1000, 2 );

		// Add a nicely padded row to our buffer array
		// PERFORMANCE OPTIMIZATION: Removed full local timestamp formatting from individual rows.
		// Relative elapsed time (+XX.XXms) is highly precise and doesn't trigger heavy timezone math.
		$this->buffer[] = sprintf(
			'#%04d | +%8sms | %-40s | args: %d',
			$this->counter,
			number_format( $elapsed, 2 ),
			$hook_name,
			$num_args
		);
	}

	// Flush all buffered lines to the log file on shutdown.
	public function flush_to_disk() {
		if ( empty( $this->buffer ) ) {
			return;
		}

		// Add a summary footer
		$elapsed_total = round( ( microtime( true ) - $this->start_time ) * 1000, 2 );
		$this->buffer[] = '-------------------------------------';
		$this->buffer[] = sprintf(
			'TOTAL: %d hooks fired in %s ms',
			$this->counter,
			number_format( $elapsed_total, 2 )
		);
		$this->buffer[] = '=====================================';
		$this->buffer[] = '';

		// Ensure our directory is ready
		if ( ! $this->ensure_log_directory() ) {
			return; // Gracefully abort if we can't write
		}

		$log_path = LAA_LOG_DIR . '/' . LAA_LOG_FILE;

		// Perform log rotation if file gets too big
		$this->maybe_rotate( $log_path );

		// Merge buffer into a single string
		$content = implode( PHP_EOL, $this->buffer ) . PHP_EOL;

		// Append the content. LOCK_EX prevents different page requests from writing over each other!
		@file_put_contents( $log_path, $content, FILE_APPEND | LOCK_EX );

		// Clear buffer so we don't write it twice
		$this->buffer = array();
	}

	// Emergency callback if the page crashes before normal shutdown fires.
	public function emergency_flush() {
		if ( ! empty( $this->buffer ) ) {
			$this->flush_to_disk();
		}
	}

	// Creates the logs folder and secures it.
	private function ensure_log_directory() {
		if ( is_dir( LAA_LOG_DIR ) ) {
			return true;
		}

		// Create it recursively
		if ( ! @mkdir( LAA_LOG_DIR, 0755, true ) ) {
			return false;
		}

		// Secure it by blocking public URL access
		$htaccess = LAA_LOG_DIR . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			@file_put_contents( $htaccess, "Order deny,allow\nDeny from all\n" );
		}

		// Silent backup index file
		$index = LAA_LOG_DIR . '/index.php';
		if ( ! file_exists( $index ) ) {
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}

		return true;
	}

	// Automatically archives old logs when they exceed the limit.
	private function maybe_rotate( $log_path ) {
		if ( ! file_exists( $log_path ) ) {
			return;
		}

		$size = @filesize( $log_path );
		if ( false === $size || $size < LAA_MAX_SIZE ) {
			return;
		}

		// Archive old file with a timestamp
		$archive = LAA_LOG_DIR . '/actions-' . date( 'Y-m-d-His' ) . '.log';
		@rename( $log_path, $archive );
	}

	/**
	 * Get the current timestamp in the configured local timezone.
	 * PERFORMANCE OPTIMIZATION: Uses a static DateTime object and cached DateTimeZone
	 * to prevent high-frequency object generation overhead.
	 */
	private function get_local_timestamp() {
		try {
			if ( null === $this->tz_object ) {
				$this->tz_object = new DateTimeZone( LAA_TIMEZONE );
			}
			
			$microtime = microtime( true );
			$seconds   = floor( $microtime );
			$micro     = sprintf( '%06d', ( $microtime - $seconds ) * 1000000 );
			
			static $dt = null;
			if ( null === $dt ) {
				$dt = new DateTime();
				$dt->setTimezone( $this->tz_object );
			}
			$dt->setTimestamp( $seconds );
			
			return $dt->format( 'Y-m-d H:i:s' ) . '.' . $micro;
		} catch ( Exception $e ) {
			return date( 'Y-m-d H:i:s' );
		}
	}

}

// Boot the logger
LAA_Action_Logger::init();

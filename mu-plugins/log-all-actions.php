<?php
/**
 * Plugin Name: Log All Actions
 * Description: Logs all actions and filters fired during a page load to wp-content/logs/actions.log.
 * Version: 1.0.0
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

	
	// Get (or create) the singleton instance and start logging.
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// Constructor — sets up hooks.
	private function __construct() {
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
		// Remove the microseconds for the header, just grab the date/time portion
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
		// Get the current action or filter name
		$hook_name = current_filter();

		// Prevent infinite loop if we capture our own shutdown handler
		if ( 'shutdown' === $hook_name && doing_action( 'shutdown' ) ) {
			return;
		}

		$this->counter++;

		$timestamp = $this->get_local_timestamp();

		// Number of arguments passed to this hook
		$num_args = func_num_args();

		// Elapsed time since request start in milliseconds
		$elapsed = round( ( microtime( true ) - $this->start_time ) * 1000, 2 );

		// Add a nicely padded row to our buffer array
		$this->buffer[] = sprintf(
			'[%s] #%04d | +%8sms | %-40s | args: %d',
			$timestamp,
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
	 */
	private function get_local_timestamp() {
		try {
			$tz = new DateTimeZone( LAA_TIMEZONE );
			$dt = new DateTime( 'now', $tz );
			
			// Extract microseconds
			$microtime = microtime( true );
			$micro = sprintf( '%06d', ( $microtime - floor( $microtime ) ) * 1000000 );
			
			return $dt->format( 'Y-m-d H:i:s' ) . '.' . $micro;
		} catch ( Exception $e ) {
			// Fallback to default UTC if timezone string is invalid
			return date( 'Y-m-d H:i:s' );
		}
	}

}


// Boot the logger
LAA_Action_Logger::init();

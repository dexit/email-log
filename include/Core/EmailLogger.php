<?php namespace EmailLog\Core;

/**
 * Log's emails sent through `wp_mail`.
 *
 * @package EmailLog\Core
 * @since   2.0
 */
class EmailLogger {

	/**
	 * Load the logger.
	 */
	public function load() {
		add_filter( 'wp_mail', array( $this, 'log_email' ) );
	}

	/**
	 * Logs email to database.
	 *
	 * @param array $mail_info Information about email.
	 *
	 * @return array Information about email.
	 */
	public function log_email( $mail_info ) {
		$email_log = email_log();

		$data = array(
			'attachments' => ( count( $mail_info['attachments'] ) > 0 ) ? 'true' : 'false',
			'to_email'    => is_array( $mail_info['to'] ) ? implode( ',', $mail_info['to'] ) : $mail_info['to'],
			'subject'     => $mail_info['subject'],
			'headers'     => is_array( $mail_info['headers'] ) ? implode( "\n", $mail_info['headers'] ) : $mail_info['headers'],
			'sent_date'   => current_time( 'mysql' ),
		);

		$message = '';

		if ( isset( $mail_info['message'] ) ) {
			$message = $mail_info['message'];
		} else {
			// wpmandrill plugin is changing "message" key to "html". See https://github.com/sudar/email-log/issues/20
			// Ideally this should be fixed in wpmandrill, but I am including this hack here till it is fixed by them.
			if ( isset( $mail_info['html'] ) ) {
				$message = $mail_info['html'];
			}
		}

		$data['message'] = $message;

		$email_log->table_manager->insert_log( $data );

		return $mail_info;
	}
}
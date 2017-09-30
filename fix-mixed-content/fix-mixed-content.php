<?php

/*
Plugin Name: Fix Mixed Content
Description: This plugin will automatically fix and warn you of mixed content errors.
Version: 1.0
Author: trentonmaki
License: GPL2
*/

function fix_mc_make_settings() {
	$option = [
		"fix_mc_add_http"          => true,
		"fix_mc_add_meta"          => false,
		"fix_mc_add_javascript"    => true,
		"fix_mc_add_compatibility" => true,
		"fix_mc_warn"              => true,
		"fix_mc_report_email"      => false,
	];

	add_option( 'fix_mc_options', $option );
}

register_activation_hook( __FILE__, "fix_mc_make_settings" );


require( "fix-mixed-content-settings-page.php" );


add_filter( 'wp_headers', 'fix_mc_add_header_csp' );

/*
 * Modify HTTP header
 */
function fix_mc_add_header_csp( $headers ) {
	$option           = get_option( FixMC_SettingsPage::OPTION_NAME );
	$addHTTP          = isset( $option["fix_mc_add_http"] ) ? $option["fix_mc_add_http"] : false;
	$addCompatibility = isset( $option["fix_mc_add_compatibility"] ) ? $option["fix_mc_add_compatibility"] : false;
	$warn             = isset( $option["fix_mc_warn"] ) ? $option["fix_mc_warn"] : false;

	if ( ! is_admin() && $addHTTP ) {
		if ( $warn ) {
			$headers["Report-To"]                           = json_encode( [
				"url"     => get_home_url() . "/fix_mc_report",
				"group"   => "fix-mc-endpoint",
				"max-age" => 10886400
			] );
			$headers['Content-Security-Policy']             = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
			$headers['Content-Security-Policy-Report-Only'] = "default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
		} else {
			$headers['Content-Security-Policy'] = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:";

		}
		if ( $addCompatibility ) {
			if ( $warn ) {
				$headers['X-WebKit-CSP']                          = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
				$headers['X-Content-Security-Policy']             = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
				$headers['X-Content-Security-Policy-Report-Only'] = "default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
				$headers['X-WebKit-CSP-Report-Only']              = "default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint";
			} else {
				$headers['X-WebKit-CSP']              = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:";
				$headers['X-Content-Security-Policy'] = "upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:";
			}
		}
	}

	return $headers;
}


add_action( 'wp_head', 'fix_mc_add_meta_csp' );

function fix_mc_add_meta_csp() {
	$option           = get_option( FixMC_SettingsPage::OPTION_NAME );
	$addMeta          = isset( $option["fix_mc_add_meta"] ) ? $option["fix_mc_add_meta"] : false;
	$addCompatibility = isset( $option["fix_mc_add_compatibility"] ) ? $option["fix_mc_add_compatibility"] : false;
	$warn             = isset( $option["fix_mc_warn"] ) ? $option["fix_mc_warn"] : false;

	if ( $addMeta && false ) {
		if ( $warn ) {
			?>
            <meta equiv="Report-To"
                  content="<?php echo json_encode( [
				      "url"     => get_home_url() . "/fix_mc_report",
				      "group"   => "fix-mc-endpoint",
				      "max-age" => 10886400
			      ] ) ?>">
            <meta http-equiv="Content-Security-Policy"
                  content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
            <meta http-equiv="Content-Security-Policy-Report-Only"
                  content="default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
			<?php if ( $addCompatibility ) { ?>
                <meta http-equiv="X-WebKit-CSP"
                      content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
                <meta http-equiv="X-Content-Security-Policy"
                      content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
                <meta http-equiv="X-Content-Security-Policy-Report-Only"
                      content="default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
                <meta http-equiv="X-WebKit-CSP-Report-Only"
                      content="default-src https: 'unsafe-inline' 'unsafe-eval' data:; report-uri /fix_mc_report; report-to fix-mc-endpoint">
				<?php
			}
		} else {
			?>
            <meta http-equiv="Content-Security-Policy"
                  content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:">
			<?php if ( $addCompatibility ) { ?>
                <meta http-equiv="X-WebKit-CSP"
                      content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:">
                <meta http-equiv="X-Content-Security-Policy"
                      content="upgrade-insecure-requests; default-src https: https: 'unsafe-inline' 'unsafe-eval' data:">
				<?php
			}
		}
	}
}


add_action( 'wp_enqueue_scripts', 'fix_mc_add_check_security_violation_js' );

function fix_mc_add_check_security_violation_js() {
	$option = get_option( FixMC_SettingsPage::OPTION_NAME );
	$addJS  = isset( $option["fix_mc_add_javascript"] ) ? $option["fix_mc_add_javascript"] : false;

	if ( $addJS ) {
		wp_enqueue_script( 'check_security_violation', plugin_dir_url( __FILE__ ) . 'check_security_violation.js', null, time(), true );
	}
}


add_action( 'parse_request', 'fix_mc_report_violation' );

function fix_mc_report_violation() {
	if ( $_SERVER["REQUEST_URI"] == '/fix_mc_report' ) {
		$option = get_option( FixMC_SettingsPage::OPTION_NAME );
		$warn   = isset( $option["fix_mc_warn"] ) ? $option["fix_mc_warn"] : false;
		if ( $warn ) {
			$json_data = json_decode( file_get_contents( 'php://input' ), true );


			$to      = isset( $option["fix_mc_report_email"] ) ? $option["fix_mc_report_email"] : get_option( "admin_email" );
			$subject = 'MIXED CONTENT VIOLATION ON ' . get_home_url();
			$body    = 'A mixed content violation was received on your site: <br/>' .
			           'Blocked URI: ' . $json_data["csp-report"]["blocked-uri"] . '<br/>' .
			           'Found on page: ' . $json_data["csp-report"]["document-uri"] . '<br/>' .
			           'At line: ' . $json_data["csp-report"]["line-number"] . '<br/>' .
			           'We attempted to automatically set the URL to https://, but you should still fix this immediately';
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );

			exit();
		}
	}
}


<?php
/**
 * GitHub webhook — auto-deploy ved push.
 *
 * Nordicway-setup:
 * 1) Generér en secret: `openssl rand -hex 32` (på din Mac)
 * 2) Tilføj til wp-config.php:
 *      define( 'S247_DEPLOY_SECRET', 'din-lange-secret-her' );
 * 3) GitHub → repo Settings → Webhooks → Add webhook:
 *      Payload URL: https://s247.dk/wp-content/themes/studie247/deploy.php
 *      Content type: application/json
 *      Secret: den secret du genererede
 *      Events: Just the push event
 *      Active: ✓
 *
 * Sikkerhed:
 * - HMAC SHA-256 verificeres (afviser requests uden gyldig signatur)
 * - Kun POST tilladt
 * - Pull'er kun den branch der er sat på serveren (beskytter mod
 *   uønsket branch-switch)
 * - Logger til deploy.log i theme-roden
 *
 * @package Studie247
 */

// Loader wp-config så vi har S247_DEPLOY_SECRET uden at boote hele WP.
$wp_root = __DIR__;
for ( $i = 0; $i < 5; $i++ ) {
	if ( file_exists( $wp_root . '/wp-config.php' ) ) {
		// Load kun den relevante define — ikke hele wp-config.
		$config = file_get_contents( $wp_root . '/wp-config.php' );
		if ( preg_match( "/define\(\s*['\"]S247_DEPLOY_SECRET['\"]\s*,\s*['\"]([^'\"]+)['\"]/", $config, $m ) ) {
			define( 'S247_DEPLOY_SECRET', $m[1] );
		}
		break;
	}
	$wp_root = dirname( $wp_root );
}

$theme_dir = __DIR__;
$log_file  = $theme_dir . '/deploy.log';

function s247_deploy_log( $msg, $log_file ) {
	@file_put_contents( $log_file, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $msg . "\n", FILE_APPEND );
}

if ( ! defined( 'S247_DEPLOY_SECRET' ) || ! S247_DEPLOY_SECRET ) {
	http_response_code( 503 );
	s247_deploy_log( 'DENIED: S247_DEPLOY_SECRET ikke defineret i wp-config.php', $log_file );
	exit( 'deploy secret not configured' );
}

if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
	http_response_code( 405 );
	exit( 'method not allowed' );
}

$payload   = file_get_contents( 'php://input' );
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ( ! $signature || ! str_starts_with( $signature, 'sha256=' ) ) {
	http_response_code( 401 );
	s247_deploy_log( 'DENIED: manglende eller forkert signatur-format', $log_file );
	exit( 'missing signature' );
}

$expected = 'sha256=' . hash_hmac( 'sha256', $payload, S247_DEPLOY_SECRET );
if ( ! hash_equals( $expected, $signature ) ) {
	http_response_code( 401 );
	s247_deploy_log( 'DENIED: signatur mismatch', $log_file );
	exit( 'invalid signature' );
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ( 'ping' === $event ) {
	http_response_code( 200 );
	s247_deploy_log( 'PING modtaget — webhook aktiv', $log_file );
	exit( 'pong' );
}
if ( 'push' !== $event ) {
	http_response_code( 200 );
	exit( 'ignored event: ' . $event );
}

$data     = json_decode( $payload, true );
$pushed   = str_replace( 'refs/heads/', '', (string) ( $data['ref'] ?? '' ) );
$commit   = (string) ( $data['head_commit']['id']      ?? '' );
$message  = (string) ( $data['head_commit']['message'] ?? '' );
$pusher   = (string) ( $data['pusher']['name']         ?? '' );

// Find nuværende branch på serveren.
chdir( $theme_dir );
$current = trim( (string) shell_exec( 'git rev-parse --abbrev-ref HEAD 2>&1' ) );

if ( $pushed !== $current ) {
	s247_deploy_log( sprintf( 'SKIP: push til "%s" matcher ikke server-branch "%s"', $pushed, $current ), $log_file );
	http_response_code( 200 );
	exit( 'not my branch' );
}

$fetch = shell_exec( 'git fetch origin ' . escapeshellarg( $current ) . ' 2>&1' );
$pull  = shell_exec( 'git reset --hard ' . escapeshellarg( 'origin/' . $current ) . ' 2>&1' );

s247_deploy_log( sprintf(
	"DEPLOY OK — branch=%s commit=%s pusher=%s\n  msg: %s\n  fetch: %s\n  pull: %s",
	$current, substr( $commit, 0, 7 ), $pusher,
	trim( explode( "\n", $message )[0] ),
	trim( $fetch ?? '' ),
	trim( $pull ?? '' )
), $log_file );

http_response_code( 200 );
echo 'deployed ' . substr( $commit, 0, 7 );

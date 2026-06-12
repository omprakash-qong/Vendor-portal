<?php

/*
 * Vercel serverless entry point for the Laravel app.
 *
 * Vercel's filesystem is read-only except for /tmp, so we point Laravel's
 * compiled-view path there before booting. Set the rest of the writable-state
 * drivers via env vars in the Vercel dashboard (see notes below):
 *
 *   SESSION_DRIVER=cookie
 *   CACHE_STORE=array
 *   QUEUE_CONNECTION=sync
 *   LOG_CHANNEL=stderr
 *   VIEW_COMPILED_PATH=/tmp/views
 *
 * Plus APP_KEY and an EXTERNAL database (DB_*) — Vercel has no MySQL.
 */

$compiled = '/tmp/views';
if (!is_dir($compiled)) {
    @mkdir($compiled, 0755, true);
}
putenv("VIEW_COMPILED_PATH={$compiled}");
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $compiled;

require __DIR__ . '/../public/index.php';

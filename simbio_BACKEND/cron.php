<?php
// cron.php – Conversió de vídeos a qualitat web (<20MB)
// Execució cada 10 minuts

require_once __DIR__ . '/includes/logger.php';

error_reporting(E_ALL);

/***********************
 * CONFIG
 ***********************/
$uploadsDir = __DIR__ . '/uploads';
$maxSize    = 20 * 1024 * 1024; // 20MB
$ffmpeg     = '/usr/bin/ffmpeg';
$lockFile   = __DIR__ . '/cron.lock';

/***********************
 * FUNCIONS
 ***********************/
function cron_out(string $msg): void {
    if (PHP_SAPI === 'cli') {
        echo $msg . PHP_EOL;
    }
}

function cron_is_function_disabled(string $fn): bool {
    $disabled = ini_get('disable_functions');
    if (!$disabled) return false;
    return in_array($fn, array_map('trim', explode(',', $disabled)), true);
}

/***********************
 * LOCK (evitar doble execució)
 ***********************/
if (file_exists($lockFile)) {
    log_warning('CRON: Ja s’està executant');
    exit;
}
file_put_contents($lockFile, getmypid());
register_shutdown_function(fn() => @unlink($lockFile));

/***********************
 * VALIDACIONS
 ***********************/
log_info('CRON: Inici');
cron_out('CRON: Inici');

if (!is_dir($uploadsDir)) {
    log_error('CRON: uploads no existeix', ['dir' => $uploadsDir]);
    exit;
}

if (!function_exists('exec') || cron_is_function_disabled('exec')) {
    log_error('CRON: exec() deshabilitat');
    exit;
}

if (!is_executable($ffmpeg)) {
    log_error('CRON: ffmpeg no executable', ['path' => $ffmpeg]);
    exit;
}

/***********************
 * PROCESSAR VÍDEOS
 ***********************/
$videos = glob($uploadsDir . '/*.mp4');

if (!$videos) {
    log_info('CRON: No hi ha vídeos');
    cron_out('CRON: No hi ha vídeos');
    exit;
}

$processed = 0;
$skipped   = 0;
$errors    = 0;

foreach ($videos as $video) {

    // Saltar vídeos petits
    if (filesize($video) <= $maxSize) {
        $skipped++;
        continue;
    }

    // Evitar vídeos fallits
    if (str_ends_with($video, '.failed.mp4')) {
        continue;
    }

    $tmp = $video . '.tmp.mp4';

    log_info("CRON: Convertint $video");
    cron_out("CRON: Convertint $video");

    $cmd = "$ffmpeg -i " . escapeshellarg($video) .
        " -vf scale=1280:-2 -c:v libx264 -preset fast -crf 28 " .
        " -c:a aac -b:a 96k -movflags +faststart " .
        escapeshellarg($tmp) . " -y 2>&1";

    exec($cmd, $output, $code);

    if ($code !== 0 || !file_exists($tmp)) {
        $errors++;
        log_error("CRON: Error convertint $video", ['output' => $output]);
        if (file_exists($tmp)) unlink($tmp);
        rename($video, $video . '.failed.mp4');
        continue;
    }

    if (filesize($tmp) <= $maxSize) {
        unlink($video);
        rename($tmp, $video);
        $processed++;
        log_info("CRON: OK $video");
        cron_out("CRON: OK $video");
    } else {
        unlink($tmp);
        rename($video, $video . '.failed.mp4');
        $errors++;
        log_error("CRON: Vídeo >20MB $video");
    }
}

log_info('CRON: Fi', [
    'processed' => $processed,
    'skipped'   => $skipped,
    'errors'    => $errors
]);

cron_out("CRON: Fi (processed=$processed skipped=$skipped errors=$errors)");

<?php

declare(strict_types=1);

if (!isset($argv[1])) {
    printf("Usage: %s <url> <out>\n", $argv[0]);
    exit;
}

$contents = file_get_contents(stripslashes($argv[1]));

$matches = [];
preg_match('/<script>window\.__DUMPERT_STATE__ = JSON.parse\("(.*)"\);window.*<\/script>/', $contents, $matches);

$state = json_decode(stripslashes($matches[1]), true);

var_dump($state['items']);

$i = 1;
foreach ($state['items']['item']['item']['media'] as $media) {
    $video = $media['variants'][0]['uri'];
    if (substr($video, -4) === 'm3u8') {
        $out = 'files/' . $state['items']['item']['item']['id'] . '_' . $i++ . '.mp4';
	shell_exec("ffmpeg -protocol_whitelist file,http,https,tcp,tls -i {$video} -c copy {$out}");
    } else {
        $out = $argv[2] ?? 'files/' . basename($video);
        printf("Saving to %s... ", $out); 
        file_put_contents($out, file_get_contents($video));
    }
}

printf("Done!\n");

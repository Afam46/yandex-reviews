<?php

namespace App\Services;

class YandexReviewParser
{
    public function parse(string $url): array
    {
        $script = base_path(
            'app/scripts/yandex-reviews.cjs'
        );

        $command =
            'node '
            . escapeshellarg($script)
            . ' '
            . escapeshellarg($url)
            . ' 2>&1';

        $output = shell_exec($command);

        if (!$output) {
            throw new \Exception(
                'Parser failed'
            );
        }

        $json = json_decode(
            $output,
            true
        );

        if (
            json_last_error() !==
            JSON_ERROR_NONE
        ) {
            throw new \Exception(
                $output
            );
        }

        return $json;
    }
}
<?php

declare(strict_types=1);

namespace CS\Service;

use CS\Application;
use CS\Singleton;

class WebFontHelper extends Singleton
{
    const API_BASE = 'https://google-webfonts-helper.herokuapp.com/api/fonts';

    protected static $_instance = null;

    public function getFonts()
    {
        $key = '_cs_local_fonts';

        if ($cache = get_transient($key)) {
            return json_decode($cache, true);
        }

        $response = wp_remote_get(self::API_BASE);

        if ($response['response']['code'] != 200) {
            return [];
        }

        set_transient($key, $response['body'], 60 * 60 * 24 * 14);

        return json_decode($response['body'], true);
    }

    public static function mirror($oldValue, $value)
    {
        return static::downloadFiles($value);
    }

    public static function downloadFiles($config)
    {
        $app = Application::getInstance();

        $localDirectory = $app->getLocalDirectory();

        $localCss = sprintf('%s/local-fonts.css', $localDirectory);

        $css = '';

        foreach ($config as $font => $enabled) {
            if (!$enabled) {
                continue;
            }

            $response = wp_remote_get(sprintf('%s/%s', self::API_BASE, $font));

            if (($response['response']['code'] ?? '') != 200) {
                continue;
            }

            $response = json_decode($response['body'] ?? '', true);

            foreach ($response['variants'] ?? [] as $variant) {
                $identifier = sprintf(
                    '%s-%s-%s',
                    $response['id'] ?? '',
                    $variant['fontStyle'] ?? '',
                    $variant['fontWeight'] ?? ''
                );

                $localFiles = [];

                foreach (['eot', 'woff', 'woff2', 'ttf', 'svg'] as $fileType) {
                    $localFile = sprintf('%s/%s.%s', $localDirectory, $identifier, $fileType);

                    $localFiles[$fileType] = sprintf('%s.%s', $identifier, $fileType);

                    if (!file_exists($localFile) && isset($variant[$fileType])) {
                        file_put_contents($localFile, file_get_contents($variant[$fileType]));
                    }
                }

                $fontFamily = $variant['fontFamily'] ?? '';
                $fontFamily = str_replace('\'', '', $fontFamily);

                $fontStyle = $variant['fontStyle'] ?? '';
                $fontWeight = $variant['fontWeight'] ?? '';

                $eot = $app->getLocalDirectoryUrl($localFiles['eot']);
                $woff2 = $app->getLocalDirectoryUrl($localFiles['woff2']);
                $woff = $app->getLocalDirectoryUrl($localFiles['woff']);
                $ttf = $app->getLocalDirectoryUrl($localFiles['ttf']);
                $svg = $app->getLocalDirectoryUrl($localFiles['svg']);

                $css .= <<<CSS
@font-face { 
    font-family: "$fontFamily"; font-style: $fontStyle; font-weight: $fontWeight;
    src: url("$eot"); /* IE9 Compat Modes */
    src: url("$eot?#iefix") format("embedded-opentype"), /* IE6-IE8 */
        url("$woff2") format("woff2"), /* Super Modern Browsers */
        url("$woff") format("woff"), /* Pretty Modern Browsers */
        url("$ttf")  format("truetype"), /* Safari, Android, iOS */
        url("$svg#svgFontName") format("svg"); /* Legacy iOS */ }

CSS;
            }
        }

        file_put_contents($localCss, $css);

        return $localCss;
    }
}
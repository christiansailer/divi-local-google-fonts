<?php

declare(strict_types=1);

namespace CS\Admin\View;

use CS\Application;

class FontList
{
    const REQUIRED_CAPABILITY = 'manage_options';

    const OPTION_NAME = 'cs_options';

    public function registerSettings()
    {
        register_setting( 'cs_common', 'cs_local_fonts');
        register_setting( 'cs_common', 'cs_local_font_enabled');

        // COMMON SECTION
        add_settings_section(
            'cs_section_font_common',
            __( 'Konfiguration', CS_LOCAL_FONT_TEXT_DOMAIN),
            null,
            'cs'
        );

        add_settings_field(
            'cs_field_local_font_enabled',
            __( 'Lokale Fonts aktiviert', CS_LOCAL_FONT_TEXT_DOMAIN),
            function ($args) {
                $optionName = 'cs_local_font_enabled';

                echo sprintf(
                        '<input type="checkbox" id="%s" name="%s" value="1" %s />',
                    $args['label_for'] ?? '',
                    $optionName,
                    get_option($optionName) ? 'checked="checked"' : ''

                );
            },
            'cs',
            'cs_section_font_common',
            [
                'label_for' => 'cs_field_local_font_enabled',
            ]
        );

        // FONT LIST SECTION
        add_settings_section(
            'cs_section_font_list',
            __( 'Bitte verwendete Schriftarten wählen.', CS_LOCAL_FONT_TEXT_DOMAIN),
            null,
            'cs'
        );

        $fonts = $this->getFonts();

        foreach ($fonts as $font) {
            add_settings_field(
                $font['id'],
                $font['family'],
                function ($args) {
                    $optionName = 'cs_local_fonts';
                    $options = get_option($optionName);

                    echo sprintf(
                        '<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />',
                        $args['label_for'] ?? '',
                        $optionName,
                        $args['label_for'] ?? '',
                        ($options[$args['label_for']] ?? '') ? 'checked="checked"' : ''
                    );
                },
                'cs',
                'cs_section_font_list',
                [
                    'label_for' => $font['id'],
                ]
            );
        }

        add_action('update_option_cs_local_fonts', [$this, 'mirror'], 10, 2);
    }

    public function adminMenuHook()
    {
        add_menu_page(__('Lokale Fonts', CS_LOCAL_FONT_TEXT_DOMAIN), 'Profile', self::REQUIRED_CAPABILITY, __FILE__, 'profile');

        $hook = add_submenu_page(
            __FILE__,
            __('Schriftarten', CS_LOCAL_FONT_TEXT_DOMAIN),
            __('Schriftarten', CS_LOCAL_FONT_TEXT_DOMAIN),
            self::REQUIRED_CAPABILITY,
            'cs-font-list',
            [$this, 'render']
        );
    }

    public function render()
    {
        if (!current_user_can( self::REQUIRED_CAPABILITY) ) {
            return;
        }

        // wordpress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'cs_messages', 'cs_message', __( 'Änderungen gespeichert', CS_LOCAL_FONT_TEXT_DOMAIN), 'updated' );
        }

        // show error/update messages
        settings_errors( 'cs_messages' );
        ?>

        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                submit_button(__('speichern', CS_LOCAL_FONT_TEXT_DOMAIN));

                // output security fields for the registered setting "wporg"
                settings_fields('cs_common');

                // output setting sections and their fields
                // (sections are registered for "wporg", each field is registered to a specific section)
                do_settings_sections('cs');

                // output save settings button
                submit_button(__('speichern', CS_LOCAL_FONT_TEXT_DOMAIN));
                ?>
            </form>
        </div>
        <?php
    }

    protected function getFonts()
    {
        $key = '_cs_local_fonts';

        if ($cache = get_transient($key)) {
            return json_decode($cache, true);
        }

        $response = wp_remote_get('https://google-webfonts-helper.herokuapp.com/api/fonts');

        if ($response['response']['code'] != 200) {
            return [];
        }

        set_transient($key, $response['body'], 60 * 60 * 24 * 14);

        return json_decode($response['body'], true);
    }

    public function mirror($oldValue, $value)
    {
        $fonts = array_map(function($v){
            return str_replace('cs_field_font_', '', $v);
        }, array_keys($value));

        $app = Application::getInstance();

        $localDirectory = $app->getLocalDirectory();

        $localCss = sprintf('%s/local-fonts.css', $localDirectory);

        $css = '';

        foreach ($fonts as $font) {
            $response = wp_remote_get(sprintf('https://google-webfonts-helper.herokuapp.com/api/fonts/%s', $font));

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
    }
}
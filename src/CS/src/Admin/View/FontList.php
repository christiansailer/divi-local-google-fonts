<?php

declare(strict_types=1);

namespace CS\Admin\View;

use CS\Admin\Admin;
use CS\Service\WebFontHelper;

class FontList
{
    const SLUG = 'cs-local-font-list';

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

        foreach (WebFontHelper::getInstance()->getFonts() as $font) {
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

        add_action('update_option_cs_local_fonts', [\CS\Service\WebFontHelper::class, 'mirror'], 10, 2);
    }

    public function adminMenuHook()
    {
        add_submenu_page(
            Admin::PARENT_MENU_SLUG,
            __('Schriftarten', CS_LOCAL_FONT_TEXT_DOMAIN),
            __('Schriftarten', CS_LOCAL_FONT_TEXT_DOMAIN),
            'manage_options',
            self::SLUG,
            [$this, 'render']
        );
    }

    public function render()
    {
        if (!current_user_can( 'manage_options')) {
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
}
<?php

declare(strict_types=1);

namespace CS\Admin\View;

use CS\Admin\Admin;
use CS\Service\WebFontHelper;

class RebuildCss
{
    const SLUG = 'cs-local-font-rebuild';

    public function registerSettings()
    {
    }

    public function adminMenuHook()
    {
        add_submenu_page(
            Admin::PARENT_MENU_SLUG,
            __('CSS neu erzeugen', CS_LOCAL_FONT_TEXT_DOMAIN),
            __('CSS neu erzeugen', CS_LOCAL_FONT_TEXT_DOMAIN),
            'manage_options',
            self::SLUG,
            [$this, 'rebuild']
        );
    }

    public function rebuild()
    {
        if (!current_user_can( 'manage_options') ) {
            return;
        }

        $options = get_option('cs_local_fonts');

        $cssFile = WebFontHelper::downloadFiles($options);

        echo sprintf('<h1>%s</h1>', __('Generiertes CSS', CS_LOCAL_FONT_TEXT_DOMAIN));
        echo '<pre style="background-color: white">' . file_get_contents($cssFile) . '</pre>';
    }
}
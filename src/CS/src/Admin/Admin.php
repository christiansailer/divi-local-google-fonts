<?php

declare(strict_types=1);

namespace CS\Admin;

use CS\Admin\View\FontList;

class Admin
{
    const PARENT_MENU_SLUG = FontList::SLUG;

    protected $adminPages = null;

    public function adminMenuHook()
    {
        add_menu_page(
            __('Lokale Fonts', CS_LOCAL_FONT_TEXT_DOMAIN),
            __('Lokale Fonts', CS_LOCAL_FONT_TEXT_DOMAIN),
            'manage_options',
            self::PARENT_MENU_SLUG
        );

        if ($this->adminPages === null) {
            $this->initPages();
        }

        foreach ($this->adminPages as $page) {
            $page->adminMenuHook();
        }
    }

    public function adminInitHook()
    {
        if ($this->adminPages === null) {
            $this->initPages();
        }

        foreach ($this->adminPages as $page) {
            $page->registerSettings();
        }
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminMenuHook']);
        add_action('admin_init', [$this, 'adminInitHook']);
    }

    protected function initPages()
    {
        $this->adminPages = [];
        $this->adminPages[] = new \CS\Admin\View\FontList();
        $this->adminPages[] = new \CS\Admin\View\RebuildCss();

        return $this->adminPages;
    }
}
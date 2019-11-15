<?php

declare(strict_types=1);

namespace CS\Admin;

class Admin
{
    protected $adminPages = null;

    public function adminMenuHook()
    {
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

        return $this->adminPages;
    }
}
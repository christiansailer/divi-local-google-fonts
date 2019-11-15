<?php

declare(strict_types=1);

namespace CS;

class Application extends Singleton
{
    protected static $_instance = null;

    public function init()
    {
        register_activation_hook(CS_LOCAL_FONT_FILE, [$this, 'activate']);
        register_deactivation_hook(CS_LOCAL_FONT_FILE, [$this, 'deactivate']);

        if (is_admin()) {
            $admin = new Admin\Admin();
        }

        if (get_option('cs_local_font_enabled', false)) {
            add_action( 'wp_enqueue_scripts', function(){
                wp_dequeue_style( 'divi-fonts');
                wp_dequeue_style( 'extra-fonts');
                wp_dequeue_style( 'et-builder-googlefonts-css');

                wp_enqueue_style( 'dp-divi-dsgvo-css', sprintf('%s/local-fonts.css', $this->getLocalDirectoryUrl()));
            }, 20 );

            add_action( 'wp_enqueue_scripts', function(){
                wp_deregister_style( 'et-core-main-fonts' );
                wp_deregister_style( 'et-gf-open-sans' );
                wp_deregister_style( 'et-gf-open-sans-css' );
                wp_deregister_style( 'et-builder-googlefonts-cached' );
            }, 30 );
        }
    }

    public function activate()
    {

    }

    public function deactivate()
    {

    }

    public function getLocalDirectory()
    {
        $localDirectory = sprintf(
            '%s/local-fonts/',
            wp_get_upload_dir()['basedir'] ?? sys_get_temp_dir()
        );

        if (!is_dir($localDirectory)) {
            mkdir($localDirectory);
        }

        return $localDirectory;
    }

    public function getLocalDirectoryUrl($fileName = null)
    {
        return sprintf(
            '%s/local-fonts/%s',
            wp_get_upload_dir()['baseurl'] ?? '',
            $fileName
        );
    }
}
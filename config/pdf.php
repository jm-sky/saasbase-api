<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default PDF Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default PDF engine used by the application.
    | Supported engines: 'mpdf', 'puppeteer'
    |
    */

    'default' => env('PDF_ENGINE', 'puppeteer'),

    /*
    |--------------------------------------------------------------------------
    | PDF Engine Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each PDF engine.
    |
    */

    'engines' => [
        'mpdf' => [
            'driver' => 'mpdf',
            'config' => [
                'format'      => 'A4',
                'orientation' => 'P', // P for portrait, L for landscape
                'margins'     => [
                    'left'   => 10,
                    'right'  => 10,
                    'top'    => 10,
                    'bottom' => 15,
                ],
                'use_substitutions' => false,
                'simple_tables'     => false,
            ],
        ],

        'puppeteer' => [
            'driver' => 'puppeteer',
            'config' => [
                'format'      => 'A4',
                'orientation' => 'portrait', // portrait or landscape
                'margins'     => [
                    'top'    => '10mm',
                    'right'  => '10mm',
                    'bottom' => '15mm',
                    'left'   => '10mm',
                ],
                'print_background'      => true,
                'prefer_css_page_size'  => false,
                'display_header_footer' => true,
                'header_template'       => '<div></div>',
                'footer_template'       => '<div style="width: 100%; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #E5E7EB; padding-top: 5px;"><span class="date"></span> | ' . config('app.name', 'SaasBase') . ' | Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>',
                'timeout'               => 30000,
                'wait_for_selector'     => null,
                'wait_for_timeout'      => 0,
                'viewport'              => [
                    'width'  => 1200,
                    'height' => 800,
                ],
                'chrome_flags' => [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-gpu',
                    '--disable-extensions',
                    '--disable-plugins',
                    '--disable-web-security',
                    '--disable-features=VizDisplayCompositor',
                    '--disable-background-timer-throttling',
                    '--disable-backgrounding-occluded-windows',
                    '--disable-renderer-backgrounding',
                    '--disable-ipc-flooding-protection',
                    '--memory-pressure-off',
                    '--max_old_space_size=4096',
                    '--single-process',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Puppeteer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Puppeteer PDF engine when running in Docker.
    |
    */

    'puppeteer' => [
        'chrome_executable'  => env('PUPPETEER_CHROME_EXECUTABLE', '/usr/bin/google-chrome-stable'),
        'node_executable'    => env('PUPPETEER_NODE_EXECUTABLE', 'node'),
        'npm_executable'     => env('PUPPETEER_NPM_EXECUTABLE', 'npm'),
        'script_timeout'     => env('PUPPETEER_SCRIPT_TIMEOUT', 60),
        'temp_dir'           => env('PUPPETEER_TEMP_DIR', storage_path('app/temp')),
        'cleanup_temp_files' => env('PUPPETEER_CLEANUP_TEMP_FILES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global PDF Settings
    |--------------------------------------------------------------------------
    |
    | These settings apply to all PDF engines where applicable.
    |
    */

    'global' => [
        'add_footer'      => env('PDF_ADD_FOOTER', true),
        'footer_text'     => env('PDF_FOOTER_TEXT', null),
        'temp_cleanup'    => env('PDF_TEMP_CLEANUP', true),
        'debug_mode'      => env('PDF_DEBUG_MODE', true),
        'save_html_debug' => env('PDF_SAVE_HTML_DEBUG', false),
    ],
];

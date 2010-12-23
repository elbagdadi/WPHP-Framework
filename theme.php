<?php
/**
 * Used for the theme's initialization.
 */
class Theme {

    var $options = array(
        'name' => 'Theme',
        'slug' => 'theme',
        'types' => array(),
        'menus' => array()
    );

    function  __construct($options) {
        $this->options = $options + $this->options;
        define('THEME_NAME', $this->options['name']);
        define('THEME_SLUG', $this->options['slug']);

        define('THEME_DIR', get_template_directory());
        define('THEME_URL', get_template_directory_uri().'/');
        define('THEME_JS',THEME_URL.'js/');
        define('THEME_FRAMEWORK',ABSPATH . 'wp-content/themes/framework/');
        define('THEME_ADMIN',THEME_FRAMEWORK.'admin/');
        define('THEME_HELPERS',THEME_FRAMEWORK.'helpers/');
        define('THEME_OPTIONS',THEME_DIR.'/options/');
        define('THEME_TYPES',THEME_DIR.'/types/');
        
        add_action('after_setup_theme', array(&$this, 'supports'));
        add_action('init',array(&$this, 'language'));

        $this->supports();
        $this->menus();
        $this->types();
        $this->admin();
        $this->options();

        require(THEME_FRAMEWORK.'functions/functions.php');
    }

    function options(){
        global $theme_options;
        require(THEME_OPTIONS.'structure.php');
        $theme_options = array();
        foreach($menus as $m){
            foreach($m['pages'] as $name=>$page){
                $theme_options[$page] = get_option(THEME_SLUG.'_'.$page);
            }
        }
    }

    function menus(){
        add_theme_support('menus');
        register_nav_menus($this->options['menus']);
    }

    function types(){
        require(THEME_FRAMEWORK.'helpers/metas.php');
        foreach($this->options['types'] as $v){
            require(THEME_TYPES.$v.'.php');
        }
    }

    function supports() {
        if (function_exists('add_theme_support')) {
            add_theme_support('custom-header');
            add_theme_support('custom-background');
            add_theme_support('post-thumbnails', array('post', 'page', 'portfolio', 'slideshow'));
            add_theme_support('automatic-feed-links');
            add_theme_support('editor-style');
        }
    }

    function admin() {
        if (is_admin()) {
            require_once (THEME_FRAMEWORK . 'admin.php');
            $admin = new AdminTheme();
        }
    }

    function language(){
        if (is_admin()) {
            load_theme_textdomain( 'graf', THEME_ADMIN . '/languages/admin' );
        }else{
            load_theme_textdomain( 'graf', THEME_ADMIN . '/languages' );
        }
    }
    
}
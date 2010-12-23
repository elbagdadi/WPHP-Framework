<?php
class metas{
    
    var $conf = array(
	'id' => '',
	'title' => '',
	'pages' => '',
	'context' => '',
	'priority' => '',
    );
    var $options; 
    
    function metas($conf,$options){
        $this->conf     =      $conf + $this->conf;
        $this->options  =       $options;
        add_action('admin_init', array(&$this, 'create'));
	add_action('save_post', array(&$this, 'save'));
    }
    
    function create(){
        if (function_exists('add_meta_box')) {
            add_meta_box($this->conf['id'], $this->conf['title'], array(&$this, 'render'), $this->conf['page'], $this->conf['context'], $this->conf['priority']);
        }
    }
    
    function save($post_id){
        print_r($post_id); print_r($_POST); 
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
            return $post_id;
        }
        // Check permissions
        if ( 'page' == $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) )
            return $post_id;
        } else {
          if ( !current_user_can( 'edit_post', $post_id ) )
            return $post_id;
        }
        // Verify nonce 
        if (! wp_verify_nonce($_POST[$this->conf['id'] . '_noncename'], plugin_basename(__FILE__))) {
                return $post_id;
        }
        foreach($this->options as $v) {
            if (isset($v['id']) && ! empty($v['id'])) {
                $value = $_POST[$v['id']];
                if (get_post_meta($post_id, $v['id']) == "") {
                    add_post_meta($post_id, $v['id'], $value, true);
                } elseif ($value != get_post_meta($post_id, $v['id'], true)) {
                    update_post_meta($post_id, $v['id'], $value);
                } elseif ($value == "") {
                    delete_post_meta($post_id, $v['id'], get_post_meta($post_id, $v['id'], true));
                }
            }
        }
    }
    
    function render(){
        global $post; 
        foreach($this->options as $v) {
            $v['default'] = isset($v['default']) ? $v['default'] : '';
            if (isset($v['id'])) {
                $default = get_post_meta($post->ID, $v['id'], true);
                if ($default != "") {
                    $v['default'] = $default;
                }
            }
            $file = THEME_HELPERS.'metas/'.$v['type'].'.php';
            extract($v); 
            include($file);
        }
	echo '<input type="hidden" name="' . $this->conf['id'] . '_noncename" id="' . $this->conf['id'] . '_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    }
    
}
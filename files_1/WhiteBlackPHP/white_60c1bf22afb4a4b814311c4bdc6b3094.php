<?php

final class WP_Taxonomy
{
    public $name;
    public $label;
    public $labels;
    public $description = '';
    public $public = true;
    public $publicly_queryable = true;
    public $hierarchical = false;
    public $show_ui = true;
    public $show_in_menu = true;
    public $show_in_nav_menus = true;
    public $show_tagcloud = true;
    public $show_in_quick_edit = true;
    public $show_admin_column = false;
    public $meta_box_cb = null;
    public $meta_box_sanitize_cb = null;
    public $object_type = null;
    public $cap;
    public $rewrite;
    public $query_var;
    public $update_count_callback;
    public $show_in_rest;
    public $rest_base;
    public $rest_controller_class;
    public $rest_controller;
    public $default_term;
    public $sort = null;
    public $args = null;
    public $_builtin;
    public function __construct($taxonomy, $object_type, $args = array())
    {
        $this->name = $taxonomy;
        $this->set_props($object_type, $args);
    }
    public function set_props($object_type, $args)
    {
        $args = wp_parse_args($args);
        $args = apply_filters('register_taxonomy_args', $args, $this->name, (array) $object_type);
        $defaults = array('labels' => array(), 'description' => '', 'public' => true, 'publicly_queryable' => null, 'hierarchical' => false, 'show_ui' => null, 'show_in_menu' => null, 'show_in_nav_menus' => null, 'show_tagcloud' => null, 'show_in_quick_edit' => null, 'show_admin_column' => false, 'meta_box_cb' => null, 'meta_box_sanitize_cb' => null, 'capabilities' => array(), 'rewrite' => true, 'query_var' => $this->name, 'update_count_callback' => '', 'show_in_rest' => false, 'rest_base' => false, 'rest_controller_class' => false, 'default_term' => null, 'sort' => null, 'args' => null, '_builtin' => false);
        $args = array_merge($defaults, $args);
        if (null === $args['publicly_queryable']) {
            $args['publicly_queryable'] = $args['public'];
        }
        if (false !== $args['query_var'] && (is_admin() || false !== $args['publicly_queryable'])) {
            if (true === $args['query_var']) {
                $args['query_var'] = $this->name;
            } else {
                $args['query_var'] = sanitize_title_with_dashes($args['query_var']);
            }
        } else {
            $args['query_var'] = false;
        }
        if (false !== $args['rewrite'] && (is_admin() || get_option('permalink_structure'))) {
            $args['rewrite'] = wp_parse_args($args['rewrite'], array('with_front' => true, 'hierarchical' => false, 'ep_mask' => EP_NONE));
            if (empty($args['rewrite']['slug'])) {
                $args['rewrite']['slug'] = sanitize_title_with_dashes($this->name);
            }
        }
        if (null === $args['show_ui']) {
            $args['show_ui'] = $args['public'];
        }
        if (null === $args['show_in_menu'] || !$args['show_ui']) {
            $args['show_in_menu'] = $args['show_ui'];
        }
        if (null === $args['show_in_nav_menus']) {
            $args['show_in_nav_menus'] = $args['public'];
        }
        if (null === $args['show_tagcloud']) {
            $args['show_tagcloud'] = $args['show_ui'];
        }
        if (null === $args['show_in_quick_edit']) {
            $args['show_in_quick_edit'] = $args['show_ui'];
        }
        $default_caps = array('manage_terms' => 'manage_categories', 'edit_terms' => 'manage_categories', 'delete_terms' => 'manage_categories', 'assign_terms' => 'edit_posts');
        $args['cap'] = (object) array_merge($default_caps, $args['capabilities']);
        unset($args['capabilities']);
        $args['object_type'] = array_unique((array) $object_type);
        if (null === $args['meta_box_cb']) {
            if ($args['hierarchical']) {
                $args['meta_box_cb'] = 'post_categories_meta_box';
            } else {
                $args['meta_box_cb'] = 'post_tags_meta_box';
            }
        }
        $args['name'] = $this->name;
        if (null === $args['meta_box_sanitize_cb']) {
            switch ($args['meta_box_cb']) {
                case 'post_categories_meta_box':
                    $args['meta_box_sanitize_cb'] = 'taxonomy_meta_box_sanitize_cb_checkboxes';
                    break;
                case 'post_tags_meta_box':
                default:
                    $args['meta_box_sanitize_cb'] = 'taxonomy_meta_box_sanitize_cb_input';
                    break;
            }
        }
        if (!empty($args['default_term'])) {
            if (!is_array($args['default_term'])) {
                $args['default_term'] = array('name' => $args['default_term']);
            }
            $args['default_term'] = wp_parse_args($args['default_term'], array('name' => '', 'slug' => '', 'description' => ''));
        }
        foreach ($args as $property_name => $property_value) {
            $this->{$property_name} = $property_value;
        }
        $this->labels = get_taxonomy_labels($this);
        $this->label = $this->labels->name;
    }
    public function add_rewrite_rules()
    {
        global $wp;
        if (false !== $this->query_var && $wp) {
            $wp->add_query_var($this->query_var);
        }
        if (false !== $this->rewrite && (is_admin() || get_option('permalink_structure'))) {
            if ($this->hierarchical && $this->rewrite['hierarchical']) {
                $tag = '(.+?)';
            } else {
                $tag = '([^/]+)';
            }
            add_rewrite_tag("%{$this->name}%", $tag, $this->query_var ? "{$this->query_var}=" : "taxonomy={$this->name}&term=");
            add_permastruct($this->name, "{$this->rewrite['slug']}/%{$this->name}%", $this->rewrite);
        }
    }
    public function remove_rewrite_rules()
    {
        global $wp;
        if (false !== $this->query_var) {
            $wp->remove_query_var($this->query_var);
        }
        if (false !== $this->rewrite) {
            remove_rewrite_tag("%{$this->name}%");
            remove_permastruct($this->name);
        }
    }
    public function add_hooks()
    {
        add_filter('wp_ajax_add-' . $this->name, '_wp_ajax_add_hierarchical_term');
    }
    public function remove_hooks()
    {
        remove_filter('wp_ajax_add-' . $this->name, '_wp_ajax_add_hierarchical_term');
    }
    public function get_rest_controller()
    {
        if (!$this->show_in_rest) {
            return null;
        }
        $class = $this->rest_controller_class ? $this->rest_controller_class : WP_REST_Terms_Controller::class;
        if (!class_exists($class)) {
            return null;
        }
        if (!is_subclass_of($class, WP_REST_Controller::class)) {
            return null;
        }
        if (!$this->rest_controller) {
            $this->rest_controller = new $class($this->name);
        }
        if (!$this->rest_controller instanceof $class) {
            return null;
        }
        return $this->rest_controller;
    }
}

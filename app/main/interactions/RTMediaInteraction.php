<?php

/**
 * Sets up the routes and the context
 *
 * @author saurabh
 */
class RTMediaInteraction {

    public $context;
    private $slugs = array(
        RTMEDIA_MEDIA_SLUG,
        'upload'
    );
    public $routes;

    /**
     * Initialise the interaction
     */
    function __construct () {

        // hook into the WordPress Rewrite Endpoint API

        add_action ( 'init', array( $this, 'rewrite_rules' ) );
        add_action ( 'init', array( $this, 'rewrite_tags' ) );
        add_action ( 'init', array( $this, 'endpoint' ) );


        // set up interaction and routes
        add_action ( 'template_redirect', array( $this, 'init' ), 99 );
        
        add_filter ( 'wp_title', array( $this, 'set_title' ), 9999, 2 );
        add_filter ( 'wpseo_opengraph_title', array( $this, 'set_title' ), 9999, 1 );
        add_filter ( 'wpseo_opengraph', array( $this, 'rtmedia_wpseo_og_image' ), 999, 1 );
        add_filter ( 'wpseo_opengraph_url', array( $this, 'rtmedia_wpseo_og_url' ), 999, 1 );
    }

    function init () {
        // set up the routes array
        $this->route_slugs ();
        $this->set_context ();
        $this->set_routers ();
        $this->set_query ();
    }

    /**
     * Set up the route slugs' array
     */
    function route_slugs () {

        // filter to add custom slugs for routes
        $this->slugs = apply_filters ( 'rtmedia_default_routes', $this->slugs );
    }

    static function rewrite_rules () {
        add_rewrite_rule ( '^/' . RTMEDIA_MEDIA_SLUG . '/([0-9]*)/([^/]*)/?', 'index.php?media_id=$matches[1]&action=$matches[2]', 'bottom' );
        add_rewrite_rule ( '^/' . RTMEDIA_MEDIA_SLUG . '/([0-9]*)/pg/([0-9]*)/?', 'index.php?media_id=$matches[1]&pg=$matches[2]', 'bottom' );
        add_rewrite_rule ( '^/' . RTMEDIA_MEDIA_SLUG . '/nonce/([^/]*)/?', 'index.php?nonce_type=$matches[1]', 'bottom' );
        add_rewrite_rule ( '^/' . RTMEDIA_MEDIA_SLUG . '/([A-Za-z]*)/pg/([0-9]*)/?', 'index.php?media_type=$matches[1]&pg=$matches[2]', 'bottom' );
        add_rewrite_rule ( '^/' . RTMEDIA_MEDIA_SLUG . '/pg/([0-9]*)/?', 'index.php?pg=$matches[1]', 'bottom' );
    }

    static function rewrite_tags () {
        add_rewrite_tag ( '%media_id%', '([0-9]*)' );
        add_rewrite_tag ( '%action%', '([^/]*)' );
        add_rewrite_tag ( '%nonce_type%', '([^/]*)' );
        add_rewrite_tag ( '%media_type%', '([A-Za-z]*)' );
        add_rewrite_tag ( '%pg%', '([0-9]*)' );
    }

    /**
     * Just adds the current /{slug}/ to the rewite endpoint
     */
    function endpoint () {

        foreach ( $this->slugs as $slug ) {
            add_rewrite_endpoint ( $slug, EP_ALL );
        }
    }

    function set_routers () {


        // set up routes for each slug
        foreach ( $this->slugs as $slug ) {
            $this->routes[ $slug ] = new RTMediaRouter ( $slug );
        }
    }

    /**
     * Sets up the default context
     */
    function default_context () {

        return new RTMediaContext();
    }

    /**
     *
     * @param array $context the context array
     * @todo the method should also allow objects
     */
    function set_context ( $context = false ) {

        // take the context supplied and replace the context
        if ( is_array ( $context ) && isset ( $context[ 'type' ] ) && isset ( $context[ 'id' ] ) ) {

            $context_object->type = $context[ 'type' ];
            $context_object->id = $context[ 'id' ];

            // if there is no context array supplied, set the default context
        } else {

            $context_object = $this->default_context ();
        }



        //set the context property

        $this->context = $context_object;
    }

    /**
     * Reset the context to the default context after temporarily setting it,
     * Say, for an upload
     */
    function rewind_context () {

        $this->context = $this->default_context ();
    }

    function set_query () {
        global $rtmedia_query;

        $args = array(
            'context' => $this->context->type,
            'context_id' => $this->context->id
        );

        $rtmedia_query = new RTMediaQuery ( $args );
    }

    function set_title ( $default, $sep = "|" ) {
        global $wp_query;
        global $rtmedia_seo_title;

        if ( ! array_key_exists ( 'media', $wp_query->query_vars ) )
            return $default;
        $title = "";
        $oldSep = " " . $sep . " ";
        $sep = "";
        global $bp;
        global $rtmedia_query;
        if ( isset ( $rtmedia_query->query ) && isset ( $rtmedia_query->query[ "media_type" ] ) ) {
            if ( $rtmedia_query->query[ "media_type" ] == "album" ) {
                if ( isset ( $rtmedia_query->media_query ) && isset ( $rtmedia_query->media_query[ "album_id" ] ) ) {
                    foreach ( $rtmedia_query->album as $single_album ) {
                        if ( intval ( $single_album->id ) == intval ( $rtmedia_query->media_query[ "album_id" ] ) ) {
                            $title .= $sep . ucfirst ( $single_album->media_title );
                            $sep = $oldSep;
                        }
                    }
                }
            } else {
                if ( isset ( $rtmedia_query->media ) && count ( $rtmedia_query->media ) > 0 ) {
                    $title .= $sep . ucfirst ( $rtmedia_query->media[ 0 ]->media_title );
                    $sep = $oldSep;
                }
                $title .= $sep . ucfirst ( $rtmedia_query->query[ "media_type" ] );
                $sep = $oldSep;
            }
        } else {
            if ( isset ( $rtmedia_query->action_query ) && isset ( $rtmedia_query->action_query->media_type ) ) {
                $title .= $sep . ucfirst ( $rtmedia_query->action_query->media_type );
                $sep = $oldSep;
            }
        }
        if ( function_exists ( "bp_is_group" ) ) {
            if ( bp_is_group () or bp_is_group_forum () or bp_is_group_forum_topic () ) {
                if ( bp_is_group_forum_topic () ) {
                    $title .= $sep . bp_get_the_topic_title ();
                    $sep = $oldSep;
                }
                $title .= $sep . bp_get_current_group_name ();
                $sep = $oldSep;
            }
        }
        if ( function_exists ( "bp_get_displayed_user_fullname" ) && bp_displayed_user_id () != 0 ) {
            $title .= $sep . bp_get_displayed_user_fullname ();
            $sep = $oldSep;
        }

        $title .= $sep . RTMEDIA_MEDIA_LABEL;
        $sep = $oldSep;
        switch ( $this->context->type ) {
            case 'group':
                $title .= $sep . ucfirst ( $bp->groups->slug );
                break;
            case 'profile':
                if ( class_exists ( 'BuddyPress' ) ) {
                    $title .= $sep . ucfirst ( $bp->profile->slug );
                } else {
                    $title .= $sep . get_query_var ( 'author_name' );
                }
                break;
            default:
                $title .= $sep . get_post_field ( 'post_title', $this->context->id );
                break;
        }
        $title .= $sep . get_bloginfo ( 'name' );
        $rtmedia_seo_title = $title;        
        return apply_filters("rtmedia_wp_title", $title, $default, $sep);
    }

    function rtmedia_wpseo_og_image ( $data ) {
        global $wp_query;
        if ( ! array_key_exists ( 'media', $wp_query->query_vars ) )
            return $data;
        global $rtmedia_query;
        if ( isset ( $rtmedia_query->media ) && $rtmedia_query->media && count ( $rtmedia_query->media ) > 0 ) {

            foreach ( $rtmedia_query->media as $media ) {
                $img = wp_get_attachment_image_src ( $media->media_id, "full" );
                if ( $img && isset ( $img[ 0 ] ) && $img[ 0 ] != "" )
                    echo "<meta property='og:image' content='" . esc_url ( $img[ 0 ] ) . "'/>";
            }
        }
    }

    function rtmedia_wpseo_og_url ( $url ) {
        global $wp_query;
        if ( ! array_key_exists ( 'media', $wp_query->query_vars ) )
            return $url;
        $s = empty ( $_SERVER[ "HTTPS" ] ) ? '' : ($_SERVER[ "HTTPS" ] == "on") ? "s" : "";
        $sp = strtolower ( $_SERVER[ "SERVER_PROTOCOL" ] );
        $protocol = substr ( $sp, 0, strpos ( $sp, "/" ) ) . $s;
        $port = ($_SERVER[ "SERVER_PORT" ] == "80") ? "" : (":" . $_SERVER[ "SERVER_PORT" ]);
        return $protocol . "://" . $_SERVER[ 'SERVER_NAME' ] . $port . $_SERVER[ 'REQUEST_URI' ];
    }

}

?>

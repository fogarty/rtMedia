<?php
/**
 * @package Media_Component
 */
?>
<?php get_header() ?>

<div id="content">
    <div class="padder">


            <h3><?php _e( 'Media Directory', 'media' ) ?>

                <!--Here Upload button can be placed-->
                <?php //if ( is_user_logged_in() ) :?>
                        <!--<a class="button" href="<?php //echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/' ?>">
                <?php //_e( 'Create a Group', 'buddypress' ) ?>
                        </a>-->
                <?php //endif; ?>
            </h3>

            <?php do_action( 'bp_before_directory_media_content' ) ?>

            <div id="media-dir-search" class="dir-search">
                <?php if (function_exists('bp_directory_media_search_form')) //bp_directory_media_search_form() ?> <!--to implement-->
            </div><!-- #media-dir-search -->

            <div class="item-list-tabs">
                <ul>
                    <!--<li class="selected" id="media-all">
                        <a href="<?php bp_root_domain() ?>">
                    <?php
//                                                    echo __('All Media');
//                                                    printf( __( 'All media (%s)', 'media' ), bp_get_total_group_count() ) //to implement
                    ?>
                        </a>
                    </li>
                    -->


                    <?php do_action( 'bp_before_media_type_tab_all' ) ?>
                    <li id="media-mediaall" class="selected"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/all-media/' ?>"><?php printf( __( 'All Media', 'buddypress' ) ) ?></a></li>
                    <?php do_action( 'bp_before_media_type_tab_photo' ) ?>
                    <li id="media-photo" ><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/photo/' ?>"><?php printf( __( 'Photos ', 'buddypress' )) ?></a></li>
                    <?php do_action( 'bp_before_media_type_tab_audio' ) ?>
                    <li id="media-audio"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/audio/' ?>"><?php printf( __( 'Audio', 'buddypress' )) ?></a></li>
                    <?php do_action( 'bp_before_media_type_tab_video' ) ?>
                    <li id="media-video"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/video/' ?>"><?php printf( __( 'Videos ', 'buddypress' )) ?></a></li>
                    <?php if ( is_user_logged_in() ) : ?>
                        <?php do_action( 'bp_before_media_type_tab_upload' ) ?>
                    <li id="media-upload"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/upload/' ?>"><?php printf( __( 'Upload', 'buddypress' )) ?></a></li>
                    <?php endif; ?>
                    <?php do_action( 'bp_media_type_tabs' ) ?>

                    <?php do_action( 'bp_media_directory_media_types' ) ?>
<?if(is_user_logged_in()):?>
                    <!--Filters to implement-->
                    <li id="media-order-select" class="last filter">

                        <?php _e( 'Sort Media:', 'buddypress' ) ?>
                        <select>
                            <option value="all-media-data"><?php _e( 'All Media', 'buddypress' ) ?></option>
                            <option value="my-media-data"><?php _e( 'My Media', 'buddypress' ) ?></option>

                            <?php do_action( 'bp_media_directory_order_options' ) ?>
                        </select>
                    </li>
<?endif;?>
                </ul>
            </div><!-- .item-list-tabs -->

            <div id="media-dir-list" class="media dir-list">
                <?php if(is_kaltura_configured()):?>
                <?php bp_media_locate_template( array( 'media/media-loop.php' ), true ) ?>
                <?php else: ?>
                    <div id="message" class="info">
                        <p>Kaltura is not configured. Please contact Admin</p>
                    </div>
                <?php endif;?>
            </div><!-- #groups-dir-list -->

            <?php do_action( 'bp_directory_media_content' ) ?>

            <?php wp_nonce_field( 'directory_media', '_wpnonce-media-filter' ) ?>


        <?php do_action( 'bp_after_directory_media_content' ) ?>

    </div><!-- .padder -->
</div><!-- #content -->
<?php locate_template( array( 'sidebar.php' ), true ) ?>
<?php get_footer() ?>
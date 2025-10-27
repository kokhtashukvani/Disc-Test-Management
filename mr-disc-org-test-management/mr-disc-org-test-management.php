<?php
/*
Plugin Name: مستر دیسک - مدیریت تست سازمانی
Plugin URI: https://mrdisc.com/
Description: افزودن امکانات مدیریتی تست دیسک سازمانی
Version: 1.0
Author: مسعود عادل
Author URI: https://masoudadel.com/
License: GPLv2 or later
Text Domain: mr-disc-org-test-management
*/

// Function to add the custom user role
function mdotm_add_organization_role() {
    add_role(
        'organization',
        __( 'سازمان', 'mr-disc-org-test-management' ),
        array(
            'read'         => true,  // true allows this capability
            'edit_posts'   => false,
            'delete_posts' => false,
        )
    );
}

// Register the activation hook
register_activation_hook( __FILE__, 'mdotm_add_organization_role' );

// Add admin menu
function mdotm_admin_menu() {
    add_menu_page(
        __( 'تست سازمانی', 'mr-disc-org-test-management' ),
        __( 'تست سازمانی', 'mr-disc-org-test-management' ),
        'manage_options',
        'mdotm-organizations',
        'mdotm_list_organizations_page',
        'dashicons-groups',
        20
    );

    add_submenu_page(
        'mdotm-organizations',
        __( 'لیست سازمان ها', 'mr-disc-org-test-management' ),
        __( 'لیست سازمان ها', 'mr-disc-org-test-management' ),
        'manage_options',
        'mdotm-organizations',
        'mdotm_list_organizations_page'
    );

    add_submenu_page(
        'mdotm-organizations',
        __( 'افزودن سازمان', 'mr-disc-org-test-management' ),
        __( 'افزودن سازمان', 'mr-disc-org-test-management' ),
        'manage_options',
        'mdotm-add-organization',
        'mdotm_add_organization_page'
    );
}
add_action( 'admin_menu', 'mdotm_admin_menu' );

// Callback for the list organizations page
function mdotm_list_organizations_page() {
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['user_id'] ) ) {
        if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mdotm_delete_organization' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/user.php' );
            $user_id = intval( $_GET['user_id'] );
            wp_delete_user( $user_id );
            echo '<div class="updated"><p>' . __( 'سازمان با موفقیت حذف شد.', 'mr-disc-org-test-management' ) . '</p></div>';
        }
    }
    ?>
    <div class="wrap mdotm-wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html__( 'لیست سازمان ها', 'mr-disc-org-test-management' ); ?></h1>
        <a href="<?php echo admin_url( 'admin.php?page=mdotm-add-organization' ); ?>" class="page-title-action"><?php echo esc_html__( 'افزودن سازمان', 'mr-disc-org-test-management' ); ?></a>
        <hr class="wp-header-end">
        <div class="organizations-list">
            <?php
            $organizations = get_users( array( 'role' => 'organization' ) );
            foreach ( $organizations as $organization ) {
                $org_name_fa = get_user_meta( $organization->ID, 'org_name_fa', true );
                $org_logo = get_user_meta( $organization->ID, 'org_logo', true );
                $bg_color = get_user_meta( $organization->ID, 'bg_color', true );
                $bg_image = get_user_meta( $organization->ID, 'bg_image', true );
                $style = '';
                if ( ! empty( $bg_image ) ) {
                    $style = 'background-image: url(' . esc_url( $bg_image ) . ');';
                } elseif ( ! empty( $bg_color ) ) {
                    $style = 'background-color: ' . esc_attr( $bg_color ) . ';';
                }
                ?>
                <div class="organization-card" style="<?php echo $style; ?>">
                    <div class="organization-card-inner">
                        <?php if ( ! empty( $org_logo ) ) : ?>
                            <img src="<?php echo esc_url( $org_logo ); ?>" alt="<?php echo esc_attr( $org_name_fa ); ?>" class="organization-logo">
                        <?php endif; ?>
                        <h2><?php echo esc_html( $org_name_fa ); ?></h2>
                        <div class="actions">
                            <a href="<?php echo admin_url( 'admin.php?page=mdotm-add-organization&edit=' . $organization->ID ); ?>"><?php echo esc_html__( 'ویرایش', 'mr-disc-org-test-management' ); ?></a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=mdotm-organizations&action=delete&user_id=' . $organization->ID ), 'mdotm_delete_organization' ); ?>" class="delete"><?php echo esc_html__( 'حذف', 'mr-disc-org-test-management' ); ?></a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

// Callback for the add organization page
function mdotm_add_organization_page() {
    $edit_mode = false;
    $user_id = 0;
    $org_data = array();

    if ( isset( $_GET['owner_changed'] ) && $_GET['owner_changed'] == 1 ) {
        echo '<div class="updated"><p>' . __( 'مالکیت با موفقیت تغییر کرد.', 'mr-disc-org-test-management' ) . '</p></div>';
    }

    if ( isset( $_GET['edit'] ) ) {
        $edit_mode = true;
        $user_id = intval( $_GET['edit'] );
        $user = get_user_by( 'ID', $user_id );
        if ( $user && in_array( 'organization', $user->roles ) ) {
            $org_data['user_id'] = $user_id;
            $org_data['org_name_fa'] = get_user_meta( $user_id, 'org_name_fa', true );
            $org_data['org_name_en'] = get_user_meta( $user_id, 'org_name_en', true );
            $org_data['test_count'] = get_user_meta( $user_id, 'test_count', true );
            $org_data['org_logo'] = get_user_meta( $user_id, 'org_logo', true );
            $org_data['bg_color'] = get_user_meta( $user_id, 'bg_color', true );
            $org_data['bg_image'] = get_user_meta( $user_id, 'bg_image', true );
            $org_data['api_key'] = get_user_meta( $user_id, 'api_key', true );
        }
    }

    if ( isset( $_POST['regenerate_api_key'] ) ) {
        $user_id = intval( $_POST['user_id'] );
        if ( $user_id > 0 ) {
            $api_key = wp_generate_password( 32, false );
            update_user_meta( $user_id, 'api_key', $api_key );
            $org_data['api_key'] = $api_key;
            echo '<div class="updated"><p>' . __( 'API Key با موفقیت تولید شد.', 'mr-disc-org-test-management' ) . '</p></div>';
        }
    }

    if ( isset( $_POST['submit'] ) ) {
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $user_id_select = isset( $_POST['user_id_select'] ) ? intval( $_POST['user_id_select'] ) : 0;
        $newly_created_user_credentials = null;

        // Sanitize form data
        $org_name_fa = isset( $_POST['org_name_fa'] ) ? sanitize_text_field( $_POST['org_name_fa'] ) : '';
        $org_name_en = isset( $_POST['org_name_en'] ) ? sanitize_text_field( $_POST['org_name_en'] ) : '';
        $test_count = isset( $_POST['test_count'] ) ? intval( $_POST['test_count'] ) : 0;
        $org_logo = isset( $_POST['org_logo'] ) ? esc_url_raw( $_POST['org_logo'] ) : '';
        $bg_color = isset( $_POST['bg_color'] ) ? sanitize_hex_color( $_POST['bg_color'] ) : '';
        $bg_image = isset( $_POST['bg_image'] ) ? esc_url_raw( $_POST['bg_image'] ) : '';
        $new_password = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
        $new_owner_id = isset( $_POST['new_owner_id'] ) ? intval( $_POST['new_owner_id'] ) : 0;

        if ( $edit_mode && $new_owner_id > 0 ) {
            // Transfer ownership
            $old_owner_id = $user_id;
            $user_id = $new_owner_id;

            // Get all meta data from old owner
            $meta_keys = array( 'org_name_fa', 'org_name_en', 'test_count', 'org_logo', 'bg_color', 'bg_image', 'api_key' );
            foreach ( $meta_keys as $key ) {
                $value = get_user_meta( $old_owner_id, $key, true );
                if ( $value ) {
                    update_user_meta( $user_id, $key, $value );
                    delete_user_meta( $old_owner_id, $key );
                }
            }

            // Change roles
            $new_owner = new WP_User( $user_id );
            $new_owner->set_role( 'organization' );

            $old_owner = new WP_User( $old_owner_id );
            $old_owner->set_role( 'subscriber' );

            // Redirect to the new owner's edit page
            wp_redirect( admin_url( 'admin.php?page=mdotm-add-organization&edit=' . $user_id . '&owner_changed=1' ) );
            exit;
        }

        if ( $user_id === 0 ) { // Not in edit mode
            if ( $user_id_select > 0 ) {
                // Convert existing user
                $user_id = $user_id_select;
            } else {
                // Create a new user with generated credentials
                $username = sanitize_title( $org_name_fa ) . '-' . wp_rand( 100, 999 );
                $password = wp_generate_password( 12, true, true );
                $email = $username . '@' . preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );

                $user_id = wp_create_user( $username, $password, $email );

                if ( ! is_wp_error( $user_id ) ) {
                    $newly_created_user_credentials = ['username' => $username, 'password' => $password];
                }
            }
        }

        if ( is_wp_error( $user_id ) ) {
            echo '<div class="error"><p>' . $user_id->get_error_message() . '</p></div>';
        } else {
            if ( $edit_mode && ! empty( $new_password ) ) {
                wp_set_password( $new_password, $user_id );
            }
            // Update user role to organization
            $user = new WP_User( $user_id );
            $user->set_role( 'organization' );

            // Update user meta
            update_user_meta( $user_id, 'org_name_fa', $org_name_fa );
            update_user_meta( $user_id, 'org_name_en', $org_name_en );
            update_user_meta( $user_id, 'test_count', $test_count );
            update_user_meta( $user_id, 'org_logo', $org_logo );
            update_user_meta( $user_id, 'bg_color', $bg_color );
            update_user_meta( $user_id, 'bg_image', $bg_image );

            // Generate API key if it doesn't exist (for new users or converted users)
            if ( ! get_user_meta( $user_id, 'api_key', true ) ) {
                $api_key = wp_generate_password( 32, false );
                update_user_meta( $user_id, 'api_key', $api_key );
            }

            // Refresh org data for display after saving
            $org_data['org_name_fa'] = $org_name_fa;
            $org_data['org_name_en'] = $org_name_en;
            $org_data['test_count'] = $test_count;
            $org_data['org_logo'] = $org_logo;
            $org_data['bg_color'] = $bg_color;
            $org_data['bg_image'] = $bg_image;
            $org_data['api_key'] = get_user_meta( $user_id, 'api_key', true );


            echo '<div class="updated"><p>' . __( 'سازمان با موفقیت ذخیره شد.', 'mr-disc-org-test-management' ) . '</p></div>';

            if ( $newly_created_user_credentials ) {
                echo '<div class="notice notice-warning is-dismissible"><p>' . sprintf(
                    __( 'کاربر جدید با موفقیت ایجاد شد. این اطلاعات را در مکانی امن ذخیره کنید:', 'mr-disc-org-test-management' ) .
                    '<br/>' . __( 'نام کاربری: %s', 'mr-disc-org-test-management' ) .
                    '<br/>' . __( 'رمز عبور: %s', 'mr-disc-org-test-management' ),
                    '<strong>' . esc_html( $newly_created_user_credentials['username'] ) . '</strong>',
                    '<strong>' . esc_html( $newly_created_user_credentials['password'] ) . '</strong>'
                ) . '</p></div>';
            }
        }
    }

    ?>
    <div class="wrap mdotm-wrap">
        <h1><?php echo esc_html__( $edit_mode ? 'ویرایش سازمان' : 'افزودن سازمان', 'mr-disc-org-test-management' ); ?></h1>
        <div class="mdotm-form-container">
            <form method="post" action="">
                <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>">
                <table class="form-table">
                <?php if ( ! $edit_mode ) : ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'کاربر', 'mr-disc-org-test-management' ); ?></th>
                    <td>
                        <select name="user_id_select">
                            <option value="0"><?php echo esc_html__( 'ایجاد کاربر جدید', 'mr-disc-org-test-management' ); ?></option>
                            <?php
                            $users = get_users( array( 'role__not_in' => 'organization' ) );
                            foreach ( $users as $user ) {
                                echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->user_login ) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( '(فارسی) نام سازمان', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" name="org_name_fa" value="<?php echo esc_attr( $org_data['org_name_fa'] ?? '' ); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( '(انگلیسی) نام سازمان', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" name="org_name_en" value="<?php echo esc_attr( $org_data['org_name_en'] ?? '' ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'تعداد تست ها', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="number" name="test_count" value="<?php echo esc_attr( $org_data['test_count'] ?? '' ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'لوگو سازمان', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" name="org_logo" class="logo-url" value="<?php echo esc_attr( $org_data['org_logo'] ?? '' ); ?>" /><button class="button upload-logo"><?php echo esc_html__( 'آپلود لوگو', 'mr-disc-org-test-management' ); ?></button></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'رنگ بک گراند', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" name="bg_color" class="color-picker" value="<?php echo esc_attr( $org_data['bg_color'] ?? '' ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'تصویر بک گراند', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" name="bg_image" class="bg-image-url" value="<?php echo esc_attr( $org_data['bg_image'] ?? '' ); ?>" /><button class="button upload-bg-image"><?php echo esc_html__( 'آپلود تصویر', 'mr-disc-org-test-management' ); ?></button></td>
                </tr>
                <?php if ( $edit_mode ) : ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'نام کاربری', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="text" readonly value="<?php echo esc_attr( get_userdata( $user_id )->user_login ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'رمز عبور جدید', 'mr-disc-org-test-management' ); ?></th>
                    <td><input type="password" name="new_password" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'API Key', 'mr-disc-org-test-management' ); ?></th>
                    <td>
                        <input type="text" readonly value="<?php echo esc_attr( $org_data['api_key'] ?? '' ); ?>" />
                        <button type="submit" name="regenerate_api_key" class="button"><?php echo esc_html__( 'تولید دوباره', 'mr-disc-org-test-management' ); ?></button>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__( 'تغییر مالکیت', 'mr-disc-org-test-management' ); ?></th>
                    <td>
                        <select name="new_owner_id">
                            <option value="0"><?php echo esc_html__( 'انتخاب کاربر جدید', 'mr-disc-org-test-management' ); ?></option>
                            <?php
                            $users = get_users( array( 'role__not_in' => 'organization', 'exclude' => $user_id ) );
                            foreach ( $users as $user ) {
                                echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->user_login ) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
            <?php submit_button( __( 'ذخیره', 'mr-disc-org-test-management' ) ); ?>
            </form>
        </div>
    </div>
    <?php
}

// Enqueue scripts and styles
function mdotm_admin_enqueue_scripts() {
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'mdotm-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), '1.0' );
    wp_enqueue_script( 'mdotm-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'wp-color-picker' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'mdotm_admin_enqueue_scripts' );

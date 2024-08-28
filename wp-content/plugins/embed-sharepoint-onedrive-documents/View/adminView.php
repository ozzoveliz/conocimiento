<?php


namespace MoSharePointObjectSync\View;

use DateTime;
use MoSharePointObjectSync\Wrappers\wpWrapper;
use MoSharePointObjectSync\Wrappers\pluginConstants;

class adminView{
    private static $instance;

    public static function getView(){
        if(!isset(self::$instance)){
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function mo_sps_menu_display(){
        if( isset( $_GET[ 'tab' ] ) ) {
            $active_tab = sanitize_text_field($_GET['tab']);
        }
	    else{
            $active_tab = 'app_config';
        }
        $this->mo_sps_display_tabs($active_tab);
    }

    private function mo_sps_display_tabs($active_tab){
        echo '<div style="display:flex;justify-content:space-between;align-items:flex-start;padding-top:8px;"><div style="width:100% !important;" id="mo_sps_container" class="mo-container">';
        $this->mo_sps_display__header_menu();

        $mo_app_config_js_url = plugins_url( '../includes/js/appConfig.js', __FILE__ );
        wp_enqueue_script( 'mo_sps_app_config_js', $mo_app_config_js_url, array( 'jquery' ) );

        wp_localize_script(
            'mo_sps_app_config_js',
            'appConfig',
            array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
                'admin_url' => admin_url( 'admin.php' ),
                'nonce'     => wp_create_nonce( 'mo_sps_app_config__nonce' ),
                'test_url'  => $this->mo_sps_get_test_url(),
                'add_new'   => esc_url( MO_SPS_PLUGIN_URL . '/images/add-new.svg' ),
            )
        );


        if ( wpWrapper::mo_sps_check_client_secret_expiry_customers() ) {
            $connector = wpWrapper::mo_sps_get_option( pluginConstants::CLOUD_CONNECTOR );
            echo '<div class="mo_sps_expired_client_secret_notice">Your Connection is expired/inactive. Please click here to reconnect: <input type="button" id="mo_sps_fetch_client_secret_auto" data-type="' . esc_attr( $connector ) . '" class="mo-ms-tab-content-button" style="border-radius:4px;" value="Reconnect"></div>';
        }

        $this->mo_sps_display__tabs($active_tab);
    
        echo '<div style="display:flex;justify-content:space-between;align-items:flex-start;">';
        $this->mo_sps_display__tab_content($active_tab);
        if($active_tab != 'Documents') {
            $supportFormHandler = supportForm::getView();
            $supportFormHandler->mo_sps_display_support_form();
        }
        echo '</div>';
        echo '</div></div>';
        
    }

    private function mo_sps_get_test_url()
    {
        return admin_url( '/admin.php?option=testSPSApp' );
    }

    private function mo_sps_display__header_menu(){
       ?>
        <div class="mo_sps_newbanner_flex-container">
            <img id="mo-ms-title-logo" src="<?php echo esc_url(plugin_dir_url(MO_SPS_PLUGIN_FILE).'images/miniorange_logo.png');?>">
            <div class="mo_sps_newbanner_flex-content">
                <div>
                    <h1><label for="sync_integrator">Embed SharePoint OneDrive Documents</label></h1>
                </div>
                <div>
                    <button class="mo_sps_newbanner_manage-apps-button" onclick="openPluginPage('mo_sps&tab=app_config')">
                        <span class="dashicons dashicons-admin-settings"></span><a>Manage Apps</a>
                    </button>
                    <button class="mo_sps_newbanner-ask-us-button" onclick="window.open('https://forum.miniorange.com/','_blank').focus()">
                        <span class="dashicons dashicons-admin-users"></span><a>Ask Us On Forum</a>
                    </button>
                    <button class="mo_sps_newbanner-faq-button" onclick="window.open('https://faq.miniorange.com/kb/azure-ad-integration/sharepoint/','_blank').focus()">
                        <span class="dashicons dashicons-editor-help"></span><a>Frequently Asked Questions</a>
                    </button>
                </div>
            </div>
            <span><a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#pricing-cards" class="banner_buttons button button-primary licensing-plan-button" style="margin-right:10px !important;margin-top: 30px !important;display:block;font-weight:600;cursor:pointer;border-width:1px;border-style: solid;margin:10px;background-color: #1B9BA1;border-color: #1B9BA1;margin-left:2rem;font-size:1.1rem;">Licensing Plans</a> </span>
            <span><a href="<?php echo esc_url_raw(add_query_arg(["page"=>"mo_sps","tab"=>"demo_request"],admin_url("admin.php")));?>" class="banner_buttons button button-primary licensing-plan-button" style="margin-right:-40px !important;margin-top: 30px !important;display:block;font-weight:600;cursor:pointer;border-width:1px;border-style: solid;margin:10px;background-color: #1B9BA1;border-color: #1B9BA1;font-size:1.1rem;"> Request for Demo </a></span>
            <span>
                <a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#demo-form"
                    class="button button-primary licensing-plan-button mo_sps_newbanner_book-meeting-button banner_buttons" style="margin: 29px 25px 0px 60px !important;">
                    Book a Meeting <span style="margin-top: 11px;" class="dashicons dashicons-video-alt2"></span>
                </a>
            </span>
        </div>


        <script>
            function openPluginPage(tab) {
            var adminUrl = '<?php echo admin_url(); ?>';
            var pluginUrl = adminUrl + 'admin.php?page=mo_sps&tab=' + tab;
            window.location.href = pluginUrl;
            }
        </script>
        <?php
    }

    private function mo_sps_display__tabs($active_tab){
        $app = wpWrapper::mo_sps_get_option(pluginConstants::APP_CONFIG);

        $value = !empty($state)?$state : (isset($app['folder_path']) ? $app['folder_path'] : '');
        ?>
        <div class="mo-ms-tab ms-tab-background mo-ms-tab-border">
            <ul class="mo-ms-tab-ul">
                <li id="app_config" class="mo-ms-tab-li">
                    <a href="<?php echo esc_url_raw(admin_url('admin.php?page=mo_sps&tab=app_config'));?>">
                        <div id="application_div_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'app_config'){
                            echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="Application" title="Application Configuration" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img style="width:20px;height:20px" src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/microsoft-sharepoint.svg');?>">
                            </div>
                            <div id="add_app_label" class="mo-ms-tab-li-label">
                                Connection
                            </div>
                        </div>
                    </a>
                </li>

                <li id="Documents" class="mo-ms-tab-li" style="margin-left:10px;" role="presentation" title="user_manage">
                    <?php $query_arg = ['tab'=>'Documents'];?>
                    <a href="<?php echo wpWrapper::mo_urlencode(str_replace('\\','',add_query_arg($query_arg)), "'");?>">
                    <?php
                    ?>
                    <input type="hidden" id="Documents_tab" value="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=Documents');?>">
                        <div id="Documents_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'Documents'){
                        echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="Documents" title="Documents" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img class="filter-green" style="width:20px;height:20px;"
                                 src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/folder_main.svg');?>">
                            </div>
                            <div id="add_app_label" class="mo-ms-tab-li-label">
                                Preview Documents / Files
                            </div>
                        </div>
                    </a>
                </li>

                <li id="Shortcode" class="mo-ms-tab-li" style="margin-left:10px;" role="presentation" title="user_manage">
                    <a href="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=Shortcode');?>">
                    <?php
                    ?>
                    <input type="hidden" id="Shortcode_tab" value="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=Shortcode');?>">
                        <div id="Documents_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'Shortcode'){
                        echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="Shortcode" title="Shortcode" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img class="filter-green" style="width:20px;height:20px;"
                                 src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/shortcode.png');?>">
                            </div>
                            <div id="add_app_label" class="mo-ms-tab-li-label">
                                Embed Options
                            </div>
                        </div>
                    </a>
                </li>

                <li id="market_feature" class="mo-ms-tab-li" style="margin-left:10px;" role="presentation" >
                    <a href="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=advanced_settings');?>">
                        <?php
                        ?>
                        <input type="hidden" id="Shortcode_tab" value="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=advanced_settings');?>">
                        <div id="Documents_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'advanced_settings'){
                            echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="advanced_settings" title="advanced_settings" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img class="filter-green" style="width:20px;height:20px;"
                                     src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/settings.png');?>">
                            </div>
                            <div id="add_app_label" class="mo-ms-tab-li-label">
                                Advanced Settings
                            </div>
                        </div>
                    </a>
                </li>

                <li id="sync_user" class="mo-ms-tab-li" style="margin-left:10px;" role="presentation" title="user_manage">
                    <a href="<?php echo esc_url_raw(admin_url('admin.php?page=mo_sps&tab=sync_user'));?>">
                    <input type="hidden" id="sync_user_tab" value="<?php esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=sync_user');?>">
                    <div id="sync_user_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'sync_user'){
                            echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="sync_user" title="Sync User" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img class="filter-green" style="width:20px;height:20px;
                            " src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/users.svg');?>">
                            </div>
                            <div id="add_app_label" class="mo-ms-tab-li-label">
                                SharePoint User Profile
                            </div>
                        </div>
                    </a>
                </li>

                <li id="mo_sps_demo_request" class="mo-ms-tab-li" style="margin-left:10px;" title="demo_request">
                    <a href="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=demo_request');?>">
                        <div id="application_div_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'demo_request'){
                            echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="Demo Request" title="Demo Request" role="button" tabindex="0">
                            <div id="add_icon" class="mo-ms-tab-li-icon" >
                                <img class="filter-green" style="width:20px;height:20px;
                            " src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/demo.png');?>">
                            </div>
                            <div id="demo_request" class="mo-ms-tab-li-label">
                               Demo Request
                            </div>
                        </div>
                    </a>
                </li>

                <li id="account_setup" class="mo-ms-tab-li">
                    <a href="<?php echo esc_url_raw(admin_url().'admin.php?page=mo_sps&tab=account_setup');?>">
                        <div id="account_setup_div_id" class="mo-ms-tab-li-div <?php
                        if($active_tab == 'account_setup'){
                            echo 'mo-ms-tab-li-div-active';
                        }
                        ?>" aria-label="account_setup" title="Account Setup" role="button" tabindex="2">
                            <div id="account_setup_icon" class="mo-ms-tab-li-icon" >
                                <img style="width:16px;height:16px;" src="<?php echo MO_SPS_PLUGIN_URL . '/images/login.png';?>">
                            </div>
                            <div id="account_setup_label" class="mo-ms-tab-li-label">
                                Account Setup
                            </div>
                        </div>
                    </a>
                </li>


            </ul>
        </div>
        
        <?php
    }
    private function mo_sps_display__tab_content($active_tab){
        $handler = self::getView();
        switch ($active_tab){
            case 'app_config':{
                $handler = appConfig::getView();
                break;
            }
            case 'sync_user':{
                $handler = syncUser::getView();
                break;
            }
            case 'Documents':{
                $handler = documentsSync::getView();
                break;
            }
            case 'Shortcode':{
                $handler = Shortcode::getView();
                break;
            }
            case 'demo_request':{
                $handler = demoRequest::getView();
                break;
            }
            case 'advanced_settings':{
                $handler = advancedSettings::getView();
                break;
            }
            case 'account_setup':{
                $handler = accountSetup::getView();
            }
            
        }
        
        $handler->mo_sps_display__tab_details();
        
    }

    private function mo_sps_display__tab_details(){
       esc_html_e("Class missing. Please check if you've installed the plugin correctly.");
    }
    
}
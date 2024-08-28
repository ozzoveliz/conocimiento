<?php


namespace MoSharePointObjectSync\View;


use MoSharePointObjectSync\Wrappers\pluginConstants;
use MoSharePointObjectSync\Wrappers\wpWrapper;
use WP_Roles;

class advancedSettings
{

    private static $instance;

    public static function getView()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function mo_sps_display__tab_details(){

        ?>
        <div class="mo-ms-tab-content" style="width:77rem;">
            <div style="width: 68%">
                <div class="mo-ms-tab-content-left-border">
                    <?php
                    $this->mo_sps_display_wp_to_ad_sync_manual_settings();
                    ?>
                </div>
            </div>
        </div>
        <?php
    }




    private function mo_sps_display_wp_to_ad_sync_manual_settings(){

        $wp_roles         = new WP_Roles();
        $roles            = $wp_roles->get_names();
        $drive_id         = wpWrapper::mo_sps_get_option(pluginConstants::SPS_SEL_DRIVE);

        ?>
        <div class="mo-ms-tab-content-tile" style="width:135%;padding: 1rem;background: #f4f4f4;border: 4px solid #A6DEE0;border-radius: 5px;margin-top:0px !important;padding-top:0px !important;">
            <div class="mo-ms-tab-content-tile-content mo-sps-prem-info" style="position:relative;">
                                <span style="font-size: 18px;font-weight: 500;">1. Roles/Folders Restriction
                                    <sup style="font-size: 12px;color:red;font-weight:600;">
                                            [Available in <a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#pricing-cards" style="color:red;">Paid</a> Plugins]
                                    </sup>
                                </span>
                <div class="mo-sps-prem-lock" style="top:2px;right:2px;position:absolute;">
                    <img class="filter-green"
                         src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/lock.svg');?>">
                    <p class="mo-sps-prem-text">Available in <a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#pricing-cards" style="color:#ffeb00;;">Paid</a> plugins.</p>
                </div>
                <div id="basic_attr_access_desc" class="mo_sps_help_desc">
                                    <span>Map your WordPress Roles / BuddyPress Groups / Membership Levels to Sharepoint site URL of folders to restrict files and folders.
                                    </span>
                </div>

                <table class="mo-ms-tab-content-app-config-table">
                    <colgroup>
                        <col span="1" style="width: 30%;">
                        <col span="2" style="width: 50%;">
                    </colgroup>
                    <?php
                    foreach($roles as $role_value => $role_name){
                        $configured_role_value = empty($roles_configured)?'':$roles_configured[$role_value];
                        ?>
                        <tr>
                            <td><span><?php echo esc_html($role_name); ?></span></td>
                            <td>
                                <input disabled style="border:1px solid #eee;" value="Enter SharePoint Server Relative URL of Folders" type="text">
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr><td></br></td></tr>
                    <tr>
                        <td>
                            <input disabled style="background-color: #DCDAD1;border:none;width:100px;height:30px;" type="submit" class="mo-ms-tab-content-button" value="Save">
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <div class="mo-ms-tab-content-tile" style="width:135%;padding: 1rem;background: #f4f4f4;border: 4px solid #A6DEE0;border-radius: 5px;margin-top:0px !important;padding-top:0px !important;">

            <div class="mo-ms-tab-content-tile-content mo-sps-prem-info" style="position:relative;">
                    <span style="font-size: 18px;font-weight: 500;">
                    2. Sync News And Articles
                    <sup style="font-size: 12px;color:red;font-weight:600;">
                                [Available in <a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#pricing-cards" style="color:red;">Paid</a> Plugins]
                    </sup>
                    </span>
                <div class="mo-sps-prem-lock" style="top:2px;right:2px;position:absolute;">
                    <img class="filter-green"
                         src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/lock.svg');?>">
                    <p class="mo-sps-prem-text">Available in <a target="_blank" href="https://plugins.miniorange.com/microsoft-sharepoint-wordpress-integration#pricing-cards" style="color:#ffeb00;;">Paid</a> plugins.</p>
                </div>
                <div id="basic_attr_access_desc" class="mo_sps_help_desc">
                        <span>Sync All your SharePoint online news and articles into the wordpress posts.
                        </span>
                </div>
                <table class="mo-ms-tab-content-app-config-table">
                    <tr>
                        <td style="width:35%;word-break: break-all;"><span><h4>Enable to Sync SharePoint Social News</h4></span></td>
                        <td class="right-div">
                            <label class="switch">
                                <input type="checkbox" disabled>
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:35%;word-break: break-all;"><span><h4>Enable to Sync Sync SharePoint Social Articles</h4></span></td>
                        <td class="right-div">
                            <label class="switch">
                                <input type="checkbox" disabled>
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php

    }
}
<?php

namespace MoSharePointObjectSync\View;
use MoSharePointObjectSync\Wrappers\pluginConstants;
use MoSharePointObjectSync\Wrappers\wpWrapper;
use MoSharePointObjectSync\API\Azure;
use MoSharePointObjectSync\Observer\shortcodeSharepoint;

class Shortcode{

    private static $instance;

    public static function getView(){
        if(!isset(self::$instance)){
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }
    

    public function mo_sps_display__tab_details(){
        
        ?>
        <div class="mo-ms-tab-content" style="width:77rem;">
            <h1>Embed Library</h1>
            
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
        ?>

    <div class="mo-ms-tab-content-tile" style="width:135%;">
    
        <div class="mo-ms-tab-content-tile-content">
            <span style="font-size: 18px;font-weight: 700;">1. Embed using WordPress Shortcode</span>
            <div id="basic_attr_access_desc" class="mo_sps_help_desc" style="font-weight:500;">
                <span>Copy this shortcode and follow the below steps to embed  sharepoint documents.
                    </span>
            </div>
            <div>
                <ol style="margin-left:20px;">
                    <li>Copy the <b>Shortcode</b> given below.</li>
                </ol>
            </div>
            <div style="background-color:#eee;display:flex;align-items:center;padding:12px;margin-top:1rem;">
                <span style="width:99%;" id="mo_copy_shortcode">[MO_SPS_SHAREPOINT width="100%" height="800px"]</span>
                <form id="mo_copy_to_clipboard" method="post" name="mo_copy_to_clipboard">
                    <input type="hidden" name="option" id="app_config" value="mo_copy_to_clipboard">
                    <input type="hidden" name="mo_sps_tab" value="app_config">
                    <?php wp_nonce_field('mo_copy_to_clipboard');?>
                    <div style="margin-left:3px;">
                        <button type="button" class="mo_copy copytooltip rounded-circle float-end" style="background-color:#eee;width:40px;height:40px;margin-top:0px;border-radius:100%;border:0 solid;">
                            <img style="width:25px;height:25px;margin-top:0px;margin-left:0px;" src="<?php echo esc_url(MO_SPS_PLUGIN_URL . '/images/copy.png');?>" onclick="copyToClipboard(this, '#mo_copy_shortcode', '#copy_shortcode');">
                            <span id="copy_shortcode" class="copytooltiptext">Copy to Clipboard</span>
                        </button>
                    </div>                 
                </form>
            </div>
            <div>
                <ol start="2" style="margin-left:20px;">
                    <li>Go to the <a href="<?php echo admin_url() . 'edit.php?post_type=page';?>"><b>Pages</b></a> or <a href="<?php echo admin_url() . 'edit.php?post_type=post';?>"><b>Posts</b></a> tab in your WordPress dashboard.</li>
                    <li>Click on add new / select any existing post/page on which you want to embed sharepoint library</li>
                    <li>Click the "+" icon and search for <b>Shortcode</b></li>
                    <li>Paste the copied shortcode into the shortcode block.</li>
                    <li>Modify 'width' and 'height' attributes as per your need.</li>
                    <li>Preview changes and then click <b>Publish</b> or <b>Update</b>.</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="mo-ms-tab-content-tile" style="width:135%;">  
        <div class="mo-ms-tab-content-tile-content">
            <span style="font-size: 18px;font-weight: 700;">2. Embed Documents Using Gutenburg Block</span>
            </br>
            </br>
            <div style="margin-bottom:10px;"><b>Note:</b>Follow below steps to Embed documents in pages and posts using gutenburg block.</div>
            <div>
                <ol style="margin-left:20px;">
                    <li>Go to the <a href="<?php echo admin_url() . 'edit.php?post_type=page';?>"><b>Pages</b></a> or <a href="<?php echo admin_url() . 'edit.php?post_type=post';?>"><b>Posts</b></a> tab in your WordPress dashboard.</li>
                    <li>Click on add new / select any existing post/page on which you want to embed sharepoint library</li>
                    <li>Click on "+" icon and search <strong>sharepoint library</strong></li>
                    <li>Enter the height and width as per your preference</li>
                    <li>Now save this and click on pubish</li>
                    <li>Wohooo!!Now you can see your media library on updated page/post.</li>
                </ol>
            </div>
            
        </div>
    </div>
    <?php
    
    }

}
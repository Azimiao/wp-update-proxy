<?php

/**
 * Plugin Name: WP Update Proxy
 * Plugin URI: https://www.azimiao.com
 * Description: 劫持 WordPress 更新操作，使用自定义反代服务器替代官方服务器，并允许在后台设置启用状态和反代地址。
 * Version: 1.3
 * Author: WildRabbit
 * Author URI: https://www.azimiao.com
 */

// 创建设置页面菜单
add_action('admin_menu', function () {
    add_options_page(
        'WP Update Proxy 设置',
        'WP Update Proxy',
        'manage_options',
        'wp-update-proxy',
        'wp_update_proxy_settings_page'
    );
});

// 渲染设置页面
function wp_update_proxy_settings_page()
{
    // 检查用户权限
    if (!current_user_can('manage_options')) {
        return;
    }

    // 保存设置
    if (isset($_POST['wp_update_proxy_submit'])) {
        check_admin_referer('wp_update_proxy_settings');
        update_option('wp_update_proxy_enabled', isset($_POST['proxy_enabled']) ? '1' : '0');
        update_option('wp_update_proxy_server', sanitize_text_field($_POST['proxy_server']));
        echo '<div class="updated"><p>设置已保存。</p></div>';
    }

    // 获取当前值
    $proxy_enabled = get_option('wp_update_proxy_enabled', '0');
    $proxy_server = get_option('wp_update_proxy_server', 'https://api.acgame.fun');

?>
    <div class="wrap">
        <h1>WP Update Proxy 设置</h1>
        <form method="post">
            <?php wp_nonce_field('wp_update_proxy_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="proxy_enabled">启用反代</label></th>
                    <td>
                        <input type="checkbox" id="proxy_enabled" name="proxy_enabled" value="1" <?php checked($proxy_enabled, '1'); ?>>
                        <p class="description">勾选此项以启用反代功能。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="proxy_server">反代服务器地址</label></th>
                    <td>
                        <input type="text" id="proxy_server" name="proxy_server" value="<?php echo esc_attr($proxy_server); ?>" class="regular-text">
                        <p class="description">请输入你的反代服务器地址，默认为：https://api.acgame.fun</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('保存设置', 'primary', 'wp_update_proxy_submit'); ?>
        </form>
    </div>
<?php
}

add_filter('pre_http_request', function ($preempt, $args, $url) {

    if($preempt || isset($args['_wp_update_proxy'])){
        return $preempt;
    }

    if ( ( ! strpos( $url, 'api.wordpress.org' ) && ! strpos( $url, 'downloads.wordpress.org' ) ) ) {
        return $preempt;
    }

    $proxy_enabled = get_option('wp_update_proxy_enabled', '0');
    $proxy_server = get_option('wp_update_proxy_server', '');
    
    // 如果未启用反代功能，直接返回
    if ($proxy_enabled !== '1' || empty($proxy_server)) {
        return false;
    }

    // 使用反代服务器替换官方域名
    $url = str_replace(
        ['https://api.wordpress.org', 'https://downloads.wordpress.org'],
        [rtrim($proxy_server, '/') . '/api', rtrim($proxy_server, '/') . '/downloads'],
        $url
    );

    $args['_wp_update_proxy'] = true;
    return wp_remote_request($url, $args);
}, 10, 3);
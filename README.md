# WordPress Update Proxy

可以通过第三方代理服务器进行 WordPress 主题、插件的列表获取或文件下载，以及 WordPress 自身的更新。

代理服务器地址在`后台管理页面 -> 设置 -> WP Update Proxy`中设置。

大部分代码由 ChatGPT 编写，参考了 WP-Chinese-Yes 项目。

代码未进行任何安全审计，不对安全性有任何保证，仅供应急使用，使用完请尽快删除。

## 代理服务器配置

```nginx
# 反代 API 请求
location /api/ {
    proxy_pass https://api.wordpress.org/;
    proxy_set_header Host api.wordpress.org;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    
    proxy_connect_timeout 30s;
    proxy_read_timeout 60s;
    proxy_send_timeout 60s;
}

# 反代下载请求
location /downloads/ {
    proxy_pass https://downloads.wordpress.org/;
    proxy_set_header Host downloads.wordpress.org;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;

    proxy_connect_timeout 30s;
    proxy_read_timeout 120s; 
    proxy_send_timeout 120s;
}
```

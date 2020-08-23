### laravel-shop 电子商城
本项目基于Laravel-china教程6《电商进阶》，在此基础上进行扩展开发

<br />线上demo：http://lara-shop.yirenkeji.com 前台用户账号为123456@qq.com 密码为123456
<br />
管理后台地址 : http://lara-shop.yirenkeji.com 
账号： admin_test 密码： abc123456
### 扩展功能点

1、商品模块
- 使用laravel-admin ModelTree 重写商品分类管理，商品分类管理支持拖拽操作
- 升级 laravel-admin 扩展版本号，各个模块数据模块管理更加方便

2、管理后台优化 
- 使用 laravel-admin iframe-tab多页面扩展，管理后台支持多ifame多页面，优化后台数据加载与用户体验
- 项目 新增 素材管理与日志管理，方便管理后台系统 功能素材，以及项目线上异常的排查
- 项目 新增 开发sql 打印监听，配置文件配置 DB_SQL_LOG=1 可在项目中开启项目sql 实时监控，方便开发调试
与分析sql 性能

### TO DO 
- 1、商品前端搜索功能使用 ElasticSearch
- ......

## 安装

### 环境要求
- php >=7.0
- 安装好 composer
- 安装好 npm 
- 安装好npm(建议使用国内镜像cnpm,安装速度更快)
- redis
- MySQL 
- ElasticSearch 并安装好ik 分词插件

### 安装方法

- 1.git clone或者下载解压到本地
```
git clone https://github.com/HeRedBo/lara-shop
```
- 2.更改storage目录权限，
```
chomd -R 777 ./storage
```
- 3.使用 composer 安装 项目扩展包
```
composer install 
```
- 4.执行如下命令
```
# laravel-admin 按照与数据填充 
 admin:install
# 数据表创建
php artisan migrate 
# 数据表填充
php artisan db:seed 

# 设置文件存储文件夹软链接 =>"public/storage" to "storage/app/public"
php artisan storage:link 
```
- 5 安装好jdk1.8配置好java环境
- 6、下载ElasticSearch以及它的中文分词插件，两个的版本要对应上，把下载的插件解压到es的plugins目录下重命名为ik 按照方法可行百度
- 7、后台管理员账号为admin 密码为admin 前台用户账号为123456@qq.com 密码为123456












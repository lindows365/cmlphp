<?php
/* * *********************************************************
 * [cml] (C)2012 - 3000 cml http://cmlphp.com
 * @Author  linhecheng<linhechengbush@live.com>
 * @Date: 14-2-8 下午3:07
 * @version  2.7
 * cml框架 URL解析类
 * *********************************************************** */
namespace Cml;

use Cml\Http\Request;

/**
 * Url解析类,负责路由及Url的解析
 *
 * @package Cml
 */
class Route
{
    /**
     * pathIfo数据用来提供给插件做一些其它事情
     *
     * @var array
     */
    private static $pathInfo = [];


    /**
     * 解析url获取pathinfo
     *
     * @return void
     */
    public static function parsePathInfo()
    {
        $urlModel = Config::get('url_model');
        $pathInfo = [];
        $isCli = Request::isCli(); //是否为命令行访问
        if ($isCli) {
            isset($_SERVER['argv'][1]) && $pathInfo = explode('/', $_SERVER['argv'][1]);
        } else {
            if ($urlModel === 1 || $urlModel === 2) { //pathInfo模式(含显示、隐藏index.php两种)SCRIPT_NAME
                if (isset($_GET[Config::get('var_pathinfo')])) {
                    $param = $_GET[Config::get('var_pathinfo')];
                } else {
                    $param = preg_replace('/(.*)\/(.*)\.php(.*)/i', '\\1\\3', $_SERVER['REQUEST_URI']);
                    $scriptName =  preg_replace('/(.*)\/(.*)\.php(.*)/i', '\\1', $_SERVER['SCRIPT_NAME']);

                    if (!empty($scriptName)) {
                        $param = substr($param, strpos($param, $scriptName) + strlen($scriptName));
                    }
                }
                $param = ltrim($param, '/');

                if (!empty($param)) { //无参数时直接跳过取默认操作
                    //获取参数
                    $pathInfo = explode(Config::get('url_pathinfo_depr'), trim(preg_replace(
                        [
                            '/\\'.Config::get('url_html_suffix').'/',
                            '/\&.*/', '/\?.*/'
                        ],
                        '',
                        $param
                    ), Config::get('url_pathinfo_depr')));
                }
            } elseif ($urlModel === 3 && isset($_GET[Config::get('var_pathinfo')])) {//兼容模式
                $urlString = $_GET[Config::get('var_pathinfo')];
                unset($_GET[Config::get('var_pathinfo')]);
                $pathInfo = explode(Config::get('url_pathinfo_depr'), trim(str_replace(
                    Config::get('url_html_suffix'),
                    '',
                    ltrim($urlString, '/')
                ), Config::get('url_pathinfo_depr')));
            }
        }

        isset($pathInfo[0]) && empty($pathInfo[0]) && $pathInfo = [];

        //参数不完整获取默认配置
        if (empty($pathInfo)) {
            $pathInfo = explode('/', trim(Config::get('url_default_action'), '/'));
        }
        self::$pathInfo = $pathInfo;
    }

    /**
     * 增加get访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function get($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->get($pattern, $action);
    }

    /**
     * 增加post访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function post($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->post($pattern, $action);
    }

    /**
     * 增加put访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function put($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->put($pattern, $action);
    }

    /**
     * 增加patch访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function patch($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->patch($pattern, $action);
    }

    /**
     * 增加delete访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function delete($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->delete($pattern, $action);
    }

    /**
     * 增加options访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function options($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->options($pattern, $action);
    }

    /**
     * 增加任意访问方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function any($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->any($pattern, $action);
    }

    /**
     * 增加REST方式路由
     *
     * @param string $pattern 路由规则
     * @param string|array $action 执行的操作
     *
     * @return void
     */
    public static function rest($pattern, $action)
    {
        Cml::getContainer()->make('cml_route')->rest($pattern, $action);
    }

    /**
     * 分组路由
     *
     * @param string $namespace 分组名
     * @param callable $func 闭包
     */
    public static function group($namespace, callable $func)
    {
        Cml::getContainer()->make('cml_route')->group($namespace, $func);
    }

    /**
     * 获取解析后的pathInfo信息
     *
     * @return array
     */
    public static function getPathInfo()
    {
        return self::$pathInfo;
    }

    /**
     * 访问Cml::getContainer()->make('cml_route')中其余方法
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([Cml::getContainer()->make('cml_route'), $name], $arguments);
    }

    /**
     * 载入应用单独的路由
     *
     * @param string $app 应用名称
     */
    public static function loadAppRoute($app = 'web')
    {
        static $loaded = [];
        if (isset($loaded[$app]) ) {
            return;
        }
        $appRoute = Cml::getApplicationDir('apps_path').DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.Cml::getApplicationDir('app_config_path_name').DIRECTORY_SEPARATOR.'route.php';
        if (!is_file($appRoute)) {
            throw new \InvalidArgumentException(Lang::get('_NOT_FOUND_', $app.DIRECTORY_SEPARATOR.Cml::getApplicationDir('app_config_path_name').DIRECTORY_SEPARATOR.'route.php'));
        }

        $loaded[$app] = 1;
        Cml::requireFile($appRoute);
    }
}
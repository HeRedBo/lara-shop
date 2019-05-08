<?php


if(!function_exists('logResult'))
{
    /**
     * @param string $data 日志内容
     * @param string $level 日志级别
     * @param string $filename 日志文件名称
     * @return bool
     */
    function logResult()
    {
        $filename = $filename ?: FileLogger::LOG_INFO;
        $levels = FileLogger::$levels;
        if(!empty($level) && !in_array($level, $levels))
        $level = 'info';
        FileLogger::getLogger($filename)->$level($data);
        return true;
    }
}


if (!function_exists('order_to_replace'))
{
    /**
     * 使用回调函数顺序匹配替换目标字符串
     * @param  string $reg  要替换正则
     * @param  string $str  需要替换的内容
     * @param  string  $param 需要替换的参数
     * @return string 返回替换后的字符串
     */
    function order_to_replace($reg,$str,$param)
    {
        return preg_replace_callback(
            $reg,
            function() use ($param) {
                static $i = 0;
                if(isset($param[$i])) {
                    $res = $param[$i];
                    $i++;
                    return $res;
                };
            },
            $str
        );
    }
}


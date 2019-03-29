<?php

namespace Mix\Http\Daemon\Commands\Service;

use Mix\Console\CommandLine\Flag;
use Mix\Console\PidFileHandler;
use Mix\Helper\FileSystemHelper;
use Mix\Core\Bean\AbstractObject;
use Mix\Log\MultiHandler;

/**
 * Class BaseCommand
 * @package Mix\Http\Daemon\Commands\Service
 * @author liu,jian <coder.keda@gmail.com>
 */
class BaseCommand extends AbstractObject
{

    /**
     * 运行中提示
     */
    const IS_RUNNING = 'Service is running, PID : %d';

    /**
     * 未运行提示
     */
    const NOT_RUNNING = 'Service is not running.';

    /**
     * 执行成功提示
     */
    const EXEC_SUCCESS = 'Command executed successfully.';

    /**
     * 配置信息
     * @var array
     */
    public $config;

    /**
     * 初始化事件
     */
    public function onInitialize()
    {
        parent::onInitialize(); // TODO: Change the autogenerated stub
        // 服务器配置处理
        $file = Flag::string(['c', 'configuration'], '');
        if ($file == '') {
            throw new \Mix\Exception\InvalidArgumentException('Option \'-c/--configuration\' required.');
        }
        if (!FileSystemHelper::isAbsolute($file)) {
            $file = getcwd() . DIRECTORY_SEPARATOR . $file;
        }
        if (!is_file($file)) {
            throw new \Mix\Exception\InvalidArgumentException("Configuration file not found: {$file}");
        }
        $config = require $file;
        // 应用配置处理
        if (!is_file($config['application']['config_file'])) {
            $filename = \Mix\Helper\FileSystemHelper::basename($file);
            throw new \Mix\Exception\InvalidArgumentException("{$filename}: 'application.config_file' file not found: {$config['application']['config_file']}");
        }
        // 构造配置信息
        $this->config = [
            'host'       => $config['server']['host'],
            'port'       => $config['server']['port'],
            'configFile' => $config['application']['config_file'],
            'setting'    => $config['setting'],
        ];
        // 配置日志组件
        $handler             = app()->log->handler;
        $fileHandler         = $handler->fileHandler;
        $fileHandler->single = $this->config['setting']['log_file'] ?? '';
        // Swoole 判断
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('Need swoole extension to run, install: https://www.swoole.com/');
        }
    }

    /**
     * 获取pid
     * @return bool|string
     */
    public function getServicePid()
    {
        $handler = new PidFileHandler(['pidFile' => $this->config['setting']['pid_file'] ?? '']);
        return $handler->read();
    }

}

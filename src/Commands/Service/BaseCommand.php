<?php

namespace Httpd\Commands\Service;

use Mix\Config\IniParser;
use Mix\Console\Command;
use Mix\Console\CommandLine\Flag;
use Mix\Console\PidFileHandler;

/**
 * Class BaseCommand
 * @package Httpd\Commands\Service
 */
class BaseCommand extends Command
{

    /**
     * 提示
     */
    const IS_RUNNING = 'Service is running, PID : %d';
    const NOT_RUNNING = 'Service is not running.';
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
        // 获取配置
        $filename = Flag::string(['c', 'configuration'], '');
        if ($filename == '') {
            $filename = app()->basePath . DIRECTORY_SEPARATOR . 'app.ini';
        }
        $ini = new IniParser([
            'filename' => $filename,
        ]);
        if (!$ini->load()) {
            throw new \Mix\Exceptions\InvalidArgumentException("Configuration file not found: {$filename}");
        }
        $this->config = $ini->sections();
        // 配置日志组件
        $handler         = app()->log->handler;
        $handler->single = $this->config['settings']['log_file'] ?? '';
    }

    /**
     * 获取pid
     * @return bool|string
     */
    public function getServicePid()
    {
        $handler = new PidFileHandler(['pidFile' => $this->config['settings']['pid_file'] ?? '']);
        return $handler->read();
    }

}

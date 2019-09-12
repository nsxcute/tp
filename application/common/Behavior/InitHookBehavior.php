<?php

namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

// 初始化钩子信息
class InitHookBehavior extends Behavior
{

    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
        if (defined('BIND_MODULE') && BIND_MODULE === 'Install') return;

        $data = S('hooks');
        if (!$data) {
            $hooks = M('Hooks')->getField('name,addons');
            foreach ($hooks as $key => $value) {
                if ($value) {
                    $map['status'] = 1;
                    $names = explode(',', $value);
                    $map['name'] = array('IN', $names);
                    $data = M('Addons')->where($map)->getField('id,name');
                    if ($data) {
                        $addons = array_intersect($names, $data);
//添加映射关系 这里的AdminIndex对应的是SiteStat,SystemInfo,DevTeam这三个插件，对应的实现分别在Addons\SiteStat\SiteStatAddon; Addons\SystemInfo\SystemInfoAddon; Addons\DevTeam\DevTeamAddon
                        Hook::add($key, array_map('get_addon_class', $addons));
                    }
                }
            }
            S('hooks', Hook::get());
        } else {
            Hook::import($data, false);
        }
    }
}
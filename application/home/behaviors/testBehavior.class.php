<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/17
 * Time: 11:02
 */
namespace Home\Behaviors;
/*
 * 注册钩子行为类，要触发的行为写在run函数里
 * */
class testBehavior extends \Think\Behavior{
    public function run(&$arg){
        echo 'test behavior=====下一行是参数<br/>'.$arg['name'];
    }
}
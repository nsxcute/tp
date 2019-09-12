<?php
/**
 * Created by zhuowenfeng.
 * User: Administrator
 * Date: 2016/7/17
 * Time: 10:56
 */
return array(
'action_begin'=>array('Home\\Behaviors\\test','Home\\Behaviors\\test'),
    //一个标签位可以有多个行为，使用数组即可。
    // 如果是3.2.1版本 则需要改成
    //'action_begin'=>array('Home\\Behaviors\\testBehavior','Home\\Behaviors\\testBehavior'),
    'mv'=>array('Home\\Behaviors\\mvBehavior','Home\\Behaviors\\mvBehavior')
);
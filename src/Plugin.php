<?php

namespace Miaoxing\LinkTo;

class Plugin extends \Miaoxing\Plugin\BasePlugin
{
    protected $name = '链接选择器';

    public function onLinkToGetLinks(&$links, &$types)
    {
        $types['url'] = [
            'name' => '网页',
            'input' => 'text',
            'sort' => 1000,
            'value' => 'http://',
        ];

        $types['browser'] = [
            'name' => '浏览器功能',
            'sort' => 100,
        ];

        $types['tel'] = [
            'name' => '一键拨号',
            'input' => 'text',
            'sort' => 200,
            'placeholder' => '请输入电话号码',
        ];

        $links[] = [
            'typeId' => 'browser',
            'name' => '后退',
            'url' => 'javascript:window.history.back();',
        ];

        $links[] = [
            'typeId' => 'browser',
            'name' => '前进',
            'url' => 'javascript:window.history.forward();',
        ];

        $links[] = [
            'typeId' => 'browser',
            'name' => '刷新',
            'url' => 'javascript:window.location.reload();',
        ];
    }

    public function onLinkToGetUrlTel($linkTo)
    {
        return 'tel:' . $linkTo['value'];
    }
}

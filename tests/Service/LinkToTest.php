<?php

namespace MiaoxingTest\LinkTo\Service;

/**
 * 链接选择器服务
 */
class LinkToTest extends \Miaoxing\Plugin\Test\BaseTestCase
{
    /**
     * 测试生成URL地址
     *
     * @dataProvider providerForGetUrl
     * @param mixed $config
     * @param mixed $url
     */
    public function testGetUrl($config, $url)
    {
        $this->assertEquals($url, wei()->linkTo->getUrl($config));
    }

    public function providerForGetUrl()
    {
        return [
            [
                [
                    'type' => 'url',
                    'value' => 'resources/new',
                ],
                '/resources/new',
            ],
        ];
    }
}

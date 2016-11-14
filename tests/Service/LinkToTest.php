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

    /*public function testGenerateOauth2UserInfoUrl()
    {
        $account = wei()->wechatAccount->getCurrentAccount();
        $account->setData([
            'verified' => true,
            'type' => WechatAccount::SERVICE,
        ]);
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $account['applicationId'] .  '&redirect_uri=' . urlencode('http://qq.com') . '&response_type=code&scope=snsapi_base&component_appid=#wechat_redirect';
        $this->assertEquals($url, wei()->linkTo->generateOauth2BaseUrl('http://qq.com'));
    }

    public function testGenerateOauth2UserInfoUrlWhenNotVerified()
    {
        $account = wei()->wechatAccount->getCurrentAccount();
        $account->setData([
            'verified' => false,
            'type' => WechatAccount::SERVICE,
        ]);
        $url = 'http://qq.com';
        $this->assertEquals($url, wei()->linkTo->generateOauth2BaseUrl('http://qq.com'));
    }

    public function testGenerateOauth2UserInfoUrlWhenNotServiceAccount()
    {
        $account = wei()->wechatAccount->getCurrentAccount();
        $account->setData([
            'verified' => true,
            'type' => WechatAccount::SUBSCRIBE,
        ]);
        $url = 'http://qq.com';
        $this->assertEquals($url, wei()->linkTo->generateOauth2BaseUrl('http://qq.com'));
    }*/
}

<?php

namespace Miaoxing\LinkTo\Service;

/**
 * @property \Wei\Url $url
 * @method string url($url = '', $argsOrParams = array(), $params = array())
 * @property \Wei\Request $request
 */
class LinkTo extends \Miaoxing\Plugin\BaseService
{
    /**
     * 链接的默认数据
     *
     * @var array
     */
    protected $defaults = [
        'type' => '',
        'value' => '',
        'decorator' => '',
    ];

    /**
     * 类型的默认数据
     *
     * @var array
     */
    protected $typeDefaults = [
        'id' => '',
        'name' => '',
        'input' => 'select',
        'sort' => 50,
        'placeholder' => '',
        'value' => '',
        'links' => [],
    ];

    public function __invoke(array $link)
    {
        if (!$link) {
            return '';
        }

        $method = 'generate' . ucfirst($link['type']);
        if (method_exists($this, $method)) {
            $url = $this->generateInternalUrl($link['url']);

            return $this->$method($url);
        }

        throw new \Exception(sprintf('Method "%s" not found for linkTo service', $link['type']));
    }

    /**
     * 获取类型,链接和修饰器配置
     *
     * @return array
     */
    public function getConfig()
    {
        // 1. 通过插件获取链接数据
        $links = $types = $decorators = [];
        $this->event->trigger('linkToGetLinks', [&$links, &$types, &$decorators]);

        // 2. 将链接加到类型中
        foreach ($links as $link) {
            $types[$link['typeId']]['links'][] = $link;
        }

        // 3. 过滤空的类型
        foreach ($types as $key => $type) {
            $type += ['id' => $key] + $this->typeDefaults;
            if ($type['input'] == 'select' && !$type['links']) {
                unset($types[$key]);
            } else {
                $types[$key] = $type;
            }
        }

        // 4. 对分类排序
        $types = $this->orderBy($types);

        return [
            'types' => $types,
            'decorators' => $decorators,
        ];
    }

    /**
     * 根据LinkTo配置,获取对应的URL地址
     *
     * @param array $linkTo
     * @return string
     */
    public function getUrl(array $linkTo)
    {
        // 1. 升级数据
        $linkTo = $this->upgrade($linkTo);

        // 2. 通过插件生成地址
        $event = 'linkToGetUrl' . ucfirst($linkTo['type']);
        $url = $this->event->until($event, [$linkTo]);
        if ($url) {
            return $url;
        }

        // 3. 如果是绝对地址,直接返回,否则通过URL服务处理
        if ($this->isAbsUrl($linkTo['value'])) {
            return $linkTo['value'];
        } else {
            return $this->url($linkTo['value']);
        }
    }

    /**
     * 根据LinkTo配置,获取完整的URL地址
     *
     * @param array $linkTo
     * @return string
     */
    public function getFullUrl(array $linkTo)
    {
        $url = $this->getUrl($linkTo);

        if (!wei()->isStartsWith($url, 'http')) {
            $url = wei()->request->getUrlFor($url);
        }

        // 按需添加OAuth2.0验证
        $url = $this->decorateUrl($url, $linkTo['decorator']);

        return $url;
    }

    /**
     * 生成带微信登录的URL地址
     *
     * @param array $linkTo
     * @return string
     * @throws \Exception
     */
    public function getUrlWithDecorator(array $linkTo)
    {
        // 生成基本的URL
        $url = $this->getUrl($linkTo);

        // 如果不是已http开头,认为是内部地址,附加前面的http头,转换为完整的地址
        if (!wei()->isStartsWith($url, 'http')) {
            $url = $this->request->getUrlFor($url);
        }

        // 如果没有设置decorator,才生成带微信登录态的内部地址
//        if (!$linkTo['decorator']) {
//            $url = $this->generateInternalUrl($url);
//        }

        // 按需添加OAuth2.0验证
        $url = $this->decorateUrl($url, $linkTo['decorator']);

        return $url;
    }

    /**
     * 根据修饰器,更新URL
     *
     * @param $url
     * @param $decorator
     * @return mixed
     * @throws \Exception
     */
    protected function decorateUrl($url, $decorator)
    {
        // 设置了修饰器
        if ($decorator) {
            $method = 'generate' . ucfirst($decorator) . 'Url';
            if (method_exists($this, $method)) {
                $url = $this->$method($url);
            } else {
                throw new \Exception(sprintf('Method "%s" not found for linkTo service', $method));
            }
        }

        return $url;
    }

    /**
     * 生成带微信登录态的内部地址
     *
     * @param string $url
     * @return string
     */
    public function generateInternalUrl($url)
    {
        // Step1 URL自动加上当前用户OpenID
        $url .= parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $url .= 'wechatOpenId=' . $this->wei->weChatApp->getFromUserName();

        // Step2 通过安全URL服务对wechatOpenId字段进行签名
        $url = $this->wei->safeUrl->generate($url, 'wechatOpenId');

        return $url;
    }

    /**
     * 生成微信OpenID授权网页地址
     *
     * @param string $url
     * @return string
     */
    public function generateOauth2BaseUrl($url)
    {
        return $this->oauth2Url($url, 'snsapi_base');
    }

    /**
     * 生成微信用户信息授权网页地址
     *
     * @param $url
     * @return string
     */
    public function generateOauth2UserInfoUrl($url)
    {
        return $this->oauth2Url($url, 'snsapi_userinfo');
    }

    /**
     * @param string $url
     * @param string $scope
     * @return string
     */
    protected function oauth2Url($url, $scope)
    {
        // todo 兼容不同的平台
        if ($this->app->getNamespace() == 'plst') {
            $account = wei()->wechatCorpAccount->getCurrentAccount();
        } else {
            $account = wei()->wechatAccount->getCurrentAccount();
        }

        if ($account->isVerifiedService()) {
            return $account->getOauth2Url($url, $scope);
        } else {
            return $url;
        }
    }

    /**
     * 二维数组排序
     *
     * @param array $array
     * @param string $key
     * @param int $type
     * @return array
     */
    protected function orderBy(array $array, $key = 'sort', $type = SORT_DESC)
    {
        $array2 = [];
        foreach ($array as $k => $v) {
            $array2[$k] = $v[$key];
        }
        array_multisort($array2, $type, $array);

        return $array;
    }

    /**
     * 转换数组为字符串供保存到数据库
     *
     * @param array $data
     * @return string
     */
    public function encode($data)
    {
        $data = (array) $data + $this->defaults;
        $data = array_intersect_key($data, $this->defaults);

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    /**
     * 转换字符串为数组
     *
     * @param string $data
     * @return array
     */
    public function decode($data)
    {
        $data = (array) json_decode($data, true);

        return $this->upgrade($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function upgrade($data)
    {
        if (!$data) {
            return $data;
        }

        if (isset($data[$data['type']])) {
            $data['value'] = $data[$data['type']];
            unset($data[$data['type']]);
        }

        if (!isset($data['value'])) {
            $data['value'] = '';
        }

        return $data;
    }

    protected function isAbsUrl($url)
    {
        if (!$url) {
            return false;
        }

        if ($url[0] == '/') {
            return true;
        }

        if (parse_url($url, PHP_URL_SCHEME) != '') {
            return true;
        }

        return false;
    }
}

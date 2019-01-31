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

    /**
     * 获取类型,链接和修饰器配置
     *
     * @return array
     */
    public function getConfig()
    {
        // 1. 通过插件获取链接数据
        $links = $types = [];
        $this->event->trigger('linkToGetLinks', [&$links, &$types]);

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
        ];
    }

    /**
     * 根据LinkTo配置,获取对应的URL地址
     *
     * @param array|null $linkTo
     * @return string
     */
    public function getUrl($linkTo)
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
     * @param array|null $linkTo
     * @return string
     */
    public function getFullUrl($linkTo)
    {
        $url = $this->getUrl($linkTo);

        if (!wei()->isStartsWith($url, 'http')) {
            $url = wei()->request->getUrlFor($url);
        }

        return $url;
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
        if ($this->isEmpty($data)) {
            return '{}';
        }

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

    protected function isEmpty($data)
    {
        return !$data || !$data['type'];
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

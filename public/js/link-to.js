define(['module', 'template'], function (module, template) {
  var LinkTo = function (options) {
    if (options) {
      $.extend(this, options);
    }

    this.initialize.apply(this, arguments);
  };

  $.extend(LinkTo.prototype, {
    /**
     * 调用插件的元素
     *
     * @link http://stackoverflow.com/questions/10636667/bootstrap-modal-appearing-under-background
     */
    $el: null,

    /**
     * 弹出的选择框
     */
    $modal: null,

    /**
     * 点击弹出选择器的元素
     */
    $link: null,

    /**
     * 链接类型元素
     */
    $type: null,

    /**
     * 修饰器下拉菜单元素
     */
    $decorator: null,

    /**
     * 链接数据
     */
    data: {
      type: '', // 链接的类型,如网页,官网,用户
      value: '', // 类型对应的值,如具体的URL地址,后退的JS代码
      decorator: '' // 链接的附加修饰器,如附加微信OpenID授权
    },

    /**
     * 要隐藏的类型
     */
    hide: {
      // keyword: true
    },

    /**
     * 表单的name值
     */
    name: 'linkTo',

    /**
     * 点击确定的回调事件
     */
    update: null,

    /**
     * 链接显示的文案
     */
    linkText: '设置链接',

    /**
     * 默认类型
     */
    defaultType: 'url',

    /**
     *  显示为输入框的链接类型,其他都是选择框(select)
     */
    inputTypes: ['keyword', 'url', 'tel'],

    /**
     * 子链接不是http的类型,http类型可以被修饰器修饰
     */
    nonHttpTypes: ['browser', 'keyword', 'tel'],

    /**
     * 类型和链接数据
     */
    types: [],

    /**
     * 修饰器数据,如微信Oauth2登录
     */
    decorators: [],

    $: function (selector) {
      return this.$el.find(selector);
    },

    initialize: function () {
      // 如果未设置$el,认为不是通过插件方式初始化
      if (this.$el) {
        this.init();
      }
    },

    /**
     * 初始化选择器,用于快速调用
     */
    init: function (options) {
      var that = this;
      $.extend(this, options);

      // 1. 升级老数据,兼容类型,地址不存在的情况
      this.data = this.upgradeData(this.data);

      // 2. 生成点击的链接和文案
      this.$link = $('<a href="javascript:;"></a>').appendTo(this.$el);
      this.updateLink(this.data);

      // 3. 渲染模板
      template.helper('$', $);
      this.$el.append(template.render('linkToTpl', this));

      // 4. 绑定事件
      this.$type = this.$('.js-link-to-type');
      this.$modal = this.$('.js-link-to-modal');
      this.$decorator = this.$('.js-link-to-decorator');
      var $doc = $(document);
      var $type = this.$type;
      var $modal = this.$modal;

      // 4.1 点击链接显示选择框
      this.$link.on('click', $.proxy(this.show, this));

      // 4.2 更换类型时,显示对应的表单
      var preType = null;
      $type.change(function () {
        $modal.find('.js-link-to-' + preType).hide();
        $modal.find('.js-link-to-' + (preType = $type.val())).show();
        $doc.trigger($.Event('linkToChangeType', {
          $el: that.$el,
          curType: preType,
          value: that.data.value
        }));
      });

      // 4.3 点击确定按钮更新文案
      this.$('.js-link-to-confirm').click(function () {
        that.confirmData();
      });

      this.initDecorator();
      this.loadData(this.data);
    },

    /**
     * Repo: 根据提供的数据,直接渲染出链接
     */
    renderLink: function (data, url) {
      if (!data || !data.type) {
        return '无';
      }
      data = this.upgradeData(data);
      return '<a href="' + url + '" target="_blank">' + this.getText(data) + '</a>';
    },

    /**
     * 显示选择器
     */
    show: function () {
      this.$modal.modal('show');
    },

    /**
     * 更新文案,并触发update回调
     */
    confirmData: function () {
      var data = this.getData();
      this.updateLink(data);
      if (this.update) {
        this.update.call(this.$el, data);
      }
    },

    /**
     * 初始化修饰器
     */
    initDecorator: function () {
      // 如果选择了链接,显示链接的附加属性
      var that = this;
      this.$type.change(function () {
        if (that.isHttpType($(this).val())) {
          that.$decorator.show();
        } else {
          that.$decorator.hide();
        }
      });
    },

    /**
     * 加载数据到链接选择器中
     */
    loadData: function (data) {
      if (data.type) {
        this.$type.val(data.type).trigger('change');
        this.$('.js-link-to-value').val(data.value);
        this.$('.js-link-to-input-' + data.type).val(data.value).trigger('change');
        this.$('.js-link-to-input-decorator').val(data.decorator);
      } else {
        this.$type.find('option:first').prop('selected', true);
        this.$type.trigger('change');
      }
    },

    /**
     * 升级数据
     */
    upgradeData: function (data) {
      if (!data) {
        return {};
      }

      if (!data.type) {
        return data;
      }

      // 1. 类型不存在,如老数据,插件关闭等情况,改为默认类型
      if (typeof this.types[data.type] === 'undefined') {
        data.type = this.defaultType;
      }

      // 2. 如果是select类型,但是链接不存在,改为默认类型
      var type = this.types[data.type];
      if (type.input !== 'select') {
        return data;
      }

      var found = false;
      $.each(type.links, function (key, link) {
        if (link.url === data.value) {
          found = true;
          return false;
        }

        return true;
      });
      if (found === false) {
        data.type = this.defaultType;
      }

      return data;
    },

    /**
     * 获取链接选择器的数据
     */
    getData: function () {
      var data = {};
      data.type = this.$type.val();

      if (data.type === '') {
        // 返回空字符串,而不是空对象{},$.param才会正确生成链接
        return '';
      }

      data.decorator = this.$('.js-link-to-input-decorator').val();
      data.value = this.$('.js-link-to-input-' + data.type).val();
      return data;
    },

    /**
     * 根据linkTo数据,获取显示文案
     */
    getText: function (data) {
      return this.getNames(data).join(' » ');
    },

    /**
     * 根据修饰器获取修饰器的名称
     */
    getDecoratorName: function (decorator) {
      return this.decorators[decorator].name;
    },

    /**
     * 根据linkTo数据,获取文案数组
     */
    getNames: function (data) {
      var names = [];
      if (!data || !data.type) {
        return names;
      }

      var type = data.type;
      var value = data.value;

      // 1. 修饰器
      if (data.decorator && this.isHttpType(type)) {
        names.push(this.getDecoratorName(data.decorator));
      }

      // 2. 类型
      names.push(this.types[type].name);

      // 3.1 input的链接
      if (this.isInputType(type)) {
        if (typeof value !== 'undefined') {
          names.push(value);
        }
        return names;
      }

      // 3.2 select的链接并找到数据
      var links = this.types[type].links;
      for (var i in links) {
        if (links[i].url === value) {
          names.push(links[i].name);
          return names;
        }
      }

      // 3.3 找不到对应的数据,通过事件获取
      var result = {
        name: null
      };
      $(document).trigger($.Event('linkToGetName', {linkTo: data}), result);
      if (result.name) {
        names.push(result.name);
        return names;
      }

      // 3.4 通过事件也取不到,直接附加原始数据
      names.push(value);
      return names;
    },

    /**
     * 判断链接类型的值是否为输入框
     */
    isInputType: function (type) {
      return $.inArray(type, this.inputTypes) !== -1;
    },

    /**
     * 判断链接类型的链接是否为http链接,即可以被修饰器修饰
     */
    isHttpType: function (type) {
      return type && $.inArray(type, this.nonHttpTypes) === -1;
    },

    /**
     * 更新文案到链接中
     */
    updateLink: function (data) {
      // 同步到隐藏框中
      this.$('.js-link-to-value').val(data.value);
      var text = this.getText(data) || this.linkText;
      this.$link.html(text);
    }
  });

  // 合并配置
  $.extend(LinkTo.prototype, module.config());

  /**
   * 将类变为jQuery插件
   */
  $.fn.linkTo = function (options) {
    return this.each(function () {
      var $this = $(this);
      var data = $this.data('linkTo');
      if (typeof options === 'object') {
        options.$el = $this;
      }

      if (!data) {
        $this.data('linkTo', (data = new LinkTo(options)));
      }

      if (typeof options === 'string') {
        data[options](data);
      }
    });
  };
  $.fn.linkTo.Constructor = LinkTo;

  /**
   * 返回实例化,供快速调用
   */
  return new LinkTo();
});

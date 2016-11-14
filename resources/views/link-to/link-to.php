<?= $block('js') ?>
<script>
  require.config({
    config: {
      linkTo: <?= json_encode($wei->linkTo->getConfig()) ?>
    }
  });
</script>
<?= $block->end() ?>

<script id="linkToTpl" type="text/html">
  <div class="js-link-to-modal modal fade text-left" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">设置链接</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-xs-3 control-label">
              类型
            </label>

            <div class="link-controls col-xs-7">
              <select class="js-link-to-type form-control" name="<%= name %>[type]">
                <option value="">无</option>
                <% $.each(types, function (id, type) { %>
                  <% if (!hide[id]) { %>
                    <option value="<%= type.id %>"><%= type.name %></option>
                  <% } %>
                <% }) %>
              </select>
            </div>
          </div>

          <% $.each(types, function (id, type) { %>
            <% if (type.input == 'custom') { return } %>
            <div class="js-link-to-<%= type.id %> form-group display-none">
              <label class="col-xs-3 control-label" for="link-to-type">
                链接到
              </label>

              <div class="col-xs-7">
                <!-- select, input之外的类型不显示,由插件自己输出 -->
                <% if (type.input == 'select') { %>
                  <select class="js-link-to-input-<%= type.id %> form-control">
                    <% $.each(type.links, function (id, link) { %>
                      <option value="<%= link.url %>"><%= link.name %></option>
                    <% }) %>
                  </select>
                <% } else { %>
                  <input type="text" class="js-link-to-input-<%= type.id %> form-control" value="<%= type.value %>"
                         placeholder="<%= type.placeholder %>">
                <% } %>
              </div>
            </div>
          <% }) %>
          <?php $event->trigger('linkToRenderInput') ?>
          <input type="hidden" class="js-link-to-value" name="<%= name %>[value]">

          <% if (!hide.decorator) { %>
            <div class="js-link-to-decorator form-group display-none">
              <label class="col-xs-3 control-label">
                附加
              </label>

              <div class="col-xs-7">
                <select class="js-link-to-input-decorator form-control" name="<%= name %>[decorator]">
                  <option value="">无</option>
                  <% $.each(decorators, function(id, decorator) { %>
                    <option value="<%= id %>"><%= decorator.name %></option>
                  <% }) %>
                </select>
              </div>
            </div>
          <% } %>
        </div>
        <div class="modal-footer">
          <button type="button" class="js-link-to-confirm btn btn-info" data-dismiss="modal">确定</button>
        </div>
      </div>
    </div>
  </div>
</script>

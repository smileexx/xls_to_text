<div>
    <div class="dictionary-list">
        <div class="row">
            <div class="col-xs-1">ID</div>
            <div class="col-xs-4">Производитель (произвольно)</div>
            <div class="col-xs-4">КОД (прописью, значение из базы)</div>
            <div class="col-xs-2"></div>
        </div>
        <div class="vendor-list">
            <?php foreach ($data['vendors'] as $key => $val) { ?>
                <div class="row"  data-id="<?php echo $val['id']; ?>">
                    <div class="col-xs-1"><?php echo $val['id']; ?></div>
                    <div class="col-xs-4"><?php echo $val['title']; ?></div>
                    <div class="col-xs-4"><?php echo $val['code']; ?></div>
                    <div class="col-xs-2">
                        <button type="button" class="btn btn-danger vendor-delete" data-id="<?php echo $val['id']; ?>">Удалить</button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Button trigger modal -->
<button type="button" class="btn btn-success btn-lg add-item-btn-fixed" data-toggle="modal" data-target="#vendor-item-edit-modal">Добавить</button>
<!-- Modal -->
<div class="modal fade" id="vendor-item-edit-modal" tabindex="-1" role="dialog" aria-labelledby="vendorModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="vendorModalLabel">Новый производитель</h4>
            </div>
            <div class="modal-body">
                <form id="new-dictionary-vendor">
                    <div class="form-group">
                        <label for="new-vendor">Производитель (название, произвольно)</label>
                        <input type="text" class="form-control" id="new-vendor" name="title" placeholder="Duka super title">
                    </div>
                    <div class="form-group">
                        <label for="new-vendor-code">КОД (прописью, значение из базы)</label>
                        <input type="text" class="form-control" id="new-vendor-code" name="code" placeholder="duka">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-add-vendor">Созранить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
            </div>
        </div>
    </div>
</div>

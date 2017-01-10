<div>
    <!--<div id="dictionary-filter" class="form-group">
        <label for="list-type" class="col-sm-3 control-label">Поставщик: </label>
        <div class="col-sm-9">
            <select id="list-type" name="type" class="form-control">
                <option value="aquademi">Аквадеми</option>
                <option value="antei">Антей</option>
                <option value="bulbashka">Бульбашка</option>
                <option value="ubm">УБМ</option>
                 <option value="armoni">Армони</option>
                 <option value="germes">Гермес</option>
                 <option value="marko">Марко треви</option>
                 <option value="metaplan">Метаплан(оптгрупп)</option>
            </select>
        </div>
    </div>-->
    <div class="dictionary-list">
        <div class="row">
            <div class="col-xs-5">Income hash</div>
            <div class="col-xs-2">Vendor</div>
            <div class="col-xs-2">ProductID</div>
            <div class="col-xs-2">Action</div>
        </div>
        <div class="articles-list">
            <?php echo $data['articles']; ?>
        </div>
    </div>
</div>

<!-- Button trigger modal -->
<button type="button" class="btn btn-success btn-lg add-item-btn-fixed" data-toggle="modal" data-target="#dictionary-item-edit-modal">Add</button>
<!-- Modal -->
<div class="modal fade" id="dictionary-item-edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">New custom article</h4>
            </div>
            <div class="modal-body">
                <form id="new-dictionary-article">
                    <div class="form-group">
                        <label for="new-input-hash">Input hash</label>
                        <input type="text" class="form-control" id="new-input-hash" name="income_hash" placeholder="Input hash">
                    </div>
                    <div class="form-group">
                        <label for="new-vendor">Vendor</label>
                        <select class="form-control" id="new-vendor" name="vendor">
                            <?php foreach ($data['vendors'] as $vendor) { ?>
                                <option value="<?php echo $vendor['code']; ?>"><?php echo $vendor['title']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-product-id">Product ID</label>
                        <input type="text" class="form-control" id="new-product-id" name="product_id" placeholder="Product ID">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-add-article">Save changes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

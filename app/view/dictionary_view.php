<div>
    <div id="dictionary-filter" class="form-group">
        <label for="list-type" class="col-sm-3 control-label">Поставщик: </label>
        <div class="col-sm-9">
            <select id="list-type" name="type" class="form-control">
                <option value="aquademi">Аквадеми</option>
                <option value="antei">Антей</option>
                <option value="bulbashka">Бульбашка</option>
                <option value="ubm">УБМ</option>
                <!-- <option value="armoni">Армони</option>
                 <option value="germes">Гермес</option>
                 <option value="marko">Марко треви</option>
                 <option value="metaplan">Метаплан(оптгрупп)</option> -->
            </select>
        </div>
    </div>
    <div class="dictionary-list">
        <div class="row">
            <div class="col-xs-2">Hash</div>
            <div class="col-xs-1">Orig. Article</div>
            <div class="col-xs-1">ProductID</div>
            <div class="col-xs-4">Orig. Title</div>
        </div>
        <div class="bad-items-list">
            <?php echo $data['bad_list']; ?>
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
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<script type="application/javascript">
    $('#myModal').on('shown.bs.modal', function () {
        $('#myInput').focus()
    })
</script>
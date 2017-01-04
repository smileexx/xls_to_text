<div class="container">
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
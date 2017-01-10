<?php foreach ($data['articles'] as $key => $val) { ?>
    <div class="row"  data-id="<?php echo $val['id']; ?>">
        <div class="col-xs-5"><?php echo $val['income_hash']; ?></div>
        <div class="col-xs-2"><?php echo $val['vendor']; ?></div>
        <div class="col-xs-2"><?php echo $val['product_id']; ?></div>
        <div class="col-xs-2">
            <button type="button" class="btn btn-danger article-delete" data-id="<?php echo $val['id']; ?>">Delete</button>
        </div>
    </div>
<?php } ?>

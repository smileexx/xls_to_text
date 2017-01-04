<?php foreach ($data['bad_list'] as $key => $val) { ?>
    <div class="row"  data-id="<?php echo $val['id']; ?>">
        <div class="col-xs-2"><?php echo $val['hash']; ?></div>
        <div class="col-xs-1"><?php echo $val['original_hash']; ?></div>
        <div class="col-xs-1"><?php echo $val['product_id']; ?></div>
        <div class="col-xs-4"><?php echo $val['description']; ?></div>
    </div>
<?php } ?>
some text

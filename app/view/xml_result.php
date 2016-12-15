<div>
    <?php echo "<a href=\"{$data['download_link']}\" target=\"_blank\">{$data['download_link']}</a><br /><hr>" ; ?>
</div>
<div class="result-table">
    <div class="row">
        <div class="col-xs-2">Hash</div>
        <div class="col-xs-1">Amount</div>
        <div class="col-xs-1">ProductID</div>
        <div class="col-xs-1">Orig. Article</div>
        <div class="col-xs-1">Orig. Amount</div>
        <div class="col-xs-1">Vendor</div>
        <div class="col-xs-4">Orig. Title</div>
        <div class="col-xs-1">Duplicate ProductID</div>
    </div>
    <?php foreach ($data['pricelist']['price'] as $key => $val) { ?>
        <div class="row">
            <div class="col-xs-2"><?php echo $val['article']; ?></div>
            <div class="col-xs-1"><?php echo $val['amount']; ?></div>
            <div class="col-xs-1"><?php echo $val['product_id']; ?></div>
            <div class="col-xs-1"><?php echo $val['orig_article']; ?></div>
            <div class="col-xs-1"><?php echo $val['orig_amount']; ?></div>
            <div class="col-xs-1"><?php echo ($val['vendor']) ? $val['vendor'] : '' ; ?></div>
            <div class="col-xs-4"><?php echo $val['title']; ?></div>
            <div class="col-xs-1"><?php echo $val['duplicate']; ?></div>
        </div>
    <?php } ?>
</div>
<hr>
<div class="skipped-results">
    <h2>Skipped results</h2>
</div>
<?php
if($data['pricelist']['error']){
    foreach ($data['pricelist']['error'] as $key => $val){
        echo "<div>".$val."</div>";
    }
}
?>
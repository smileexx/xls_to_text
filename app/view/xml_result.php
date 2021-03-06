<div class="row">
    <div class="col-xs-2">Распознано: </div><div class="col-xs-10"><?php echo "<a href=\"{$data['download_link']}\" target=\"_blank\">{$data['download_link']}</a>" ; ?></div>
</div>
<div class="row">
    <div class="col-xs-2">НЕ распознано: </div><div class="col-xs-10"><?php echo "<a href=\"{$data['download_unrecognized']}\" target=\"_blank\">{$data['download_unrecognized']}</a>" ; ?></div>
</div>
<div class="result-table">
    <div class="row">
        <div class="col-xs-2">Артикул (хеш)</div>
        <div class="col-xs-1">Количество</div>
        <div class="col-xs-1">ID продукта</div>
        <div class="col-xs-1">Артикул ориг.</div>
        <div class="col-xs-1">Колич. ориг.</div>
        <div class="col-xs-1">Производитель</div>
        <div class="col-xs-4">Текст</div>
        <div class="col-xs-1">Дубликаты</div>
    </div>
    <?php foreach ($data['pricelist']['price'] as $key => $val) { ?>
        <div class="row">
            <div class="col-xs-2"><?php echo (isset($val['article'])) ? $val['article'] : ''; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['amount'])) ? $val['amount'] : '' ; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['product_id'])) ? $val['product_id'] : ''  ; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['orig_article'])) ? $val['orig_article'] : '' ; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['orig_amount'])) ? $val['orig_amount'] : '' ; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['vendor'])) ? $val['vendor'] : '' ; ?></div>
            <div class="col-xs-4"><?php echo (isset($val['title'])) ? $val['title'] : '' ; ?></div>
            <div class="col-xs-1"><?php echo (isset($val['duplicate'])) ? $val['duplicate'] : '' ; ?></div>
        </div>
    <?php } ?>
</div>
<hr>
<div class="skipped-results">
    <h2>Пропущеные данные</h2>
</div>
<?php
if( !empty($data['pricelist']['error']) ){
    foreach ($data['pricelist']['error'] as $key => $val){
        echo "<div>".$val."</div>";
    }
}
?>
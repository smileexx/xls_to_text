<div>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-articles">Артикулы</a></li>
        <li><a data-toggle="tab" href="#tab-vendors">Производители</a></li><!--
        <li><a data-toggle="tab" href="#menu2">Menu 2</a></li>
        <li><a data-toggle="tab" href="#menu3">Menu 3</a></li>-->
    </ul>

    <div class="tab-content">
        <div id="tab-articles" class="tab-pane fade in active">
            <h3>Артикулы</h3>
            <div>
                <?php echo $data['tab_articles']; ?>
            </div>
        </div>
        <div id="tab-vendors" class="tab-pane fade">
            <h3>Производители</h3>
            <div>
                <?php echo $data['tab_vendors']; ?>
            </div>
        </div>
<!--        <div id="menu2" class="tab-pane fade">
            <h3>Menu 2</h3>
            <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
        </div>
        <div id="menu3" class="tab-pane fade">
            <h3>Menu 3</h3>
            <p>Eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
        </div>-->
    </div>
</div>
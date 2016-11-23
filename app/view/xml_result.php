<div>
    <?php echo "<a href=\"{$data['download_link']}\" target=\"_blank\">{$data['download_link']}</a><br /><hr>" ; ?>
</div>
<table>
    <thead>
    <tr>
        <th>Hash</th>
        <th>Amount</th>
        <th>ProductID</th>
        <th>Orig. Article</th>
        <th>Orig. Amount</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data['pricelist'] as $key => $val) {
        $hash = $val[0]; ?>
    <tr>
        <td><?php echo $hash; ?></td>
        <td><?php echo $val[1]; ?></td>
        <td><?php if (isset($data['hash_product'][$hash])) {
                $str = $data['hash_product'][$hash];

                if(isset($duplicate[$hash])){
                    $str .= ', '.implode(', ', $duplicate[$hash]);
                }
                echo $str;
            } ?></td>
        <td><?php echo $val['article']; ?></td>
        <td><?php echo $val['amount']; ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<div class="container">
    <form id="load-xml-form" class="form-horizontal" action="/xml" enctype="multipart/form-data" method="POST">
        <!-- Поле MAX_FILE_SIZE должно быть указано до поля загрузки файла -->
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
        <!-- Название элемента input определяет имя в массиве $_FILES -->
        <div class="form-group">
            <label for="field-xml-file" class="col-sm-3 control-label">Выбрать файл XML: </label>
            <div class="col-sm-9">
                <input id="field-xml-file" class="form-control" name="xml" type="file"
                       accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"/>
            </div>
        </div>
        <div class="form-group">
            <label for="list-type" class="col-sm-3 control-label">Поставщик: </label>
            <div class="col-sm-9">
                <select id="list-type" name="type" class="form-control">
                    <option value="0">Aqua</option>
                    <option value="1">Bulbash</option>
                    <option value="2">UBM</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-default">Загрузить</button>
    </form>
</div>
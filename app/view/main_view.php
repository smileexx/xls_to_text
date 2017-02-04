<form id="load-xml-form" class="form-horizontal" action="/xml" enctype="multipart/form-data" method="POST">
    <!-- Поле MAX_FILE_SIZE должно быть указано до поля загрузки файла -->
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
    <!-- Название элемента input определяет имя в массиве $_FILES -->
    <div class="form-group">
        <label for="field-xml-file" class="col-sm-3 col-md-2 control-label">Выбрать файл XML: </label>
        <div class="col-sm-9 col-md-10">
            <input id="field-xml-file" class="form-control" name="xml" type="file"
                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"/>
        </div>
    </div>
    <div class="form-group">
        <label for="list-type" class="col-sm-3 col-md-2 control-label">Поставщик: </label>
        <div class="col-sm-9 col-md-10">
            <select id="list-type" name="type" class="form-control">
                <option value="aquademi">Аквадеми</option>
                <option value="antei">Антей</option>
                <option value="bulbashka">Бульбашка</option>
                <option value="ubm">УБМ</option>
                <option value="germes">Гермес</option>
                <option value="mtg">МТГ</option>
                <option value="optgroup">Опт Груп</option>
                <!-- <option value="armoni">Армони</option>
                 <option value="marko">Марко треви</option>
                 <option value="metaplan">Метаплан(оптгрупп)</option> -->
             </select>
         </div>
     </div>

     <button type="submit" class="btn btn-default">Загрузить</button>
 </form>
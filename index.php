<?php session_start() ?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>База данных костюмов</title>
        <link rel="stylesheet" href="static/css/tables_styles.css"/>
        <link rel="stylesheet" href="static/css/bootstrap.min.css"/>
        
        <style>
            /* Временные стили для изображений, пока не добавлены реальные */
            .temp-img {
                width: 50px;
                height: 50px;
                background-color: #eee;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #999;
            }
        </style>
    </head>
    <body>
        <?php $selector_warehouse = '<select>
                                <option>Грация</option>
                                <option>Каб. 4</option>
                                <option>О. Вершинина</option>
                                <option>Поющие руки</option>
                            </select>';
        ?>
        
        <!-- Навигационная панель (заголовки сверху) -->
        <nav class="nav db-nav">
            <a class="nav-link div-link" href="#">Костюмы</a>
            <a class="nav-link div-link" href="#">Сувениры</a>
            <span class="ms-auto" style="display: flex;">
                    <a class="nav-link div-link" href="#">Настройки баз данных</a>
                    <a class="nav-link div-link" href="#">Выйти</a>
            </span>
        </nav>
        
        <form class="d-flex search-table" role="search" method="POST" action="" id="search-form">
            <input class="form-control me-2" type="search" placeholder="Поиск" name="q"
                   aria-label="Search" id="search-input">
            <button class="btn btn-outline-success" type="submit">Поиск</button>
            <div id="search-results-container"></div>
        </form>
        
        <div id="table-costumes">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Категория</th>
                        <th>Фото</th>
                        <th>Инвентаризационный номер</th>
                        <th>Размерный ряд</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Место хранения</th>
                        <th>Примечание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Чтение .csv файла с данными по костюмам
                        $row = 1;
                        $header_types = Array();
                        if (($handle = fopen("db/Пример таблицы костюмов.csv", "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                                echo "<tr dataset-id='" . $data[0] ."'>";
                                $num = count($data);
                                if ($row > 1) {
                                    for ($c=0; $c < $num; $c++) {
                                        // Если строка вторая, то это типы столбцов
                                        // Считываем их в отдельный список и пропускаем цикл
                                        if ($row === 2) {
                                            $header_types = array_slice($data, offset: 1);
                                            break;
                                        }
                                        if ($c !== 0) { // Если не id
                                            if ($c === 2) { // Если картинка
                                                // Рисуем картинку в таблице размером 100*100
                                                echo "<td><img width='100' height='100'"
                                                . " src='static/img/" . $data[$c] .
                                                        "' alt='" . $data[$c] ."'></td>";
                                            }
                                            else {
                                                // иначе выводим текстовое содержимое
                                                echo "<td>" . htmlspecialchars($data[$c]) . "</td>";
                                            }
                                        }
                                    }
                                    echo "</tr>";
                                }
                                $row++;  // Переходим на следующую строку
                            }
                            fclose($handle);  // закрытие файла
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div id="info-box" style="display: none;">
            
        </div>
        
    <script type="text/javascript">
        document.querySelectorAll('.data-table select').forEach(select => {
            select.style.width = 'auto';
            select.style.width = (select.scrollWidth + 20) + 'px';
        });

        const table = document.querySelector('.data-table');
        const rows = table.getElementsByTagName('tr');
        const headers = table.getElementsByTagName('th');
        const headerTypes = <?php echo json_encode($header_types, JSON_UNESCAPED_UNICODE); ?>;
        const infoBox = document.getElementById('info-box'); // исправлен ID
        
        // Кнопки сохранения и удаления предмета в отедльном окне
        const infoBoxButtons = '<div class="d-flex justify-content-end">\
                                <button type="button" class="btn btn-primary table-info-btn">Сохранить</button>\n\
                                <button type="button" class="btn btn-danger table-info-btn">Удалить</button>\n\
                                </div>';
;

        // Для каждой строки таблицы, кроме первой добавяем событие клика,
        // которое показывает информацию по предмету и позволяет его редактировать
        // в отдельном окне
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            row.addEventListener('click', function(event) {
                event.stopPropagation(); // предотвращаем всплытие

                let contentInfo = `<span class="close-btn" style="font-size:30px;">×</span>
                                  <h3>Информация о предмете</h3>
                                  <form action="" method="POST">`;

                for (let cell = 0; cell < headers.length; cell++) {
                    if (row.cells[cell]) {
                        contentInfo += `
                            <div class="info-header">
                                <label for="input-n${cell}">
                                    <b>${headers[cell].textContent}:</b>
                                </label>`;

                        switch (headerTypes[cell]) {
                            case 'int':
                                contentInfo += `
                                    <input type="number" class="input-info"
                                           id="input-n${cell}"
                                           value="${row.cells[cell].textContent.trim()}">`;
                                break;
                            case 'list_of_int':
                            case 'str':
                                contentInfo += `
                                    <input type="text" class="input-info"
                                           id="input-n${cell}"
                                           value="${row.cells[cell].textContent.trim()}">`;
                                break;
                            case 'img':
                                contentInfo += `
                                    <input type="file" class="input-info"
                                           id="input-n${cell}"
                                           accept=".png,.jpg,.jpeg">`;
                                break;
                            case 'list_of_str':
                                contentInfo += `<?php echo $selector_warehouse; ?>`;
                                break;
                        }

                        contentInfo += `</div>`; // Закрываем .info-header
                    }
                }
                contentInfo += infoBoxButtons;
                contentInfo += "</form>";

                infoBox.innerHTML = contentInfo;
                infoBox.style.display = 'block';

                // Обработчик закрытия
                infoBox.querySelector('.close-btn').addEventListener('click', closeInfoBox);
            });
        }

        function closeInfoBox() {
            document.getElementById('info-box').style.display = 'none';
        }

        // Закрытие при клике вне окна
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#info-box') && !event.target.closest('tr')) {
                closeInfoBox();
            }
        });
    </script>
    <script src="static/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
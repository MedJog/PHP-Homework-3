<?php

function readAllFunction(array $config) : string {
    $address = $config['storage']['address'];
    
    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");
        
        $contents = ''; 
    
        while (!feof($file)) {
            $contents .= fread($file, 100);
        }
        
        fclose($file);
        return $contents;
    }
    else {
        return handleError("Файл не существует");
    }
}
// Функция добавления записи в файл
function addFunction(array $config) : string {
    $address = $config['storage']['address'];
    // консольный ввод данных
    $name = readline("Введите имя и фамилию: ");
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");
    // Проверка корректности введенной даты
    if(validate($date)){
        $data = $name . ", " . $date . "\r\n";

        // Попытка открыть файл для добавления данных
        $fileHandler = @fopen($address, 'a');

        if ($fileHandler === false) {
            return handleError("Произошла ошибка открытия файла $address. Проверьте права доступа.");
        } elseif (fwrite($fileHandler, $data) === false) {
            return handleError("Произошла ошибка записи. Данные не сохранены.");
        } else {
            return "Запись $data добавлена в файл $address";
        }
        
        fclose($fileHandler);
    } else {
        return handleError("Введена некорректная информация");
    }
}
// Функция для проверки корректности даты
function validate(string $date): bool {
    $datePattern = '/^\d{2}-\d{2}-\d{4}$/'; // Формат: ДД-ММ-ГГГГ
    $dateBlocks = explode("-", $date);

    // Проверка формата даты
    if (!preg_match($datePattern, $date)) {
        return false;
    }

    list($day, $month, $year) = $dateBlocks;

    // Проверка дня
    if ($day < 1 || $day > 31) {
        return false;
    }

    // Проверка месяца
    if ($month < 1 || $month > 12) {
        return false;
    }

    // Проверка года
    if ($year > date('Y')) {
        return false;
    }
    return true;
}
// Функция удаления всех данных из файла 
function clearFunction(array $config) : string {
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "w");
        
        fwrite($file, '');
        
        fclose($file);
        return "Файл очищен";
    }
    else {
        return handleError("Файл не существует");
    }
}
// Функция поиска именинника
function findBirthdaysToday(array $config): string {
    $address = $config['storage']['address'];
    // Попытка открыть файл для чтения
    $fileHandler = @fopen($address, 'r');

    if ($fileHandler === false) {
        return handleError("Не удалось открыть файл $address. Проверьте права доступа.");
    }

    $today = date('d-m'); // текущая дата в формате "ДД-ММ"
    $birthdaysToday = []; 

    while (($line = fgets($fileHandler)) !== false) {
        $line = trim($line); // удалить лишние пробелы и символы новой строки

        if (empty($line)) {
            continue; // пропустить пустые строки
        }

        $data = explode(', ', $line); // разделить строку на имя и дату

        if (count($data) !== 2) {
            continue; //  строки, которые не соответствуют ожидаемому формату
        }

        [$name, $date] = $data;

        // сравнить текущую дату с днем и месяцем в строке
        $birthDate = substr($date, 0, 5); // Берем только "ДД-ММ"
        if ($birthDate === $today) {
            $birthdaysToday[] = $name;
        }
    }

    fclose($fileHandler);

    if (empty($birthdaysToday)) {
        return "Сегодня нет дней рождения.";
    }
    return "Сегодня день рождения у следующих людей: " . implode(', ', $birthdaysToday);
}
// Функция удаления строки из файла
function deleteLine(array $config): string {
    $address = $config['storage']['address'];

    if (!file_exists($address)) {
        return handleError("Файл $address не найден.");
    }

    $searchTerm = readline("Введите имя или дату для удаления (ДД-ММ-ГГГГ): ");
    $lines = file($address, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); // Считываем файл в массив строк

    $found = false;
    $updatedLines = [];

    foreach ($lines as $line) {
        // Если строка содержит искомый термин, пропускаем ее
        if (strpos($line, $searchTerm) !== false) {
            $found = true;
            continue;
        }
        $updatedLines[] = $line; // Добавляем строку в новый массив, если она не совпадает с искомой
    }

    if (!$found) {
        return "Строка, содержащая '$searchTerm', не найдена.";
    }

    // Перезапись файла
    $fileHandler = @fopen($address, 'w');
    if ($fileHandler === false) {
        return handleError("Не удалось открыть файл $address для записи.");
    }

    foreach ($updatedLines as $line) {
        fwrite($fileHandler, $line . PHP_EOL);
    }

    fclose($fileHandler);

    return "Строка, содержащая '$searchTerm', успешно удалена.";
}

function helpFunction() {
    return handleHelp();
}

function readConfig(string $configAddress): array|false{
    return parse_ini_file($configAddress, true);
}

function readProfilesDirectory(array $config): string {
    $profilesDirectoryAddress = $config['profiles']['address'];

    if(!is_dir($profilesDirectoryAddress)){
        mkdir($profilesDirectoryAddress);
    }

    $files = scandir($profilesDirectoryAddress);

    $result = "";

    if(count($files) > 2){
        foreach($files as $file){
            if(in_array($file, ['.', '..']))
                continue;
            
            $result .= $file . "\r\n";
        }
    }
    else {
        $result .= "Директория пуста \r\n";
    }

    return $result;
}

function readProfile(array $config): string {
    $profilesDirectoryAddress = $config['profiles']['address'];

    if(!isset($_SERVER['argv'][2])){
        return handleError("Не указан файл профиля");
    }

    $profileFileName = $profilesDirectoryAddress . $_SERVER['argv'][2] . ".json";

    if(!file_exists($profileFileName)){
        return handleError("Файл $profileFileName не существует");
    }

    $contentJson = file_get_contents($profileFileName);
    $contentArray = json_decode($contentJson, true);

    $info = "Имя: " . $contentArray['name'] . "\r\n";
    $info .= "Фамилия: " . $contentArray['lastname'] . "\r\n";

    return $info;
}
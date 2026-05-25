<?php
header('Content-Type: application/json');

// Конфигурация Telegram
define('TG_TOKEN', '8146405600:AAGsj3RRTS8Np_IktqllmwzD-ciHCAbFIno');
define('TG_CHAT_ID', '-5022307022');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Сбор и базовая фильтрация данных
    $name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'])) : '';

    // Валидация полей
    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все поля.']);
        exit;
    }

    // Формирование текста для Telegram (с поддержкой Markdown)
    $text = "🔔 *Новая заявка с сайта!*\n\n";
    $text .= "👤 *Имя:* " . $name . "\n";
    $text .= "📞 *Телефон:* " . $phone . "\n";
    $text .= "📅 *Дата:* " . date('d.m.Y H:i:s') . "\n";

    // Отправка в Telegram через cURL
    $url = "https://api.telegram.org/bot" . TG_TOKEN . "/sendMessage";
    $params = [
        'chat_id' => TG_CHAT_ID,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Проверка отправки в ТГ
    if ($error) {
        echo json_encode(['success' => false, 'message' => 'Ошибка отправки в Telegram: ' . $error]);
    } else {
        $resData = json_decode($response, true);
        if (isset($resData['ok']) && $resData['ok'] === true) {
            echo json_encode(['success' => true]);
        } else {
            // Вывод ошибки от самого API Телеграма для дебага, если что-то пойдет не так
            echo json_encode(['success' => false, 'message' => 'Telegram API вернул ошибку: ' . $resData['description']]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Некорректный метод запроса.']);
}
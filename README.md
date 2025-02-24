# RiskTools Blacklist SDK

[![Latest Stable Version](https://poser.pugx.org/wearesho-team/risktools-blacklist/v/stable)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![Total Downloads](https://poser.pugx.org/wearesho-team/risktools-blacklist/downloads)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![License](https://poser.pugx.org/wearesho-team/risktools-blacklist/license)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![PHP Tests](https://github.com/wearesho-team/risktools-blacklist/actions/workflows/php.yml/badge.svg)](https://github.com/wearesho-team/risktools-blacklist/actions/workflows/php.yml)

Реалізація SDK для API RiskTools Blacklist.

Сервіс дозволяє перевіряти позичальників на наявність у чорних списках за допомогою 
[API](https://doc.blacklist.risktools.pro). 
Чорні списки передаються кредиторами.
Кожен запис містить категорію (причину), за якою клієнт доданий до чорного списку.

## Установка

```bash
composer require wearesho-team/risktools-blacklist
```

## Конфігурація

Для налаштування пакету доступні два варіанти конфігурації,
які реалізують інтерфейс [ConfigInterface](./src/ConfigInterface.php):

### Базова конфігурація

Використовуйте клас [Config](./src/Config.php) для прямого налаштування через конструктор:

```php
use Wearesho\RiskTools\Blacklist\Config;

$config = new Config(
    authKey: 'ваш-ключ-авторизації',
    apiUrl: 'url-api-сервісу'
);
```

### Конфігурація через змінні оточення

Клас `EnvironmentConfig` дозволяє налаштувати пакет використовуючи змінні оточення:

| Змінна оточення               | Опис                                |
|-------------------------------|-------------------------------------|
| RISK_TOOLS_BLACKLIST_AUTH_KEY | Ключ авторизації для доступу до API |
| RISK_TOOLS_BLACKLIST_API_KEY  | URL-адреса API сервісу              |


Приклад використання:

```php

use Wearesho\RiskTools\Blacklist\EnvironmentConfig;

$config = new EnvironmentConfig();
```

Для цього варіанту необхідно попередньо налаштувати відповідні змінні оточення у вашому середовищі або .env файлі:

```dotenv
RISK_TOOLS_BLACKLIST_AUTH_KEY=ваш-ключ-авторизації
RISK_TOOLS_BLACKLIST_API_KEY=url-api-сервісу
```

## Ініціалізація сервісу

Для створення екземпляру сервісу використовуйте Builder:

```php
use Wearesho\RiskTools\Blacklist\Service\Builder;

// Базова ініціалізація з налаштуваннями за замовчуванням
$service = Builder::create()->getService();

// Ініціалізація з власним конфігом
$service = Builder::create()
    ->withConfig(new CustomConfig())
    ->getService();

// Ініціалізація з власним HTTP-клієнтом
$service = Builder::create()
    ->withHttpClient(new CustomHttpClient())
    ->getService();

// Ініціалізація з усіма залежностями
$service = Builder::create()
    ->withConfig(new CustomConfig())
    ->withHttpClient(new CustomHttpClient())
    ->getService();
```

### Налаштування за замовчуванням
За замовчуванням Builder використовує:
- EnvironmentConfig - конфігурація з змінних оточення
- GuzzleHttp\Client - стандартний HTTP-клієнт

## Пошук

Для виконання пошуку у чорному списку використовуйте метод search() сервісу.
Пошук можливий за номером телефону та/або ІПН.
Якщо в параметрах пошуку задані одночасно і ІПН та номер телефону, пошук виконуйтеся за логікою АБО,
тобто будуть знайдені записи, у яких збігається ІПН або номер телефону.

### Формування запиту

```php
use Wearesho\RiskTools\Blacklist\Search\Request;
use Wearesho\RiskTools\Blacklist\Category;

// Пошук за номером телефону
$request = Request::phone('380501234567');

// Пошук за ІПН
$request = Request::ipn('1234567890');

// Пошук за номером телефону та ІПН одночасно
$request = Request::phoneOrIpn('380501234567', '1234567890');

// Пошук з фільтрацією за категоріями
$request = Request::phone(
    '380501234567',
    Category::MILITARY,
    Category::FRAUD
);
```

### Отримання результатів
```php
use Wearesho\RiskTools\Blacklist\Service;

/** @var Service $service */
$response = $service->search($request);

// Отримання кількості знайдених записів
$total = $response->found();

// Отримання списку партнерів, які додали записи
$partners = $response->partners(); // ['87613', '01933']

// Отримання статистики за категоріями
$categories = $response->categories(); // ['military' => 2, 'fraud' => 1]

// Обробка знайдених записів
foreach ($response->records() as $record) {
    // Отримання даних запису
    $phone = $record->phone(); // ?string
    $ipn = $record->ipn(); // ?string
    $category = $record->category(); // Category enum
    $partnerId = $record->partnerId(); // string
    $addedAt = $record->addedAt(); // DateTimeImmutable

    // Приклад використання
    echo sprintf(
        "Запис %s додано %s партнером %s в категорії %s\n",
        $phone ?? $ipn,
        $addedAt->format('Y-m-d'),
        $partnerId,
        $category->value
    );
}

```

## Ліцензія
[MIT](./LICENSE)

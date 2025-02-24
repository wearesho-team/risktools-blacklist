# RiskTools Blacklist SDK

[![Latest Stable Version](https://poser.pugx.org/wearesho-team/risktools-blacklist/v/stable)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![Total Downloads](https://poser.pugx.org/wearesho-team/risktools-blacklist/downloads)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![License](https://poser.pugx.org/wearesho-team/risktools-blacklist/license)](https://packagist.org/packages/wearesho-team/risktools-blacklist)
[![PHP Tests & Linting](https://github.com/wearesho-team/risktools-blacklist/actions/workflows/php.yml/badge.svg)](https://github.com/wearesho-team/risktools-blacklist/actions/workflows/php.yml)

Реалізація SDK для API RiskTools Blacklist.

Сервіс дозволяє перевіряти позичальників на наявність у чорних списках за допомогою 
[API](https://doc.blacklist.risktools.pro). 
Чорні списки передаються кредиторами.
Кожен запис містить категорію (причину), за якою клієнт доданий до чорного списку.

## Зміст

1. [Встановлення](#встановлення)
2. [Конфігурація](#конфігурація)
    - [Базова конфігурація](#базова-конфігурація)
    - [Конфігурація через змінні оточення](#конфігурація-через-змінні-оточення)
3. [Ініціалізація сервісу](#ініціалізація-сервісу)
    - [Використання Builder](#використання-builder)
    - [Використання Dependency Injection](#використання-dependency-injection)
3. [Пошук](#пошук)
    - [Формування запиту](#формування-запиту)
    - [Доступні категорії](#доступні-категорії)
    - [Отримання результатів](#отримання-результатів)
4. [Оновлення (додавання) даних](#оновлення-додавання-даних)
    - [Формування записів](#формування-записів)
    - [Відправка даних](#відправка-даних)
    - [Обробка відповіді](#обробка-відповіді)
5. [Обробка винятків](#обробка-винятків)
    - [Exception](#exception)
    - [RequestException](#requestexception)
    - [ResponseException](#responseexception)

## Встановлення

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

### Використання Builder

Для створення екземпляра сервісу використовуйте Builder:

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

#### Налаштування за замовчуванням
За замовчуванням Builder використовує:
- EnvironmentConfig - конфігурація з змінних оточення
- GuzzleHttp\Client - стандартний HTTP-клієнт

### Використання Dependency Injection

Для використання сервісу з Dependency Injection вам необхідно налаштувати DI Container
для реалізації двох інтерфейсів:
- [ConfigInterface](./src/ConfigInterface.php)
- \GuzzleHttp\ClientInterface

```php
use Wearesho\RiskTools\Blacklist;

$service = new Blacklist\Service(
    client: new Blacklist\Client(
        config: new Blacklist\Config(), // налаштувати в DI Container
        httpClient: new \GuzzleHttp\Client(), // налаштувати в DI Container
    ),
    searchFactory: new Blacklist\Search\Factory(),
    updateFactory: new Blacklist\Update\Factory(), 
);
```

## Пошук

Для виконання пошуку у чорному списку використовуйте метод search() сервісу.
Пошук можливий за номером телефону та/або ІПН.
Якщо в параметрах пошуку задані одночасно і ІПН та номер телефону, пошук виконуйтеся за логікою АБО,
тобто будуть знайдені записи, у яких збігається ІПН або номер телефону.

### Доступні категорії

Для фільтрації записів доступні наступні категорії 
([Category enum](./src/Category.php)):

- `MILITARY` - військові дії
- `CLAIM` - претензії
- `FRAUD` - шахрайство
- `CIRCLE` - коло спілкування
- `DEAD` - померлі
- `GAMING` - азартні ігри
- `INCAPABLE` - недієздатні
- `WRITEOFF` - списання
- `INADEQUATE` - неадекватна поведінка
- `ADDICT` - залежність
- `LOST_DOCS` - втрачені документи
- `SELF` - самозанесення
- `OTHER` - інше

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

## Оновлення (додавання) даних

### Формування записів

```php
use Wearesho\RiskTools\Blacklist\Update\Record;
use Wearesho\RiskTools\Blacklist\Category;
use DateTimeImmutable;

// Створення запису за номером телефону
$record = Record::withPhone(
    "380501234567",
    Category::MILITARY,
    new DateTimeImmutable() // опціонально
);

// Створення запису за ІПН
$record = Record::withIpn(
    "1234567890",
    Category::FRAUD,
    new DateTimeImmutable("2023-08-01T12:00:00+03:00") // опціонально
);

// Створення запису з телефоном та ІПН
$record = Record::withPhoneAndIpn(
    "380501234567",
    "1234567890",
    Category::CIRCLE,
    new DateTimeImmutable() // опціонально
);
```

### Відправка даних

```php
use Wearesho\RiskTools\Blacklist\Exception;

// Формування масиву записів
$records = [
    Record::wi("380501234567", Category::MILITARY),
    Record::ipn("1234567890", Category::FRAUD),
    Record::phoneAndIpn(
        "380507654321",
        "0987654321",
        Category::CIRCLE,
        new DateTimeImmutable()
    ),
];

// Відправка даних
try {
    $response = $service->update($records);
} catch (Exception $e) {
    // Обробка помилок запиту
    echo "Помилка запиту: " . $e->getMessage() . PHP_EOL;
}

```

### Обробка відповіді

```php
use Wearesho\RiskTools\Blacklist\Update\Response;
/** @var Response $response */

if ($response->isSuccessful()) {
    echo "Всі записи успішно оновлено" . PHP_EOL;
    exit;
}

echo "Кількість записів з помилками: " . $response->countErrors() . PHP_EOL;

foreach ($response->errors() as $error) {
    $record = $error->record();

    // Виведення інформації про запис з помилкою
    echo sprintf(
        "Помилка для запису %s / %s:" . PHP_EOL,
        $record->phone() ?? '-',
        $record->ipn() ?? '-'
    );

    // Виведення помилок валідації
    foreach ($error->errors() as $field => $messages) {
        echo "- $field: " . implode(', ', $messages) . PHP_EOL;
    }
}
```

## Обробка винятків


SDK використовує три типи винятків для різних ситуацій:

### Exception

Базовий інтерфейс для всіх винятків SDK. Рекомендується використовувати його для відлову будь-яких помилок SDK:

```php
use Wearesho\RiskTools\Blacklist\Exception;

try {
    $response = $service->update($records);
} catch (Exception $e) {
    // Обробка будь-якої помилки SDK
    echo "Помилка SDK: " . $e->getMessage();
}
```

### RequestException

Виникає у випадках:
- Мережевих помилок
- Невірного формату відповіді API
- Помилок автентифікації
- Інших помилок HTTP-запитів

```php

use Wearesho\RiskTools\Blacklist\RequestException;

try {
    $response = $service->search($request);
} catch (RequestException $e) {
    echo "Помилка запиту: " . $e->getMessage();
    echo "Код помилки: " . $e->getCode();

    // Доступ до оригінального винятку Guzzle (якщо є)
    if ($e->getPrevious() instanceof \GuzzleHttp\Exception\GuzzleException) {
        // Обробка специфічної помилки Guzzle
    }
    
    // Доступ до оригінального винятку JSON (якщо є)
    if ($e->getPrevious() instanceof \JsonException) {
        // Обробка специфічної помилки JSON
    }
}
```

### ResponseException

Виникає при некоректному форматі даних у відповіді API:
- Відсутні обов'язкові поля
- Неправильні типи даних
- Неможливість обробки відповіді

```php
use Wearesho\RiskTools\Blacklist\ResponseException;

try {
    $response = $service->update($records);
} catch (ResponseException $e) {
    echo "Помилка обробки відповіді: " . $e->getMessage();
}
```

## Ліцензія
[MIT](./LICENSE)

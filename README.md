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



## Ліцензія
[MIT](./LICENSE)

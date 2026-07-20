# Contributing

[English](#english) · [Русский](#русский)

## English

Thank you for improving Phenogram Gateway Bindings.

### Set up the repository

```bash
composer install
composer tools:install
```

### Make a change

1. Keep the public API compatible when a safe option exists.
2. Use the field names and behavior from the
   [official Telegram Gateway API reference](https://core.telegram.org/gateway/api).
3. Add a test for each behavior change.
4. Keep every default test and example offline.
5. Update the English and Russian documents together.
6. Use short active sentences in English.

Do not put a real token, phone number, or verification code in the repository.
Do not call `checkSendAbility` in the default test suite. A successful check can
charge the account.

### Run the quality gate

```bash
composer check
```

The pull request must pass Composer validation, PHPUnit, all offline examples,
PHPStan at the maximum level, and the style check.

## Русский

Спасибо за вклад в Phenogram Gateway Bindings.

### Подготовка репозитория

```bash
composer install
composer tools:install
```

### Внесение изменений

1. Сохраняйте совместимость публичного API, если есть безопасный способ.
2. Сверяйте поля и поведение с
   [официальной документацией Telegram Gateway API](https://core.telegram.org/gateway/api).
3. Добавляйте тест для каждого изменения поведения.
4. Не используйте сеть в стандартных тестах и примерах.
5. Обновляйте английскую и русскую документацию одновременно.
6. Пишите английский текст короткими предложениями в активном залоге.

Не добавляйте в репозиторий реальные токены, номера телефонов и коды
подтверждения. Не вызывайте `checkSendAbility` в стандартном наборе тестов.
Успешная проверка может привести к списанию средств.

### Проверка качества

```bash
composer check
```

Pull request должен пройти проверку Composer, PHPUnit, все офлайн-примеры,
PHPStan на максимальном уровне и проверку стиля.

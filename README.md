# Raptor Test Utils v0.1.0

(c) Михаил Каморин aka raptor_MVK

## Описание

Набор утверждений и вспомогательных методов для упрощения тестирования.

## Установка

Для установки необходимо:

- Открыть `composer.json`, добавить название пакета в блок `require` и ссылку на данный репозиторий в блок
`repositories`:

```
    "require": {
+        "raptor/test-utils": "1.0.*"
    },
    ...
    "repositories": [
+        {
+          "type": "git",
+          "url": "git@github.com:raptor-mvk/test-utils.git"
+        },
        ...
    ],
```

- Выполнить команду `composer update`


## Использование

Модуль предоставляет трейт `Raptor\Test\ExtraAssertions`, который содержит следующие утверждения и вспомогательные
функции:

- 

 
## История версий

v0.1.0

-

## Авторы

- Михаил Каморин aka raptor_MVK

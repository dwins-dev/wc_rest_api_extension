# Woocommerce REST API extension

Плагин для расширения REST API woocommerce используя
его [подход авторизации](https://developer.woocommerce.com/2015/08/07/api-settings-and-the-api-authentication-endpoint-in-2-4/)
через Basic Auth и ключи доступа

## Название сайта

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/site-name`

### Ответ

#### Правильные ключи доступа

```json
"Name site"
```

#### В случае ошибки

```json
false
```

## Список пользователей

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/users`

### Ответ

#### Правильные ключи доступа

```json
[
  {
    "data": {
      "ID": "1",
      "user_login": "dwins",
      "user_pass": "$P$B1Feu.sumbSl94HDiJdBr4PcQuxCEk.",
      "user_nicename": "dwins",
      "user_email": "m.l.forwardtosuccess@gmail.com",
      "user_url": "http://ngh-mainframe-wp.test",
      "user_registered": "2021-08-20 15:39:55",
      "user_activation_key": "",
      "user_status": "0",
      "display_name": "dwins"
    }
  },
  {
    "data": {
      "ID": "2",
      "user_login": "test",
      "user_pass": "$P$BPx3JGb/YQI9YdST76nOMioHXaGj2K1",
      "user_nicename": "test",
      "user_email": "test2@test.test",
      "user_url": "",
      "user_registered": "2021-08-21 12:45:03",
      "user_activation_key": "1629549904:$P$BpUuUEDCR0oH28IYj.v6INWpuaLtD.1",
      "user_status": "0",
      "display_name": "test"
    }
  }
]
```

#### В случае ошибки

```json
false
```

## Добавить пользователя

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/user-create`

body:

```json
{
  "username": "name",
  "password": "password",
  "email": "email@email.email",
  "role": "subscriber"
}
```

### Ответ

#### Правильные ключи доступа

В ответ приходит id созданного пользователя

#### В случае ошибки

Отдает ошибки в элементе массива message

Пример:

```json
{
  "code": "existing_user_login",
  "message": "Извините, это имя пользователя уже существует!",
  "data": null
}
```

## Обновить данные пользователя

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/user-update`

body:

```json
{
  "ID": 3,
  "username": "name",
  "password": "password",
  "email": "email@email.email"
}
```

## Удалить пользователя

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/user-delete`

body:

```json
{
  "ID": 3
}
```

## Получить массив ролей

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/roles`

#### Правильный ключ доступа

```json
{
  "administrator": "Administrator",
  "editor": "Editor",
  "author": "Author",
  "contributor": "Contributor",
  "subscriber": "Subscriber",
  "customer": "Customer",
  "shop_manager": "Shop manager"
}
```
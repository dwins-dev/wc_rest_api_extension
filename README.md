# Woocommerce REST API extension

Плагин для расширения REST API woocommerce используя
его [подход авторизации](https://developer.woocommerce.com/2015/08/07/api-settings-and-the-api-authentication-endpoint-in-2-4/)
через Basic Auth и ключи доступа.

Каждый запрос должен содержать
`Basic Auth` и стрку `webhook_url` в body которая должна передавать в себе url сайта из которого идет запрос и к
которому должены будут передаваться запросы от webhook wc

## Название сайта и роли

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/site-data`

### Ответ

Вернет массив с строкой названия сайта и массивом ролей или в случае ошибки массив с кодом ошибки

## Название сайта

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/site-name`

### Ответ

Вернет строку с названием сайта или массив с кодом ошибки

## Список пользователей

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/users`

### Ответ

Вернет массив с пользователями или массив с кодом ошибки

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

В ответ приходит id созданного пользователя или массив с кодом ошибки

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

### Ответ

В ответ приходит массив обновленного пользователя или массив с кодом ошибки

## Удалить пользователя

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/user-delete`

body:

```json
{
  "ID": 3
}
```

### Ответ

В случае ошибки в ответ приходит массив с кодом ошибки

## Получить массив ролей

### Запрос

`post` `/wp-json/wc-rest-api-extension/v1/roles`

#### Ответ

Пример:

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

В случае ошибки в ответ приходит массив с кодом ошибки

## Webhook

Вебхуки подключаются к системе ключей Woocommerce и используют те же ключи что были сгенерированы при подключении к
сайту по rest api woocommerce

Для того что бы активировать их достаточно выполнить один из выше перечисленных запросов, с переданным адрессом сайта к
которому будут подключены вебхуки. Передать адрес сайта нужно в теле запроса, ячейке массива с ключом `webhook_url`

После активации вебхуков, они будут отправлять

### Запросы к сайту

#### После добавления пользователя

Отправляется запрос по адресу с Basic auth

`POST` `ссылка.сайта/api/webhooks/connector-users`

body:

```json
{
  "site_url": "ссылка.на.сайт.вп",
  "user_id": 3,
  "user_email": "test@test.test",
  "user_role": "subscribe"
}
```

#### После обновления аккаунту пользователя

Отправляется запрос по адресу с Basic auth (пс. username или в нашем случае `consumer_key` отправляется как хэш код
сгенерированный с использованием метода `HMAC` с алгоритмом `sha256` и секретным ключем `wc-api` все ниже описанные
запросы имеют такуюже логику поведения)

`PATH` `ссылка.сайта/api/webhooks/connector-users`

body:

```json
{
  "site_url": "ссылка.на.сайт.вп",
  "user_id": 3,
  "user_email": "test@test.test",
  "user_role": "subscribe"
}
```

#### После удаления пользователя

Отправляется запрос по адресу с Basic auth

`DELETE` `ссылка.сайта/api/webhooks/connector-users`

body:

```json
{
  "site_url": "ссылка.на.сайт.вп",
  "user_id": 3
}
```

#### Отключить вебхуки

Отключить их можно удалив rest api ключ через который он был подключен
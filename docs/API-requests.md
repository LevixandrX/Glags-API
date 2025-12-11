## Общие сведения

- **Базовый адрес API**: `https://k-levitin.xn--80ahdri7a.site/api`
- **Стиль API**: REST, формат данных — `application/json` (кроме загрузки файлов).
- **Аутентификация**: в защищённых методах — заголовок  
  `Authorization: Bearer <token>`.
- **Ошибки авторизации и доступа** (см. пример [`Пример описания REST API`](file://Пример описания REST API.pdf)):
  - Гость обращается к защищённому ресурсу:
    - **Status: 403**
    - **Body**:
      ```json
      {
        "message": "Login failed"
      }
      ```
  - Авторизованный пользователь без прав доступа:
    - **Status: 403**
    - **Body**:
      ```json
      {
        "message": "Forbidden for you"
      }
      ```
  - Обращение к несуществующему ресурсу:
    - **Status: 404**
    - **Body**:
      ```json
      {
        "message": "Not found",
        "code": 404
      }
      ```
- **Ошибки валидации** (единый формат):
  - **Status: 422**
  - **Body**:
    ```json
    {
      "error": {
        "code": 422,
        "message": "Validation error",
        "errors": {
          "<field>": [
            "<сообщение об ошибке>"
          ]
        }
      }
    }
    ```

Во всех запросах ниже, если явно не указано иное, предполагается заголовок  
`Content-Type: application/json`.

---

## 1. Пользователи и авторизация

### 1.1 Регистрация пользователя

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/registration`  
- **Method**: `POST`  
- **Авторизация**: не требуется (гость)
- **Body (JSON)** — в соответствии с ТЗ и примером регистрации:
  ```json
  {
    "first_name": "Иван",
    "last_name": "Петров",
    "patronymic": "Сергеевич",
    "email": "user@example.com",
    "password": "PaSSword1",
    "birth_date": "2001-02-15"
  }
  ```
- **Успешный ответ**:
  - **Status: 201**
  - **Body (пример по образцу из ТЗ)**:
    ```json
    {
      "data": {
        "user": {
          "name": "Петров Иван Сергеевич",
          "email": "user@example.com"
        },
        "code": 201,
        "message": "Пользователь создан"
      }
    }
    ```
- **Ошибки**:
  - Ошибка валидации данных — формат 422 из общих требований.

### 1.2 Аутентификация (получение токена)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/authorization`  
- **Method**: `POST`  
- **Авторизация**: не требуется (гость)
- **Body (JSON)**:
  ```json
  {
    "email": "user@example.com",
    "password": "PaSSword1"
  }
  ```
- **Успешный ответ** (по образцу из ТЗ и ТЗ на магазин):
  - **Status: 200**
  - **Body**:
    ```json
    {
      "data": {
        "user": {
          "id": 1,
          "name": "Петров Иван Сергеевич",
          "birth_date": "2001-02-15",
          "email": "user@example.com"
        },
        "token": "<сгенерированный токен>"
      }
    }
    ```
- **Ошибки**:
  - Неверные логин или пароль — **Status: 401**, тело в формате ошибок из общих требований.
  - Ошибки валидации полей — **Status: 422**, формат валидации.

### 1.3 Выход (сброс авторизации)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/logout`  
- **Method**: `POST`  
- **Авторизация**: требуется (`Authorization: Bearer <token>`)
- **Body**: отсутствует
- **Успешный ответ**:
  - **Status: 200** или **204** (тело может не возвращаться)
- **Ошибки**:
  - Гость без токена — 403, `{ "message": "Login failed" }`.

---

## 2. Каталог товаров

Реализовано через REST‑контроллер `product` и правило `UrlRule` с префиксом `/api`.  
Идентификация пользователя в защищённых методах — Bearer Token.

### 2.1 Получение списка товаров

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products`  
- **Method**: `GET`  
- **Авторизация**: не требуется
- **Query‑параметры**:
  - `category_id` — фильтр по категории (целое число, необязательный).
  - `q` — строка поиска по названию товара (необязательный).
  - `per-page` — размер страницы (по умолчанию 20).
  - `page` — номер страницы.
- **Успешный ответ**:
  - **Status: 200**
  - **Body (пример)**:
    ```json
    {
      "items": [
        {
          "id": 1,
          "name": "Стеклянный сувенир «Луна»",
          "price": 1990,
          "category_id": 3,
          "category": { "...": "..." },
          "images": [
            { "id": 10, "image_url": "/assets/upload/products/..." }
          ]
        }
      ],
      "_meta": {
        "totalCount": 1,
        "pageCount": 1,
        "currentPage": 1,
        "perPage": 20
      }
    }
    ```

### 2.2 Получение товара по ID

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products/{id}`  
- **Method**: `GET`  
- **Авторизация**: не требуется
- **Успешный ответ**:
  - **Status: 200**
  - **Body** — объект товара с категориями и изображениями.
- **Ошибки**:
  - Не найдено — **404**, тело:
    ```json
    {
      "message": "Not found",
      "code": 404
    }
    ```

### 2.3 Создание товара (админ)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products`  
- **Method**: `POST`  
- **Авторизация**: требуется (роль администратора)
- **Body (пример)**:
  ```json
  {
    "name": "Стеклянный шар с подсветкой",
    "description": "Подарочный стеклянный шар...",
    "price": 2990,
    "category_id": 3
  }
  ```
- **Успешный ответ**:
  - **Status: 201**
  - **Body** — созданный объект товара.
- **Ошибки**:
  - Нет токена / гость — 403 `Login failed`.
  - Нет прав (не админ) — 403 `Forbidden for you`.
  - Ошибки валидации — 422 (общий формат).

### 2.4 Обновление товара (админ)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products/{id}`  
- **Method**: `PUT` / `PATCH`  
- **Авторизация**: требуется (админ)
- **Body**: частичные или полные данные товара.
- **Успешный ответ**:
  - **Status: 200**
  - **Body** — обновлённый объект товара.
- **Ошибки**:
  - Не найдено — 404 (формат «Not found»).
  - Нет прав / нет токена — 403.
  - Ошибки валидации — 422.

### 2.5 Удаление товара (админ)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products/{id}`  
- **Method**: `DELETE`  
- **Авторизация**: требуется (админ)
- **Успешный ответ**:
  - **Status: 204**
  - **Body**: отсутствует.
- **Ошибки**:
  - Не найдено — 404.
  - Нет прав / нет токена — 403.

### 2.6 Загрузка изображения товара

Метод реализован как `ProductController::actionUploadImage($id)`; для Postman рекомендуется использовать URL вида:

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/products/{id}/image`  
- **Method**: `POST`  
- **Авторизация**: требуется (админ)  
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Content-Type: multipart/form-data`
- **Body (form-data)**:
  - `image` — файл изображения (`.jpg`, `.jpeg`, `.png`, `.webp`), не более 5 МБ.
- **Успешный ответ**:
  - **Status: 201**
  - **Body** — сохранённая запись `ProductImage`:
    ```json
    {
      "id": 10,
      "product_id": 1,
      "image_url": "/assets/upload/products/<hash>.jpg"
    }
    ```
- **Ошибки (по коду)**:
  - 400 — файл не передан.
  - 422 — недопустимое расширение или размер.
  - 404 — товар не найден.
  - 500 — ошибка сохранения файла или записи.

---

## 3. Корзина

В `config/web.php` настроены следующие маршруты:

### 3.1 Добавление товара в корзину

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/cart`  
- **Method**: `POST`  
- **Авторизация**: рекомендуется авторизованный пользователь (по ТЗ — через Bearer Token)
- **Body (пример)**:
  ```json
  {
    "product_id": 1,
    "quantity": 2
  }
  ```
- **Успешный ответ**:
  - **Status: 201**
  - **Body** — созданный элемент корзины.

### 3.2 Получение содержимого корзины

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/cart`  
- **Method**: `GET`  
- **Авторизация**: авторизованный пользователь
- **Query‑параметры**:
  - `user_id` — фильтр по пользователю (используется в `CartItemController`).
- **Успешный ответ**:
  - **Status: 200**
  - **Body**:
    ```json
    {
      "items": [
        {
          "id": 1,
          "user_id": 5,
          "product_id": 10,
          "quantity": 2,
          "product": { "...": "..." }
        }
      ],
      "_meta": { "...": "..." }
    }
    ```

### 3.3 Удаление позиции из корзины

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/cart/{id}`  
- **Method**: `DELETE`  
- **Авторизация**: авторизованный пользователь
- **Успешный ответ**:
  - **Status: 204**
  - **Body**: отсутствует.
- **Ошибки**:
  - Не найдено (позиция корзины) — 404.

---

## 4. Заказы

### 4.1 Создание заказа

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/orders`  
- **Method**: `POST`  
- **Авторизация**: авторизованный пользователь
- **Body (пример)**:
  ```json
  {
    "user_id": 5,
    "items": [
      { "product_id": 1, "quantity": 2 },
      { "product_id": 3, "quantity": 1 }
    ],
    "delivery_address": "г. Санкт-Петербург, ...",
    "comment": "Позвонить перед доставкой"
  }
  ```
- **Успешный ответ**:
  - **Status: 201**
  - **Body** — созданный заказ с позициями.
- **Ошибки**:
  - Ошибки валидации — 422.

### 4.2 Получение списка заказов

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/orders`  
- **Method**: `GET`  
- **Авторизация**: авторизованный пользователь  
  (по ТЗ: пользователь видит свои заказы, админ — все)
- **Query‑параметры**:
  - `status` — фильтр по статусу.
  - `user_id` — фильтр по пользователю (используется в `OrderController`).
- **Успешный ответ**:
  - **Status: 200**
  - **Body** — список заказов с вложенными `items.product` и `user`.

---

## 5. Профиль пользователя

### 5.1 Загрузка аватара

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/profile/avatar`  
- **Method**: `POST`  
- **Авторизация**: авторизованный пользователь
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Content-Type: multipart/form-data`
- **Body (form-data)**:
  - `file` — изображение (`.jpg`, `.jpeg`, `.png`, `.webp`), до 5 МБ.
- **Успешный ответ**:
  - **Status: 200**
  - **Body**:
    ```json
    {
      "url": "https://example.com/path/to/avatar.webp"
    }
    ```
- **Ошибки**:
  - 413 — слишком большой файл.
  - 415 — неподдерживаемый формат.
  - 422 — ошибки валидации (нет файла и т.п.).

---

## 6. Администрирование заказов

### 6.1 Получение списка всех заказов (админ)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/admin/orders`  
- **Method**: `GET`  
- **Авторизация**: требуется роль администратора
- **Успешный ответ**:
  - **Status: 200**
  - **Body** — список заказов с деталями.
- **Ошибки**:
  - Не админ — 403, `"Forbidden for you"`.

### 6.2 Обновление статуса заказа (админ)

- **URL**: `https://k-levitin.xn--80ahdri7a.site/api/admin/orders/{id}`  
- **Method**: `PATCH`  
- **Авторизация**: админ
- **Body (пример)**:
  ```json
  {
    "status": "confirmed"
  }
  ```
- **Успешный ответ**:
  - **Status: 200**
  - **Body**:
    ```json
    {
      "data": {
        "code": 200,
        "message": "Миссия обновлена"
      }
    }
    ```
    (формат сообщения может быть адаптирован под ваш текст, главное — код и успешный статус).
- **Ошибки**:
  - Не найден заказ — 404 (формат «Not found»).
  - Ошибки валидации статуса — 422.
  - Нет прав — 403.

---

## 7. Тестовые учётные записи (для Postman)

В соответствии с заданием необходимо завести как минимум следующие аккаунты:

- **Пассажир Луна**:  
  - `email: passenger@moon.ru`  
  - `password: P@rtyAstr0nauts`
- **Пассажир Марс**:  
  - `email: passenger@mars.ru`  
  - `password: QwertyP@rtyAstr0nauts`

Рекомендуется в Postman создать коллекцию с папками:

- **Auth** (`https://k-levitin.xn--80ahdri7a.site/api/registration`, `.../api/authorization`, `.../api/logout`),
- **Products** (`https://k-levitin.xn--80ahdri7a.site/api/products`, `.../api/products/{id}`, CRUD, загрузка изображения),
- **Cart** (`https://k-levitin.xn--80ahdri7a.site/api/cart`, `.../api/cart/{id}`),
- **Orders** (`https://k-levitin.xn--80ahdri7a.site/api/orders`),
- **Profile** (`https://k-levitin.xn--80ahdri7a.site/api/profile/avatar`),
- **Admin** (`https://k-levitin.xn--80ahdri7a.site/api/admin/orders`, `.../api/admin/orders/{id}`),

и настроить окружение с переменными `host` и `token` (получается из `/api/authorization`).



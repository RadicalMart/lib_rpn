## Обработка формул с помощью обратной польской записи

### Сначала надо обработчик мат. формул в ОПЗ:
```php
$rpn = $RPNBase->to("формула");
```

### Потом сам расчет:
```php
$total = $RPNCalc->calc($rpn)
```

### Тест
```shell
php test.php "(max(1,50)-min(5,8)+round(5.7, 0))*3"
```

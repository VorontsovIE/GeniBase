<?php
// Запрещено непосредственное исполнение этого скрипта
if(empty($_SERVER['PHP_SELF']) || (basename($_SERVER['PHP_SELF']) == basename(__FILE__)))	die('Direct execution forbidden!');

/**
 * Флаги режимов отладки
 */
// define('DEBUG',	1);	// Общий режим отладки
//
// define('SQL_DEBUG',	1);



/**
 * Настройки подключения к базе данных
 */
define('DB_HOST',	'');		// URL MySQL-сервера
define('DB_USER',	'');		// Имя пользователя
define('DB_PWD',	'');		// Пароль
define('DB_BASE',	'');		// Имя базы данных

/**
 * Лимиты
 */
define('Q_LIMIT',	20);	// Лимит числа строк на одной странице результатов поиска
define('P_LIMIT',	70);	// Лимит числа единовременно публикуемых записей



define('OVERLOAD_BAN_TIME',	60);	// На сколько минут блокируется нарушитель, вызвавший перегрузку системы

?>
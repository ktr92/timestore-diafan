<?php
/**
 * Файл конфигурации
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	include dirname(__FILE__).'/includes/404.php';
}

//папка, в которой лежит сайт. Для корня домена оставьте пустым
define("REVATIVE_PATH", '');

//название сайта, добавляется к тегу title в конце через дефис
define("TIT1", 'Timestore');

//параметры подключения к БД
define("DB_URL", 'mysqli://ktr992hc_firlet:236*iMb&@localhost/ktr992hc_firlet');

//префикс таблиц сайта в БД
define("DB_PREFIX", 'diafan_');

//кодировка БД
define("DB_CHARSET", 'utf8');

//название папки с визуальным редактором
define("USERFILES", 'userfls');

//версия DIAFAN.CMS
define("VERSION_CMS", "6.0");

//ЧПУ папки панели администрирования
define("ADMIN_FOLDER", 'admin');

//мобильная версия true/false (да/нет)
define("MOBILE_VERSION", false);

//имя мобильной версии в url-адресе
define("MOBILE_PATH", 'm');

//использовать имя мобильной версии в качестве поддомена true/false (да/нет)
define("MOBILE_SUBDOMAIN", false);

//demo-версия true/false (да/нет)
define("IS_DEMO", false);

//включить режим разработки, когда на сайт выводятся все возможные ошибки true/false (да/нет)
define("MOD_DEVELOPER", false);

//показывать ошибки только администратору true/false (да/нет)
define("MOD_DEVELOPER_ADMIN", false);

//включить режим технического обслуживания сайта, сайт станет недоступен для пользователей (шаблон оформления сообщения в themes/503.php) true/false (да/нет)
define("MOD_DEVELOPER_TECH", false);

//включить режим сжатия HTML-контента true/false (да/нет)
define("MOD_DEVELOPER_MINIFY", false);

//отключить кеширование true/false (да/нет)
define("MOD_DEVELOPER_CACHE", true);

//выводить запросы к БД на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILING", false);

//адрес FTP текущего сайта
define("FTP_HOST", '');

//путь к DIAFAN.CMS, после входа ftp-пользователя, например, www/site.ru/docs/
define("FTP_DIR", '');

//имя FTP-пользователя
define("FTP_LOGIN", 'admin');

//пароль FTP-пользователя
define("FTP_PASSWORD", 'local');

//главный ящик администратора, владельца сайта, используется по умолчанию во всех уведомлениях
define("EMAIL_CONFIG", 'testservices92@gmail.com');

//использовать SMTP-авторизацию при отправке почтовых сообщений true/false (да/нет)
define("SMTP_MAIL", false);

//url почтового сервера (например, smtp.site.ru)
define("SMTP_HOST", '');

//логин почты
define("SMTP_LOGIN", '');

//пароль к почте
define("SMTP_PASSWORD", '');

//порт (по умолчанию 25)
define("SMTP_PORT", '');

//экстремальное кэширование
define("CACHE_EXTREME", false);

//использовать Memcached сервер для кэширования
define("CACHE_MEMCACHED", false);

//хост сервера Memcached
define("CACHE_MEMCACHED_HOST", '');

//порт сервера Memcached
define("CACHE_MEMCACHED_PORT", '');

//часовой пояс сайта, в формате http://www.php.net/manual/en/timezones.php
define("TIMEZONE", 'Europe/Moscow');

//конец строки ЧПУ, по умолчанию "/". Можно ввести ".htm"
define("ROUTE_END", '/');

//использовать автоматическое формирование ЧПУ для модулей true/false (да/нет)
define("ROUTE_AUTO_MODULE", true);

//подключить SMS-уведомления true/false (да/нет)
define("SMS", false);

// ключ для сервиса byteHand
define("SMS_KEY", '');

// id в системе byteHand
define("SMS_ID", '');

// подпись для уведомлений
define("SMS_SIGNATURE", '');

//дата последнего экспорта заказов в систему 1С:Предприятие
define("LAST_1C_EXPORT", '');

// разрешить вставлять во frame
define("NO_X_FRAME", false);

// примененные темы
define("CUSTOM", 'addon_52_6778eae8,addon_53_f7912608');

<?php
/**
 * Редактирование параметров сайта
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Config_admin
 */
class Config_admin extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'name' => array(
				'type' => 'none',
				'name' => 'Название сайта',
				'help' => 'Если на сайте используются несколько языковых версий, то поле «Название сайта» нужно заполнять для каждой версии.',
				'no_save' => true,
			),
			'hr1' => 'hr',
			'db_host' => array(
				'type' => 'text',
				'name' => 'Host для базы данных',
				'help' => 'Хост для подключения к базе данных. Например, localhost. Данные обычно предоставляются хостингом при регистрации.',
				'disabled' => true,
			),
			'db_name' => array(
				'type' => 'text',
				'name' => 'База данных',
				'help' => 'Название базы данных. Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом. При создании базы данных рекомендуется выбирать кодировку UTF8.',
				'disabled' => true,
			),
			'db_user' => array(
				'type' => 'text',
				'name' => 'Пользователь базы данных',
				'help' => 'Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом.',
				'disabled' => true,
			),
			'db_pass' => array(
				'type' => 'password',
				'name' => 'Пароль для базы данных',
				'help' => 'Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом.',
				'disabled' => true,
			),
			'db_prefix' => array(
				'type' => 'text',
				'name' => 'Префикс (например, diafan_)',
				'help' => 'Символы, добавляемые к каждой таблице в базе данных, используемой CMS. Полезно, когда в одной базе данный MySQL имеются таблицы не только CMS. Префикс может быть пустым.',
				'disabled' => true,
			),
			'db_charset' => array(
				'type' => 'text',
				'name' => 'Кодировка базы данных',
				'help' => 'DIAFAN.CMS работает с базой данных в кодировке UTF8. Изменить параметр можно в случае индивидуальной настройки системы.',
				'disabled' => true,
			),
			'hr2' => 'hr',
			'userfiles' => array(
				'type' => 'text',
				'name' => 'Папка для хранения пользовательских файлов.',
				'help' => 'Имя папки, где будут храниться все загружаемые файлы для контента сайта.  По умолчанию все пользовательские файлы хранятся в папке *userfls*. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'admin_folder' => array(
				'type' => 'text',
				'name' => 'Папка административной части',
				'help' => 'Адрес административной части сайта. Например, *http://site.ru/admin/* или *http://site.ru/manager/*. Изменение параметра означает изменение URL-адреса панели администрирования. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'maxlength' => 20,
			),
			'hr22' => 'hr',
			'mobile_version' => array(
				'type' => 'checkbox',
				'name' => 'Использовать отдельный шаблон мобильной версии (при наличии)',
				'help' => 'Если отмечено, то CMS будет автоматически определять устройство, с которого зашли на сайт и если это мобильное устройство, то автоматически будет загружаться дополнительный шаблон дизайна themes/m/site.php.',
			),
			'mobile_path' => array(
				'type' => 'text',
				'name' => 'Имя мобильной версии в URL-адресе',
				'help' => 'Название, используемое в URL-адресе, в качестве адреса мобильной версии. Допустимо использование латиницы в нижнем регистре, а также символов тире и нижнего подчеркивания. Например, *http://site.ru/m/* или *http://site.ru/mobile/*. Изменение параметра означает изменение URL-адреса мобильной версии. При изменении параметра следует скорректировать содержание файла robots.txt по необходимости. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'default' => 'm',
				'maxlength' => 20,
				'depend' => 'mobile_version',
			),
			'mobile_subdomain' => array(
				'type' => 'checkbox',
				'name' => 'Использовать имя мобильной версии в качестве поддомена',
				'help' => 'Если отмечено, то название мобильной версии будет использоваться в качестве поддомена. Например, *http://m.site.ru/* или *http://mobile.site.ru/*. Изменение параметра означает изменение URL-адреса мобильной версии. Возможно Вам потребуется скорректировать файл robots.txt. ВАЖНО: требуется внесение соответствующих "CNAME" или "A" записей в dns-зону домена, а также изменение настроек веб-сервера. Например, для Apache параметр "ServerAlias", для NGINX параметр "server_name". Прежде, чем изменять параметр настройки, убедитесь, что имя мобильной версии не совпадает ни с одной из частей доменного имени. Например, для URL-адреса *http://site.ru/* в качестве имени мобильной версии нельзя использовать: *site* и *ru*. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'default' => false,
				'depend' => 'mobile_version',
			),
			'hr23' => 'hr',
			'no_x_frame' => array(
				'type' => 'checkbox',
				'name' => 'Запретить загружать сайт во frame',
				'help' => 'Если не отмечено, то сайт можно будет вставлять во frame. Повышается опасность clickjacking-атак на сайт.',
			),
			'hr3' => 'hr',
			'route_method' => array(
				'type' => 'select',
				'name' => 'Вариант генерации ЧПУ',
				'help' => 'Cпособ предобразования названия при автоматическом генерировании ЧПУ. ',
				'select' => array(
					1 => 'транслит',
					2 => 'перевод на английский',
					3 => 'русская кириллица',
				),
			),
			'route_translit_from' => array(
				'type' => 'textarea',
				'name' => 'Способ преобразования',
				'help' => 'Массив кириллических символов и соответствующих им латинских символов. Символы разделены пробелом. Параметр появляется, если в качестве варианта генерации ЧПУ выбран «транслит».',
			),
			'route_translit_to' => array(
				'type' => 'textarea',
				'hide' => true,
			),
			'route_translate_yandex_key' => array(
				'type' => 'text',
				'name' => 'API-ключ сервиса Яндекс Переводчик<br><a href="https://tech.yandex.ru/keys/get/?service=trnsl" target="_blank">Получить</a>',
			),
			'route_end' => array(
				'type' => 'text',
				'name' => 'ЧПУ оканчивается на',
				'help' => 'Можно использовать слеш или иное окончание. Например, если установить *.php*, все адреса страниц сайта будут формироваться как *http://site.ru/news.php* Для *.html* – *http://site.ru/news.html*. По умолчанию слеш и *http://site.ru/news/*.',
			),
			'route_auto_module' => array(
				'type' => 'checkbox',
				'name' => 'Генерировать ЧПУ для модулей автоматически',
				'help' => 'Формирование ЧПУ для модулей (новостей, категорий новостей, товаров, статей и пр.) в автоматическом режиме из названий. Если галка отключена, ЧПУ отдельного товара будет генерироваться как *http://site.ru/shop/cat1/show5/*. Если галка стоит, то при сохранении ЧПУ сгенерируется автоматически из названия категорий и имени элементов, т.е. *http://site.ru/shop/telefony/nokia8800/*.',
			),
			'hr4' => 'hr',
			'ftp_host' => array(
				'type' => 'text',
				'name' => 'FTP-хост',
				'help' => 'Адрес FTP-сервера, для подключения к хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_login' => array(
				'type' => 'text',
				'name' => 'FTP-логин',
				'help' => 'Имя ftp-пользователя, для подключения хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_password' => array(
				'type' => 'password',
				'name' => 'FTP-пароль',
				'help' => 'Пароль ftp-пользователя, для подключения хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_dir' => array(
				'type' => 'text',
				'name' => 'Относительный путь до сайта',
				'help' => 'Нужен, если указанный FTP-пользователь после авторизации попадает не в корень сайта, а неколькими уровнями выше. Тогда нужно указать путь к корню сайта. Например, */www/site.ru/*, узнайте на хостинге. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'hr5' => 'hr',
			'email' => array(
				'type' => 'email',
				'name' => 'e-mail сайта',
				'help' => 'Адрес, на который по умолчанию приходят уведомления и который указывается в обратном адресе всех писем. Здесь может быть указан только один адрес.',
			),
			'smtp_mail' => array(
				'type' => 'checkbox',
				'name' => 'Использовать SMTP-авторизацию при отправке почты с сайта',
				'help' => 'Обязательно используйте исходящую SMTP-авторизацию, иначе письма-уведомления с сайта могут блокироваться большинством спам-фильтров.',
			),
			'smtp_host' => array(
				'type' => 'text',
				'name' => 'SMTP-хост (например, tls://smtp.mail.ru)',
				'depend' => 'smtp_mail',
				
			),
			'smtp_login' => array(
				'type' => 'text',
				'name' => 'SMTP-логин (например, ivanov@mail.ru)',
				'help' => 'Ваш почтовый логин, для входа в почту.',
				'depend' => 'smtp_mail',
			),
			'smtp_password' => array(
				'type' => 'password',
				'name' => 'SMTP-пароль',
				'help' => 'Ваш почтовый пароль, для входа в почту.',
				'depend' => 'smtp_mail',
			),
			'smtp_port' => array(
				'type' => 'numtext',
				'name' => 'SMTP-порт (например, 465 или 587)',
				'help' => 'В большинстве случаев можно не указывать. Если используется протокол SSL, то чаще всего необходимо указывать SMTP-порт 465. Если используется протокол TLS, то чаще всего необходимо указывать SMTP-порт 587.',
				'depend' => 'smtp_mail',
			),
			'hr6' => 'hr',
			'cache_memcached' => array(
				'type' => 'checkbox',
				'name' => 'Кэширование Memcached',
				'help' => 'Подключает Memcached-кэширование. По умолчанию используется файловое кэширование. Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
			),
			'cache_memcached_host' => array(
				'type' => 'text',
				'name' => 'Xост сервера Memcached',
				'help' => 'Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
				'depend' => 'cache_memcached',
			),
			'cache_memcached_port' => array(
				'type' => 'numtext',
				'name' => 'Порт сервера Memcached',
				'help' => 'Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
				'depend' => 'cache_memcached',
			),
			'hr7' => 'hr',
			'sms' => array(
				'type' => 'checkbox',
				'name' => 'Подключить SMS-уведомления<br>(требуется <a href="http://www.bytehand.com/?r=c3c2c0125f667cb1" target="_blank">регистрация</a>).',
				'help' => 'SMS-рассылки интегрирована в модули «Обратная связь», «Оформление заказа», «Комментарии», «Вопрос-Ответ» для уведолмения администраторов. А также в модуль «Рассылки» для массовой рассылки SMS. Подключеть SMS-уведомления нужно в настройках соответствующего модуля. Для включения SMS на сайте необходимо зарегистрироваться в системе [Byte Hand](http://www.bytehand.com/?r=c3c2c0125f667cb1). На хостинге должны быть открыты соответствующие порты (обычно 3800)',
			),
			'sms_key' => array(
				'type' => 'text',
				'name' => 'Ключ',
				'help' => 'Данные из настроек сервиса Byte Hand.',
				'depend' => 'sms',
			),
			'sms_id' => array(
				'type' => 'text',
				'name' => 'ID',
				'help' => 'Данные из настроек сервиса Byte Hand.',
				'depend' => 'sms',
			),
			'sms_signature' => array(
				'type' => 'text',
				'name' => 'Подпись',
				'help' => 'Данные из настроек сервиса Byte Hand.',
				'depend' => 'sms',
			),
			'hr8' => 'hr',
			'timezone' => array(
				'type' => 'text',
				'name' => 'Таймзона',
				'help' => 'Часовой пояс, [список часовых поясов](http://www.php.net/manual/en/timezones.php). По умолчанию: Europe/Moscow',
			),
		),
		'mod_developer_tab' => array (
			'mod_developer' => array(
				'type' => 'checkbox',
				'name' => 'Включить режим разработки',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая все уведомления сервера с замечаниями и PHP-ошибками. Режим разработки также отключает сжатие CSS и JS файлов. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_admin' => array(
				'type' => 'checkbox',
				'name' => 'Показывать ошибки только администратору',
			),
			'mod_developer_tech' => array(
				'type' => 'checkbox',
				'name' => 'Перевести сайт в режим обслуживания',
				'help' => 'Если отметить, сайт будет доступен только авторизованному администратору. Все остальные посетители сайта будут видеть только страницу themes/503.php – «Сайт в разработке, временно недоступен». (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_cache' => array(
				'type' => 'checkbox',
				'name' => 'Отключить кэширование',
				'help' => 'Данный параметр разработчику необходимо обязательно вкючать при доработке скриптов и обязательно отключать в штатном режиме работы сайта. Постоянно отключенное кэширование может замедлить работу системы! (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_delete_cache' => array(
				'type' => 'checkbox',
				'name' => 'Сбросить кэш',
				'help' => 'Если отметить, внутренний кэш сайта будет удален. Галка при этом не останется отмечена. Рекомендуется сбрасывать кеш, после внесения изменений в скрипты. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'cache_extreme' => array(
				'type' => 'checkbox',
				'name' => '<a href="http://www.diafan.ru/highload/">Экстремальное кеширование</a>',
				'help' => 'Внимание! Возможно ограничение функционала! Используйте только после ознакомления с назначением данного параметра.',
			),
			'mod_developer_minify' => array(
				'type' => 'checkbox',
				'name' => 'Включить сжатие HTML-контента',
				'help' => 'Если отметить, сгенерированная HTML-страница будет сжиматься перед отправкой в веб-браузер клиента.',
			),
			'mod_developer_profiling' => array(
				'type' => 'checkbox',
				'name' => 'Включить профилирование SQL-запросов',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая список всех использованных системой SQL-запросов и время их выполнения. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные',
		'mod_developer_tab' =>'Режим разработки',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'only_edit', // модуль состоит только из формы редактирования
		'tab_card', // использование вкладок
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		foreach ($this->diafan->_languages->all as $language)
		{
			$base = $this->variables['base'];
			$this->variables['base'] = array();
			$this->variables['base']['title_'.$language["id"]] = array(
				'type' => 'text',
				'name' => $this->diafan->_('Название сайта').($language["shortname"] ? ' ('.$language["shortname"].')' : ''),
				'help' => $this->diafan->_('Название сайта используется при автоматической генерации заголовков title для всех страниц сайта, а также для писем-уведомлений и пр.')
			);
			foreach ($base as $k => $v)
			{
				$this->variables['base'][$k] = $v;
			}
		}
		
		// поддержка старой версии config.php
		if(! defined('MOBILE_PATH')) define('MOBILE_PATH', 'm');
		if(! defined('MOBILE_SUBDOMAIN')) define('MOBILE_SUBDOMAIN', false);
		
		// запрещаем редактирование значения полей mobile_path и mobile_subdomain в демо-версии
		if(defined('IS_DEMO') && IS_DEMO)
		{
			$this->diafan->variable('mobile_path', 'disabled', true);
			$this->diafan->variable('mobile_subdomain', 'disabled', true);
		}
	}

	/**
	 * Выводит форму редактирования параметров сайта
	 * @return void
	 */
	public function edit()
	{
		if (file_exists(ABSOLUTE_PATH.'config.php') &&  ! is_writable(ABSOLUTE_PATH.'config.php'))
		{
			echo '<div class="error">'.$this->diafan->_('Установите права на запись (777) для файла конфигурации config.php').'</div>';
		}
		parent::__call('edit', array());
	}

	/**
	 * Якорь для ЧПУ
	 * @return void
	 */
	public function edit_variable_hr3()
	{
		echo '<h2><a name="url"></a>
					</h2>';
	}
	
	/**
	 * Задает значения полей для формы
	 * 
	 * @return array
	 */
	public function get_values()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			$values = array(
				'db_prefix' => ! empty($_SESSION["CONFIG_DB_PREFIX"]) ? $_SESSION["CONFIG_DB_PREFIX"] : 'diafan_',
				'db_url' => ! empty($_SESSION["CONFIG_DB_URL"]) ? $_SESSION["CONFIG_DB_URL"] : 'mysqli://user:pass@localhost/dbname',
				'db_charset' => ! empty($_SESSION["CONFIG_DB_CHARSET"]) ? $_SESSION["CONFIG_DB_CHARSET"] : 'utf8',
				'userfiles' => ! empty($_SESSION["CONFIG_USERFILES"]) ? $_SESSION["CONFIG_USERFILES"] : 'userfls',
				'admin_folder' => ! empty($_SESSION["CONFIG_ADMIN_FOLDER"]) ? $_SESSION["CONFIG_ADMIN_FOLDER"] : 'admin',
				'no_x_frame' =>  ! empty($_SESSION["CONFIG_NO_X_FRAME"]) ? true : false,
				'mod_developer' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER"]) ? true : false,
				'mod_developer_tech' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_TECH"]) ? true : false,
				'mod_developer_minify' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_MINIFY"]) ? true : false,
				'mod_developer_profiling' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_PROFILING"]) ? true : false,
				'mod_developer_cache' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_CACHE"]) ? true : false,
				'cache_extreme' => ! empty($_SESSION["CONFIG_CACHE_EXTREME"]) ? true : false,
				'ftp_host' => ! empty($_SESSION["CONFIG_FTP_HOST"]) ? $_SESSION["CONFIG_FTP_HOST"] : '',
				'ftp_login' => ! empty($_SESSION["CONFIG_FTP_LOGIN"]) ? $_SESSION["CONFIG_FTP_LOGIN"] : '',
				'ftp_password' => ! empty($_SESSION["CONFIG_FTP_PASSWORD"]) ? $_SESSION["CONFIG_FTP_PASSWORD"] : '',
				'ftp_dir' => ! empty($_SESSION["CONFIG_FTP_DIR"]) ? $_SESSION["CONFIG_FTP_DIR"] : '',
				'mobile_path' => ! empty($_SESSION["CONFIG_MOBILE_PATH"]) ? $_SESSION["CONFIG_MOBILE_PATH"] : 'm',
				'mobile_subdomain' => ! empty($_SESSION["CONFIG_MOBILE_SUBDOMAIN"]) ? true : false,
			);
		}
		else
		{
			$values = array(
				'db_prefix' => DB_PREFIX,
				'db_url' => DB_URL,
				'db_charset' => DB_CHARSET,
				'userfiles' => USERFILES,
				'admin_folder' => ADMIN_FOLDER,
				'no_x_frame' => NO_X_FRAME,
				'mod_developer' => MOD_DEVELOPER,
				'mod_developer_admin' => defined('MOD_DEVELOPER_ADMIN') ? MOD_DEVELOPER_ADMIN : false,
				'mod_developer_tech' => MOD_DEVELOPER_TECH,
				'mod_developer_minify' => defined('MOD_DEVELOPER_MINIFY') ? MOD_DEVELOPER_MINIFY : false,
				'mod_developer_profiling' => MOD_DEVELOPER_PROFILING,
				'mod_developer_cache' => MOD_DEVELOPER_CACHE,
				'cache_extreme' => defined('CACHE_EXTREME') ? CACHE_EXTREME : false,
				'ftp_host' => FTP_HOST,
				'ftp_login' => FTP_LOGIN,
				'ftp_password' => FTP_PASSWORD,
				'ftp_dir' => FTP_DIR,
				'mobile_path' => defined('MOBILE_PATH') ? MOBILE_PATH : 'm',
				'mobile_subdomain' => defined('MOBILE_SUBDOMAIN') ? MOBILE_SUBDOMAIN : false,
			);
		}
		$url = parse_url($values['db_url']);

		$translit_array = explode('````', DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='translit_array' LIMIT 1"), 2);
		$array = array(
			'db_host'                    => urldecode($url['host']).(! empty($url['port']) ? ':'.$url['port'] : ''),
			'db_user'                    => urldecode($url['user']),
			'db_pass'                    => isset($url['pass']) ? urldecode($url['pass']) : '',
			'db_name'                    => substr(urldecode($url['path']), 1),
			'db_prefix'                  => $values['db_prefix'],
			'email'                      => EMAIL_CONFIG,
			'db_charset'                 => $values['db_charset'],
			'userfiles'                  => $values['userfiles'],
			'admin_folder'               => $values['admin_folder'],
			'mobile_version'             => MOBILE_VERSION,
			'mobile_path'                => $values['mobile_path'],
			'mobile_subdomain'           => $values['mobile_subdomain'],
			'no_x_frame'                 => $values['no_x_frame'],
			'mod_developer'              => $values['mod_developer'],  
			'mod_developer_admin'        => $values['mod_developer_admin'],
			'mod_developer_tech'         => $values['mod_developer_tech'],
			'mod_developer_minify'       => $values['mod_developer_minify'],
			'mod_developer_profiling'    => $values['mod_developer_profiling'],
			'mod_developer_cache'        => $values['mod_developer_cache'],
			'cache_extreme'              => $values['cache_extreme'],
			'mod_developer_delete_cache' => false,

			'route_method'               => DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='method' LIMIT 1"),
			'route_translit_from'        => $translit_array[0],
			'route_translit_to'          => ! empty($translit_array[1]) ? $translit_array[1] : '',
			'route_end'                  => ROUTE_END,
			'route_auto_module'          => ROUTE_AUTO_MODULE,
			'route_translate_yandex_key' => DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='translate_yandex_key' LIMIT 1"),

			'ftp_host'                   => FTP_HOST,
			'ftp_login'                  => FTP_LOGIN,
			'ftp_password'               => FTP_PASSWORD,
			'ftp_dir'                    => FTP_DIR,
			
			'smtp_mail'                  => SMTP_MAIL,
			'smtp_host'                  => SMTP_HOST,
			'smtp_login'                 => SMTP_LOGIN,
			'smtp_password'              => SMTP_PASSWORD,
			'smtp_port'                  => SMTP_PORT,

			'cache_memcached'            => CACHE_MEMCACHED,
			'cache_memcached_host'       => CACHE_MEMCACHED_HOST,
			'cache_memcached_port'       => CACHE_MEMCACHED_PORT,

			'sms'                        => SMS,
			'sms_id'                     => SMS_ID,
			'sms_key'                    => SMS_KEY,
			'sms_signature'              => SMS_SIGNATURE,

			'timezone'                   => defined('TIMEZONE') ? TIMEZONE : '',
		);

		foreach ($this->diafan->_languages->all as $language)
		{
			$array['title_'.$language["id"]] = (defined('TIT'.$language["id"]) ? constant('TIT'.$language["id"]) : '');
		}

		return $array;
		
	}

	/**
	 * Проверка параметров подключения к Memcached
	 * 
	 * @return void
	 */
	public function validate_variable_cache_memcached()
	{
		if(! empty($_POST["cache_memcached"]))
		{
			if(! class_exists('Memcached'))
			{
				$this->diafan->set_error("cache_memcached", "Не установлен модуль Memcached для PHP.");
			}
			elseif(empty($_POST["cache_memcached_host"]) || empty($_POST["cache_memcached_port"]))
			{
				$this->diafan->set_error("cache_memcached", "Укажите хост и порт сервера Memcached.");
			}
			else
			{
				Custom::inc('includes/cache.php');
				Custom::inc('includes/cache/cache.memcached.php');
				if(! Cache_memcached::check($_POST["cache_memcached_host"], $_POST["cache_memcached_port"]))
				{
					$this->diafan->set_error("cache_memcached", "Не верные параметры подключения.");
				}
			}
		}
	}

	/**
	 * Проверка параметров подключения по FTP
	 * 
	 * @return void
	 */
	public function validate_variable_ftp()
	{
		if(! empty($_POST["ftp_host"]))
		{
			if(! extension_loaded('ftp'))
			{
				$this->diafan->set_error("ftp_host", "Не установлено PHP-расширение для работы с FTP.");
			}
			if(empty($_POST["ftp_login"]))
			{
				$this->diafan->set_error("ftp_login", "Укажите имя пользователя для подключения по FTP.");
			}
			if(empty($_POST["ftp_password"]))
			{
				$this->diafan->set_error("ftp_password", "Укажите пароль для подключения по FTP.");
			}
			if(! empty($_POST["ftp_login"]) && ! empty($_POST["ftp_password"]))
			{
				$host = $_POST["ftp_host"];
				$port = null;
				if(strpos($host, ':') !== false)
				{
					list($host, $port) = explode(':', $_POST["ftp_host"], 2);
				}
				if(! $conn_id = ftp_connect($host, $port))
				{
					$this->diafan->set_error("ftp_host", "Ошибка подключения по FTP. Хост не найден.");
				}
				elseif(! ftp_login($conn_id, $_POST["ftp_login"], $_POST["ftp_password"]))
				{
					ftp_close($conn_id);
					$this->diafan->set_error("ftp_host", 'Ошибка подключения по FTP. Указаны неверные данные для подлкючения.');
				}
				else
				{
					ftp_pasv($conn_id, true);
					if (! ftp_chdir($conn_id, $_POST["ftp_dir"]))
					{
						$this->diafan->set_error("ftp_dir", 'Неправильно задан относительный путь.');
					}
					ftp_close($conn_id);
				}
			}
		}
	}

	/**
	 * Валидация имени папки
	 * 
	 * @return void
	 */
	public function validate_variable_admin_folder()
	{
		if(strpos($_POST["admin_folder"], '/') !== false)
		{
			$this->diafan->set_error("admin_folder", "Символ / не доступстим в названии папки");
		}
	}

	/**
	 * Проверка параметров подключения к SMTP
	 * 
	 * @return void
	 */
	public function validate_variable_smtp_mail()
	{
		if(! empty($_POST["smtp_mail"]))
		{
			if(empty($_POST["smtp_host"]) || empty($_POST["smtp_login"]) || empty($_POST["smtp_password"]))
			{
				$this->diafan->set_error("smtp_mail", "Укажите хост, логин, пароль для SMTP-авторизации");
			}
		}
	}
	
	/**
	 * Проверка имени мобильной версии в url-адресе
	 * 
	 * @return void
	 */
	public function validate_variable_mobile_path()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(empty($_POST['mobile_path']) || $_POST['mobile_path'] != 'm')
			{
				$this->diafan->set_error("mobile_path", "Изменение имени мобильной версии в URL-адресе в демо-версии не доступно.");
			}
		}
		elseif(! empty($_POST["mobile_version"]))
		{
			if(empty($_POST['mobile_path']) || preg_match('/[^a-z0-9-_]+/', $_POST['mobile_path']))
			{
				$this->diafan->set_error("mobile_path", "Укажите корректное имя мобильной версии в URL-адресе.");
			}
			elseif(! empty($_POST['mobile_subdomain']))
			{
				$rew = explode('.', MAIN_DOMAIN);
				if(false !== array_search($_POST['mobile_path'], $rew))
				{
					$this->diafan->set_error("mobile_path", "Имя мобильной версии в URL-адресе не должно совпадать ни с одной из частей доменного имени.");
				}
				else
				{
					$url = $_POST['mobile_path'].'.'.MAIN_DOMAIN;
					$answer = $this->diafan->get_http_status($url);
					if (Core::HTTP_OK != $answer && Core::HTTP_SERVICE_UNAVAILABLE != $answer)
					{
						$this->diafan->set_error("mobile_path", "Имя мобильной версии в URL-адресе не поддерживается хостингом.");
						$this->diafan->set_error("mobile_subdomain", 'HTTP status code: '.$answer);
					}
				}
			}
		}
	}

	/**
	 * Проверка использования имени мобильной версии в качестве поддомена
	 * 
	 * @return void
	 */
	public function validate_variable_mobile_subdomain()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(! empty($_POST['mobile_subdomain']))
			{
				$this->diafan->set_error("mobile_subdomain", "Использования имени мобильной версии в качестве поддомена в демо-версии не доступно.");
			}
		}
	}

	/**
	 * Сохраняет файл конфигурации
	 * 
	 * @return boolean
	 */
	public function save()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return false;
		}

		//проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', 'config'))
		{
			$this->diafan->redirect(URL);
			return false;
		}

		$dir_url_path = '';

		if (getenv('REQUEST_URI') != "/".ADMIN_FOLDER."/config/save1/")
		{
			$dir_url_path = str_replace("/".ADMIN_FOLDER."/config/save1/", "", getenv('REQUEST_URI'));
		}

		$admin_folder = substr($this->diafan->filter($_POST, "string", "admin_folder", ADMIN_FOLDER, 1), 0, 20);

		$mobile_path = substr(preg_replace('/[^a-z0-9-_]+/', '', $this->diafan->filter($_POST, "string", "mobile_path", MOBILE_PATH, 1)), 0, 20);
		if(! empty($_POST['mobile_subdomain']))
		{
			$rew = explode('.', MAIN_DOMAIN);
			$mobile_path = false === array_search($mobile_path, $rew) ? $mobile_path : '';
		}
		// запрещаем запись некорректного значения для MOBILE_PATH
		$mobile_path = ! empty($mobile_path) ? $mobile_path : 'm';

		$new_values = array(
				'DB_URL' => str_replace('"', '\\"', DB_URL),
				'DB_PREFIX' => DB_PREFIX,
				'EMAIL_CONFIG' => str_replace('"', '\\"', $this->diafan->filter($_POST, "string", "email", EMAIL_CONFIG)),
				'USERFILES' => $this->diafan->filter($_POST, "string", "userfiles", USERFILES),
				'ADMIN_FOLDER' => $admin_folder,
				'MOBILE_VERSION' => (! empty($_POST["mobile_version"]) ? true : false),
				'MOBILE_PATH' => $mobile_path,
				'MOBILE_SUBDOMAIN' => (! empty($_POST["mobile_subdomain"]) ? true : false),
				'NO_X_FRAME' => (! empty($_POST["no_x_frame"]) ? true : false),
				'MOD_DEVELOPER' => (! empty($_POST["mod_developer"]) ? true : false),
				'MOD_DEVELOPER_ADMIN' => (! empty($_POST["mod_developer_admin"]) ? true : false),
				'MOD_DEVELOPER_TECH' => (! empty($_POST["mod_developer_tech"]) ? true : false),
				'MOD_DEVELOPER_CACHE' => (! empty($_POST["mod_developer_cache"]) ? true : false),
				'CACHE_EXTREME' => (! empty($_POST["cache_extreme"]) ? true : false),
				'MOD_DEVELOPER_MINIFY' => (! empty($_POST["mod_developer_minify"]) ? true : false),
				'MOD_DEVELOPER_PROFILING' => (! empty($_POST["mod_developer_profiling"]) ? true : false),
				'FTP_HOST' => $this->diafan->filter($_POST, "string", "ftp_host", FTP_HOST),
				'FTP_DIR' => $this->diafan->filter($_POST, "string", "ftp_dir", FTP_DIR),
				'FTP_LOGIN' => $this->diafan->filter($_POST, "string", "ftp_login", FTP_LOGIN),
				'FTP_PASSWORD' => $this->diafan->filter($_POST, "string", "ftp_password", FTP_PASSWORD),
				'SMTP_MAIL' => (! empty($_POST["smtp_mail"]) ? true : false),
				'SMTP_HOST' => $this->diafan->filter($_POST, "string", "smtp_host"),
				'SMTP_LOGIN' => $this->diafan->filter($_POST, "string", "smtp_login"),
				'SMTP_PASSWORD' => $this->diafan->filter($_POST, "string", "smtp_password"),
				'SMTP_PORT' => $this->diafan->filter($_POST, "string", "smtp_port"),
				'CACHE_MEMCACHED' => (class_exists('Memcached') && ! empty($_POST["cache_memcached"]) ? true : false),
				'CACHE_MEMCACHED_HOST' => $this->diafan->filter($_POST, "string", "cache_memcached_host"),
				'CACHE_MEMCACHED_PORT' => $this->diafan->filter($_POST, "string", "cache_memcached_port"),
				'TIMEZONE' => $this->diafan->filter($_POST, "string", "timezone"),
				'ROUTE_END' => $this->diafan->filter($_POST, "string", "route_end"),
				'ROUTE_AUTO_MODULE' => (! empty($_POST["route_auto_module"]) ? true : false),
				'SMS' => (! empty($_POST["sms"]) ? true : false),
				'SMS_ID' => $_POST["sms_id"],
				'SMS_KEY' => $_POST["sms_key"],
				'SMS_SIGNATURE' => $_POST["sms_signature"],
				'CUSTOM' => implode(',', Custom::names()),
			);
		foreach ($this->diafan->_languages->all as $language)
		{
			$new_values['TIT'.$language["id"]] = $this->diafan->filter($_POST, "string", "title_".$language["id"]);
		}
		$route_method = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='method' LIMIT 1");
		if(! $route_method)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'method', '%d')", $_POST["route_method"]);
		}
		elseif($route_method["value"] != $_POST["route_method"])
		{
			DB::query("UPDATE {config} SET value='%d' WHERE module_name='route' AND name='method'", $_POST["route_method"]);
		}

		$route_translit_array = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='translit_array' LIMIT 1");
		if(! $route_translit_array)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'translit_array', '%h')", $_POST["route_translit_from"]."````".$_POST["route_translit_to"]);
		}
		elseif($route_translit_array["value"] != $_POST["route_translit_from"]."````".$_POST["route_translit_to"])
		{
			DB::query("UPDATE {config} SET value='%h' WHERE module_name='route' AND name='translit_array'", $_POST["route_translit_from"]."````".$_POST["route_translit_to"]);
		}

		$route_translate_yandex_key = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='translate_yandex_key' LIMIT 1");
		if(! $route_translate_yandex_key)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'translate_yandex_key', '%h')", $_POST["route_translate_yandex_key"]);
		}
		elseif($route_translate_yandex_key["value"] != $_POST["route_translate_yandex_key"])
		{
			DB::query("UPDATE {config} SET value='%h' WHERE module_name='route' AND name='translate_yandex_key'", $_POST["route_translate_yandex_key"]);
		}

		Custom::inc('includes/config.php');
		Config::save($new_values, $this->diafan->_languages->all);

		if (! empty($_POST["mod_developer_delete_cache"]) || ROUTE_END != $this->diafan->filter($_POST, "string", "route_end"))
		{
			$this->diafan->_cache->delete("", array());
		}
		if ($admin_folder == ADMIN_FOLDER)
		{
			$this->diafan->redirect(URL.'success1/');
		}
		else
		{
			$this->diafan->redirect('http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.$admin_folder.'/config/success1/');
		}
		return true;
	}
}

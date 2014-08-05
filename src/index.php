<?php
// Проверка версии PHP
if(version_compare(phpversion(), "5.3.0", "<"))	die('<b>ERROR:</b> PHP version 5.3+ needed!');

require_once('gb/common.php');	// Общие функции системы

$dbase = new ww1_database_solders(Q_SIMPLE);

$tmp = trim($_REQUEST['region'] . ' ' . $_REQUEST['place']);
$squery = $_REQUEST['surname'] . ' ' . $_REQUEST['name'] . (empty($tmp) ? '' : " ($tmp)");
$squery = trim($squery);

html_header('Поиск' . (empty($squery) ? 'персоны' : '"' . htmlspecialchars($squery) . '"'));
show_records_stat();
?>
<form action="<?php print $_SERVER['PHP_SELF']?>#report">
	<h2>Поиск персоны</h2>
	<p class="small alignright"><a href="/extsearch.php">Расширенный поиск</a></p>
	<?php $dbase->search_form(); ?>
	<div class="buttons">
		<button class="search" type="submit">Искать</button>
		<button class="clearForm" type="button">Очистить</button>
	</div>
	<div id="help">
		<p class="nb">Система при поиске автоматически пытается расширить Ваш запрос с&nbsp;учётом возможных ошибок и&nbsp;сокращений в&nbsp;написании имён и&nbsp;фамилий.</p>
		<p class="nb"><strong>Обратите внимание:</strong> во&nbsp;времена Первой Мировой Войны не&nbsp;было современных республик и&nbsp;областей&nbsp;— были губернии и&nbsp;уезды Российской Империи, границы которых часто отличаются от&nbsp;границ современных территорий. Места жительства в&nbsp;системе указываются по&nbsp;состоянию на&nbsp;даты войны.</p>
		<p class="nb">Для поиска частей слов используйте метасимволы: «?» (вопрос)&nbsp;— заменяет один любой символ, «*» (звёздочка)&nbsp;— заменяет один и&nbsp;более любых символов. При использовании метасимволов автоматическое расширение запроса отключается.</p>
	</div>
</form>
<?php
if($dbase->have_query){
	load_check();
	$report = $dbase->do_search();
	log_event($report->records_cnt);

	// Выводим результаты в html
	$brief_fields = array(
		'surname'	=> 'Фамилия',
		'name'		=> 'Имя Отчество',
		'region'	=> 'Губерния, Уезд, Волость',
		'place'		=> 'Волость/Нас.пункт',
	);
	$detailed_fields = array(
		'rank'		=> 'Воинское звание',
		'religion'	=> 'Вероисповедание',
		'marital'	=> 'Семейное положение',
		'reason'	=> 'Причина выбытия',
		'date'		=> 'Дата выбытия',
		'source'	=> 'Источник',
		'comments'	=> '',
	);
	$report->show_report($brief_fields, $detailed_fields);
}
?>
<p style="text-align: center; margin-top: 3em;"><a href="/stat.php">Статистика</a> | <a href="/todo.php">ToDo-list</a> | <a href="http://forum.svrt.ru/index.php?showtopic=3936&view=getnewpost" target="_blank">Обсуждение сервиса</a> (<a href="http://forum.svrt.ru/index.php?showtopic=7343&view=getnewpost" target="_blank">техническое</a>) | <a href="crue.php">Команда проекта</a></p>
<?php

// Выводим ссылки для поисковых роботов на 12 последних результатов поиска
$db = db_open();
$stmt = $db->prepare('SELECT `query`, `url` FROM `logs` WHERE `query` != "" AND `records_found` ORDER BY datetime DESC LIMIT 12');
$stmt->execute();
$stmt->bind_result($squery, $url);
$res = array();
while($stmt->fetch()){
	if(empty($squery))	$squery = '.';
	$res[] = "<a href='$url'>" . htmlspecialchars($squery) . "</a>";
}
$stmt->close();
print "<p class='lastq aligncenter'>Некоторые последние поисковые запросы в систему: " . implode(', ', $res) . "</p>\n";

html_footer();
db_close();
?>
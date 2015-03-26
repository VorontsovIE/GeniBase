<?php
/**
 * These functions are needed to translate pronounce or scripting
 * of words from one language to another.
 * 
 * @since	2.2.3
 *
 * @package	GeniBase
 * @subpackage i18n
 *
 * @copyright	Copyright © 2015, Andrey Khrolenok (andrey@khrolenok.ru)
 */

// Direct execution forbidden for this script
if( !defined('GB_VERSION') || count(get_included_files()) == 1)	die('<b>ERROR:</b> Direct execution forbidden!');



/*
Тестовая таблица:
$tests = array(
	'Александров Алексей',	'Алексеев Яков',		'Андреев ИВАН',		'Беляев никита',
	'Беляков Сергей', 		'Бондаренко ЮРИЙ',		'Васильев Кирилл',	'Ващенко Семён',
	'Верещагин Семен',		'Власов Авраам Ил.',	'Волков Захар. Никол.',
	'Воробьёв Пав. Ильин',	'Гаврилов Яков Никифорович',		'Глущенко Готфрид Готлибов',
	'Гончаренко Янкель Степ.',		'Гончаров Иулиан Иос.',		'Грачев Ян Марцинович',
	'Григорьев',	'Грищенко',		'Гуляев',	
	'Гущин',		'Данилов',		'Дегтярев',		'Дмитриев',		'Дьяченко',	
	'Емельянов',	'Зайцев',		'Заяц',			'Иващенко',		'Ильин',	
	'Ищенко',		'Кириченко',	'Киселев',		'Князев',		'Ковалёв',	
	'Коваленко',	'Ковальчук',	'Козлов',		'Королёв',		'Кравченко',	
	'Кудрявцев',	'Кузнецов',		'Лебедев',		'Левченко',		'Лещенко',	
	'Лукьянов',		'Марченко',		'Матвеев',		'Мельник',		'Мельников',	
	'Мещеряков',	'Михайлов',		'Мищенко',		'Молчанов',		'Нечаев',	
	'Николаев',		'Овсянников',	'Овчинников',	'Онищенко',		'Орлов',	
	'Павлов',		'Панченко',		'Пащенко',		'Петров',		'Поздняков',	
	'Полещук',		'Поляков',		'Румянцев',		'Рябов',		'Савченко',	
	'Семёнов',		'Сергеев',		'Соколов',		'Соловьёв',		'Степанов',	
	'Терещенко',	'Тимофеев',		'Тищенко',		'Ткач',			'Ткаченко',	
	'Ткачук',		'Третьяков',	'Тыщенко',		'Ульянов',		'Фёдоров',	
	'Филиппов',		'Фролов',		'Харченко',		'Чернов',		'Черный',	
	'Чернышёв',		'Чернявский',	'Чистяков',		'Шевченко',		'Шевчук',	
	'Ширяев',		'Щеглов',		'Щеголев',		'Щербак',		'Щербаков',	
	'Щербина',		'Щербинин',		'Щукин',		'Юрченко',		'Якимов',	
	'Яковенко',		'Яковлев',		'Янковский',	'Яценко',		'Яшнискин',	
	'Ященко',
);
*/

class GB_Transcriptor {
	const TRANSCRIBE	= 1;
	const TRANSLITERATE	= 2;
	
	/** A multidimentional array of text translation rules from one language to another. */
	static $trans_table = array();

	/**
	 * Transcript text from one language to another.
	 * 
	 * @since	2.2.3
	 * 
	 * @param string $text		Source text.
	 * @param string $from_lang	Source language code.
	 * @param string $to_lang	Destination language code. Default current locale language.
	 * @param int $mode			Convertion mode. {@see GB_Transcriptor} Default self::TRANSCRIBE
	 * @return string	Converted text.
	 */
	static function transcript($text, $from_lang, $to_lang = null, $mode = self::TRANSCRIBE){
		$text = trim($text);
		if( empty($text) || !preg_match('/\w/u', $text) )
			return $text;
		
		if( null === $to_lang )
			$to_lang = get_locale();
	
		$from_lang = strtolower($from_lang);
		$to_lang = strtolower($to_lang);

		if( !isset(self::$trans_table) || !is_array(self::$trans_table)
				|| !isset(self::$trans_table[$mode]) || !is_array(self::$trans_table[$mode]))
			return $text;
		if( !isset(self::$trans_table[$mode][$from_lang]) || !is_array(self::$trans_table[$mode][$from_lang]) ){
			$from_lang = strtok($from_lang, '_');
			if( !isset(self::$trans_table[$mode][$from_lang]) || !is_array(self::$trans_table[$mode][$from_lang]) )
				return $text;
		}
		if( !isset(self::$trans_table[$mode][$from_lang][$to_lang]) || !is_array(self::$trans_table[$mode][$from_lang][$to_lang]) ){
			$to_lang = strtok($to_lang, '_');
			if( !isset(self::$trans_table[$mode][$from_lang][$to_lang]) || !is_array(self::$trans_table[$mode][$from_lang][$to_lang]) )
				return $text;
		}
		
		// If needed split text to single words and translate it separately
		if(preg_match('/\W/uS', $text)){
			$words = preg_split('/(\W+)/uS', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
			for($i = 0; $i < count($words); $i += 2)
				$words[$i] = self::transcript($words[$i], $from_lang, $to_lang, $mode);
			return implode($words);
		}
		
		$old_enc = mb_internal_encoding();
		mb_internal_encoding('UTF-8');
		$tr = self::$trans_table[$mode][$from_lang];
	
		// Detect word's case
		$case = 0;	// abcd
		$t = mb_substr($text, 0, 1);
		if($t != mb_strtolower($t)){
			$case = 1;	// Abcd
			$t = mb_substr($text, 1);
			if($t != mb_strtolower($t))	$case = 2;	// ABCD
		}
	
		// Transcript word
		$text = mb_strtolower($text);
		if(isset($tr[$to_lang.'-special']) && !empty($tr[$to_lang.'-special']))
			$text = preg_replace(array_keys($tr[$to_lang.'-special']), array_values($tr[$to_lang.'-special']), $text);
		if(isset($tr[$to_lang]) && !empty($tr[$to_lang]))
			$text = strtr($text, $tr[$to_lang]);
	
		// Restore initial word's case
		switch($case){
			case 1:	// Abcd
				$text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
				break;
			case 2:	// ABCD
				$text = mb_strtoupper($text);
				break;
			default: // abcd
				break;
		}
	
		mb_internal_encoding($old_enc);
		return $text;
	}

	/**
	 * Add new transcription table.
	 * 
	 * @since	2.2.3
	 * 
	 * @param string $from_lang	Source language code.
	 * @param string $to_lang	Destination language code.
	 * @param int $mode			Convertion mode. {@see GB_Transcriptor}
	 * @param array $tr			Main translation table. {@see strtr}
	 * @param array $tr_special	Additional translation table for special ocassions.
	 * 							{@see preg_replace} 
	 * @return boolean	Always true.
	 */
	static function add_transcription($from_lang, $to_lang, $mode, $tr, $tr_special = null){
		$from_lang = strtolower($from_lang);
		$to_lang = strtolower($to_lang);

		if( !isset(self::$trans_table) || !is_array(self::$trans_table) )
			self::$trans_table = array();
		if( !isset(self::$trans_table[$mode]) || !is_array(self::$trans_table[$mode]) )
			self::$trans_table[$mode] = array();
		if( !isset(self::$trans_table[$mode][$from_lang]) || !is_array(self::$trans_table[$mode][$from_lang]) )
			self::$trans_table[$mode][$from_lang] = array();
	
		if( !empty($tr_special) )
			self::$trans_table[$mode][$from_lang][$to_lang.'-special'] = $tr_special;
	
		self::$trans_table[$mode][$from_lang][$to_lang] = $tr;
		
		return true;
	}

	/**
	 * Check whether there is a transcription table for given language pair and mode.
	 * 
	 * @since	2.2.3
	 * 
	 * @param string $from_lang	Source language code.
	 * @param string $to_lang	Destination language code. Default current locale language.
	 * @param int $mode			Convertion mode. {@see GB_Transcriptor} Default self::TRANSCRIBE
	 * @return boolean	True if transcription table have. False otherwise.
	 */
	static function has_transcription($from_lang, $to_lang = null, $mode = self::TRANSCRIBE){
		$from_lang = strtolower($from_lang);
		$to_lang = strtolower($to_lang);

		if( null === $to_lang )
			$to_lang = get_locale();

		if( !isset(self::$trans_table) || !is_array(self::$trans_table)
				|| !isset(self::$trans_table[$mode]) || !is_array(self::$trans_table[$mode]))
			return false;
		if( !isset(self::$trans_table[$mode][$from_lang]) || !is_array(self::$trans_table[$mode][$from_lang]) ){
			$from_lang = strtok($from_lang, '_');
			if( !isset(self::$trans_table[$mode][$from_lang]) || !is_array(self::$trans_table[$mode][$from_lang]) )
				return false;
		}
		if( !isset(self::$trans_table[$mode][$from_lang][$to_lang]) || !is_array(self::$trans_table[$mode][$from_lang][$to_lang]) ){
			$to_lang = strtok($to_lang, '_');
			if( !isset(self::$trans_table[$mode][$from_lang][$to_lang]) || !is_array(self::$trans_table[$mode][$from_lang][$to_lang]) )
				return false;
		}

		return true;
	}
}

function gb_transcriptor_init(){
	// Русско-английская транскрипция (произношение) и транслитерация (написание)
	$tr_special = array(
			'/\bе/uS' => 'ye',		'/ий\b/uS' => 'iy',	'/ой\b/uS' => 'oy',
			'/ее\b/uS' => 'eye',	'/ое\b/uS' => 'oye',	'/ая\b/uS' => 'aya',
			'/яя\b/uS' => 'yaya',	'/ия\b/uS' => 'iya',	'/ие\b/uS' => 'iye',
			'/ые\b/uS' => 'yye',
			'/\bсергей\b/uS' => 'sergey',		'/\bюрий\b/uS' => 'yuri',
	);
	$tr = array(
			'а' => 'a',			'ай' => 'ai',		'б' => 'b',			'в' => 'v',
			'г' => 'g',			'д' => 'd',			'е' => 'e',			'ё' => 'yo',
			'ей' => 'ei',		'ёй' => 'yoi',		'ж' => 'zh',		'жё' => 'zho',
			'же' => 'zhe',		'жёй' => 'zhoi',	'з' => 'z',			'и' => 'i',
			'ий' => 'ii',		'й' => 'y',			'к' => 'k',			'л' => 'l',
			'м' => 'm',			'н' => 'n',			'о' => 'o',			'ой' => 'oi',
			'п' => 'p',			'р' => 'r',			'с' => 's',			'т' => 't',
			'у' => 'u',			'уй' => 'ui',		'ф' => 'f',			'х' => 'kh',
			'ц' => 'ts',		'ч' => 'ch',		'чё' => 'cho',		'че' => 'che',
			'чёй' => 'choi',	'ш' => 'sh',		'шё' => 'sho',		'ше' => 'she',
			'шёй' => 'shoi',	'щ' => 'shch',		'щё' => 'shcho',	'ще' => 'shche',
			'щёй' => 'shchoi',	'ъ' => '',			'ъе' => 'ye',		'ъё' => 'yo',
			'ъи' => 'yi',		'ъо' => 'yo',		'ъо' => 'yo',		'ъю' => 'yu',
			'ъя' => 'ya',		'ы' => 'y',			'ый' => 'yi',		'ь' => '',
			'ье' => 'ye',		'ьё' => 'yo',		'ьи' => 'yi',		'ьо' => 'yo',
			'ьо' => 'yo',		'ью' => 'yu',		'ья' => 'ya',		'э' => 'e',
			'эй' => 'ei',		'ю' => 'yu',		'юй' => 'yui',		'я' => 'ya',
			'яй' => 'yai',
	);
	GB_Transcriptor::add_transcription('ru', 'en', GB_Transcriptor::TRANSLITERATE, $tr, $tr_special);
	GB_Transcriptor::add_transcription('ru', 'en', GB_Transcriptor::TRANSCRIBE, $tr, $tr_special);

	// Русско-немецкая транскрипция (произношение)
	$tr = array(
			'а' => 'a',		'б' => 'b',		'в' => 'w',	'г' => 'g',	'д' => 'd',		'е' => 'je',
			'ё' => 'jo',	'ж' => 'sh',	'з' => 's',	'и' => 'i',	'й' => 'j',		'к' => 'k',
			'л' => 'l',		'м' => 'm',		'н' => 'n',	'о' => 'o',	'п' => 'p',		'р' => 'r',
			'с' => 's',		'т' => 't',		'у' => 'u',	'ф' => 'f',	'х' => 'ch',	'ц' => 'z',
			'ч' => 'tsch',	'ш' => 'sch',	'щ' => 'schtsch',	'ь' => '\'',	'ы' => 'y',
			'ъ' => '\'',	'э' => 'e',		'ю' => 'yu',	'я' => 'ja',
	);
	GB_Transcriptor::add_transcription('ru', 'de', GB_Transcriptor::TRANSCRIBE, $tr);

	// Русско-немецкая транслитерация (написание)
	$tr_special = array(
			'/ой/uS'	=> 'äu',		'/кк/uS'	=> 'ck',		'/кр/uS'	=> 'chr',
			'/ай/uS'	=> 'ei',		'/кв/uS'	=> 'qu',		'/шп/uS'	=> 'sp',
			'/шт/uS'	=> 'st',
	);
	$tr = array(
			'а' => 'a',		'б' => 'b',		'в' => 'v',	'г' => 'g',	'д' => 'd',		'е' => 'ä',
			'ё' => 'ö',		'ж' => 'zh',	'з' => 's',	'и' => 'i',	'й' => 'j',		'к' => 'k',
			'л' => 'l',		'м' => 'm',		'н' => 'n',	'о' => 'o',	'п' => 'p',		'р' => 'r',
			'с' => 's',		'т' => 't',		'у' => 'u',	'ф' => 'f',	'х' => 'ch',	'ц' => 'z',
			'ч' => 'ĉ',		'ш' => 'ŝ',		'щ' => 'ŝĉ',	'ь' => '\'',	'ы' => 'y',
			'ъ' => '\'',	'э' => 'ä',		'ю' => 'ü',	'я' => 'ja',
			'готфрид' => 'gottfried',		'вильгельм' => 'wilhelm',	'иоган' => 'johann',
			'август' => 'august',			'готлиб' => 'gottlieb',		'людвиг' => 'ludwig',
	);
	GB_Transcriptor::add_transcription('ru', 'de', GB_Transcriptor::TRANSLITERATE, $tr);

	// Русско-польская транскрипция (произношение)
	$tr_special = array(
			'/(?<=\b|[аоэиуыеёюяъь])е/uS' => 'jo',	'/(?<=[жцчшщ])е/uS' => 'o',
			'/(?<=\b|[аоэиуыеёюяъь])ё/uS' => 'je',	'/(?<=[жцчшщ])ё/uS' => 'e',
			'/(?<=[ь])и/uS' => 'ji',				'/(?<=[жцш])и/uS' => 'y',
			'/л(?=[иья])/uS' => 'l',
			'/(?<=\b|[аоэиуыеёюяъь])ю/uS' => 'ju',
			'/(?<=\b|[аоэиуыеёюяъь])я/uS' => 'ja',
	);
	$tr = array(
			'а' => 'a',		'б' => 'b',		'в' => 'w',		'г' => 'g',		'д' => 'd',
			'е' => 'ie',	'ё' => 'io',	'ж' => 'ż',		'з' => 'z',		'и' => 'i',
			'й' => 'j',		'к' => 'k',		'л' => 'ł',		'ле' => 'lo',	'лё' => 'le',
			'лю' => 'lu',	'ля' => 'la',	'м' => 'm',		'н' => 'n',		'о' => 'o',
			'п' => 'p',		'р' => 'r',		'с' => 's',		'т' => 't',		'у' => 'u',
			'ф' => 'f',		'х' => 'ch',	'ц' => 'c',		'ч' => 'cz',	'ш' => 'sz',
			'щ' => 'szcz',	'ъ' => '',		'ы' => 'y',		'ь' => '\'',	'э' => 'e',
			'ю' => 'iu',	'я' => 'ia',
	);
	GB_Transcriptor::add_transcription('ru', 'pl', GB_Transcriptor::TRANSCRIBE, $tr);

	// Русско-польская транслитерация (написание)
	$tr_special = array(
			'/ий\b/uS' => 'i',			'/ый\b/uS' => 'y',			'/ой\b/uS' => 'oj',
			'/ая\b/uS' => 'a',
	);
	$tr = array(
			'а' => 'a',	'б' => 'b',	'в' => 'v',	'г' => 'g',	'д' => 'd',	'е' => 'e',	'ё' => 'ё',
			'ж' => 'ž',	'з' => 'z',	'и' => 'i',	'й' => 'j',	'к' => 'k',	'л' => 'l',	'м' => 'm',
			'н' => 'n',	'о' => 'o',	'п' => 'p',	'р' => 'r',	'с' => 's',	'т' => 't',	'у' => 'u',
			'ф' => 'f',	'х' => 'h',	'ц' => 'c',	'ч' => 'č',	'ш' => 'š',	'щ' => 'ŝ',	'ъ' => '″',
			'ы' => 'y',	'ь' => '′',	'э' => 'è',	'ю' => 'û',	'я' => 'â',
	);
	GB_Transcriptor::add_transcription('ru', 'pl', GB_Transcriptor::TRANSLITERATE, $tr, $tr_special);

	// Русско-финская транскрипция (произношение) и транслитерация (написание)
	$tr_special = array(
			'/(?<=\b|[аоэиуыеёюяъь])е/uS' => 'je',		'/(?<=[жчшщ])ё/uS' => 'o',
			'/(?<=ь)и/uS' => 'ji',
			'/ий\b/uS' => 'i',		'/(?<=\b|[и])й\B/uS' => 'j',
	);
	$tr = array(
			'а' => 'a',		'б' => 'b',		'в' => 'v',		'г' => 'g',		'д' => 'd',
			'е' => 'e',		'ё' => 'jo',	'ж' => 'ž',		'з' => 'z',		'и' => 'i',
			'й' => 'i',		'к' => 'k',		'л' => 'l',		'м' => 'm',		'н' => 'n',
			'о' => 'o',		'п' => 'p',		'р' => 'r',		'с' => 's',		'т' => 't',
			'у' => 'u',		'ф' => 'f',		'х' => 'h',		'ц' => 'ts',	'ч' => 'tš',
			'ш' => 'š',		'щ' => 'štš',	'ъ' => '',		'ы' => 'y',		'ь' => '',
			'э' => 'e',		'ю' => 'ju',	'я' => 'ja',
			'вейна' => 'wayne',		'вайне' => 'wayne',
	);
	GB_Transcriptor::add_transcription('ru', 'fi', GB_Transcriptor::TRANSCRIBE, $tr, $tr_special);
	GB_Transcriptor::add_transcription('ru', 'fi', GB_Transcriptor::TRANSLITERATE, $tr, $tr_special);

	// Польско-русская транскрипция (произношение) и транслитерация (написание)
	// https://ru.wikipedia.org/wiki/%D0%9F%D0%BE%D0%BB%D1%8C%D1%81%D0%BA%D0%BE-%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%B0%D1%8F_%D0%BF%D1%80%D0%B0%D0%BA%D1%82%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B0%D1%8F_%D1%82%D1%80%D0%B0%D0%BD%D1%81%D0%BA%D1%80%D0%B8%D0%BF%D1%86%D0%B8%D1%8F
	$tr_special = array(
			'/\be/uS' => 'э',
			'/\bja/uS' => 'я',
			'/\bje/uS' => 'е',
			'/\bjo/uS' => 'йо',
			'/\bjó/uS' => 'ю',
			'/\bju/uS' => 'ю',
			'//uS' => '',
			'//uS' => '',
			'//uS' => '',
	);
	$tr = array(
			'ąb' => 'омб',
			'ąp' => 'омп',
			'ą' => 'он',
			'b' => 'б',
			'c' => 'ц',
			'ć' => 'ць',
			'ch' => 'х',
			'cz' => 'ч',
			'd' => 'д',
			'dż' => 'дж',
			'dz' => 'дз',
			'e' => 'е',
			'ęb' => 'емб',
			'ęp' => 'емп',
			'ę' => 'ен',
			'f' => 'ф',
			'g' => 'г',
			'h' => 'х',
			'ia' => 'я',
			'ie' => 'е',
			'io' => 'ё',
			'ió' => 'ю',
			'iu' => 'ю',
			'k' => 'к',
			'ł' => 'л',
			'm' => 'м',
			'n' => 'н',
			'ń' => 'нь',
			'p' => 'п',
			'r' => 'р',
			's' => 'с',
			'sz' => 'ш',
			'szcz' => 'щ',
			't' => 'т',
			'w' => 'в',
			'ż' => 'ж',
			'z' => 'з',
			'' => '',
	);
	GB_Transcriptor::add_transcription('pl', 'ru', GB_Transcriptor::TRANSCRIBE, $tr, $tr_special);
	GB_Transcriptor::add_transcription('pl', 'ru', GB_Transcriptor::TRANSLITERATE, $tr, $tr_special);
}

// Initialize transcriptor
if( class_exists('GB_Hooks') )	GB_Hooks::add_action('init', 'gb_transcriptor_init');
else	gb_transcriptor_init();
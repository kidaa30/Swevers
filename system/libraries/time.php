<?

define('RELATIVE_TIME_ACCURACY_SECOND',1);
define('RELATIVE_TIME_ACCURACY_DAY',2);

function relative_time($time,$accuracy=RELATIVE_TIME_ACCURACY_SECOND){
	
	$date = new DateTime();
	
	if (!is_numeric($time)) $time = strtotime($time);
	
	$future = (time() < $time);

	$name = "";
	
	if ($accuracy == RELATIVE_TIME_ACCURACY_DAY) {
		
		$time = strtotime(date('Y-m-d',$time).' 00:00:00');
		$today = mktime(0,0,0);
		$week = strtotime('last monday', strtotime('tomorrow'));
		$month = mktime(0, 0, 0, date("m"), 1, date("Y"));
		
		if ($today == $time) return 'Vandaag';
		else if (strtotime('-1 day',$today) == $time) return 'Gisteren';
		else if (strtotime('-2 days',$today) == $time) return 'Eergisteren';
		else if (strtotime('+1 day',$today) == $time) return 'Morgen';
		else if (strtotime('+2 days',$today) == $time) return 'Overmorgen';
		else if ($time < strtotime('+1 week',$week) && $time >= $week) return strftime('%A',$time);
		else if ($time < strtotime('+2 weeks',$week) && $time >= strtotime('+1 week',$week)) return 'Volgende week';
		else if ($time < $week && $time >= strtotime('-1 week',$week)) return 'Vorige week';
		else if ($time < $month && $time >= strtotime('-1 month',$month)) return 'Vorige maand';
		else if ($time < strtotime('+2 months',$month) && $time >= strtotime('+1 month',$month)) return strftime('%B',$time);
		else if ($time < strtotime('-1 month',$month) && $time >= strtotime('-2 months',$month)) return strftime('%B',$time);
		else return relativeTime($time);
		
	} else {
	
		$time = abs(time() - $time);
	
		$divisions	= array(1,60,60,24,7,4.34,12);
		
		$names		= array('nl'=>array('seconde','minuut','uur','dag','week','maand','jaar'),'fr'=>array('seconde','minute','heure','jour','semaine','mois','an'),'en'=>array('second','minute','hour','day','week','month','year'),'de'=>array('Sekunde','Minute','Stunde','Tag','Woche','Monat','Jahr'));
		$namesp		= array('nl'=>array('seconden','minuten','uur','dagen','weken','maanden','jaren'),'fr'=>array('secondes','minutes','heures','jours','semaines','mois','ans'),'en'=>array('seconds','minutes','hours','days','weeks','months','years'),'de'=>array('Sekunden','Minuten','Stunden','Tage','Wochen','Monate','Jahre'));
	
		if ($time < 30 && !$future){
			return l(array('nl'=>'zojuist','fr'=>'tout &agrave; l\'heure','en'=>'just now','de'=>'vorhin'));
		}
	
		for($i=0; $i<count($divisions); $i++){
			if($time < $divisions[$i]) break;
	
			$time = $time/$divisions[$i];
			
			if ($future && $names['nl'][$i] == 'week') $time += 1;
			
			if(round($time) == 1) $name = $names[language()][$i];
			else $name = $namesp[language()][$i];
		}
	
		$time = round($time);
		
		if ($future) return l(array('nl'=>"binnen $time $name",'fr'=>"dans $time $name",'en'=>"in $time $name",'de'=>"in $time $name"));
		else return l(array('nl'=>"$time $name geleden",'fr'=>"il y a $time $name",'en'=>"$time $name ago",'de'=>"vor $time $name"));
		
	}
}
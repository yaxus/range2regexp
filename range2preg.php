<?php

	$ci;            // ChangeIndex
	$rp;            // root pattern
	$sp  = array(); // start patterns
	$fp  = array(); // finish patterns
	$err = array(); // errors

	$ca = count($argv);
	if ($ca == 2 AND preg_match("/^(\d+)\-(\d+)$/", $argv[1], $mch))
		list ($sn, $fn) = array($mch[1], $mch[2]);
	elseif ($ca == 3 AND preg_match("/^\d+$/", $argv[1]) AND preg_match("/^\d+$/", $argv[2]))
		list ($sn, $fn) = array($argv[1], $argv[2]);
	else
		$err[] = 'Format error! Example: XXXXYYY-XXXXZZZ or XXXXYYY XXXXZZZ';

	$numlen = strlen($sn);
	$li = $numlen - 1; // last index
	if ($numlen !== strlen($fn))
		$err[] = 'Numbers should be equal length!';

	if ( ! empty($err))
	{
		echo implode("\n", $err);
		return FALSE;
	}

	// Найти ChangeIndex, поочередно сравнивая каждый символ
	for ($ci=0; $ci<$numlen; $ci++)
	{
		if ($sn[$ci] == $fn[$ci])
			continue;
		// Если StartNumber больше FinishNumber, меняем их местами
		if ($sn[$ci] > $fn[$ci])
			list($sn, $fn) = array($fn, $sn);
		break;
	}

	/******************************************************
	 *                  start patterns                    *
	 ******************************************************/
	// Удаление нулей в конце
	$clr0 = strlen(rtrim($sn, '0'))-1;
	$bsi = ($clr0 > $ci) ? $clr0 : $ci;
	// Если BeginStartIndex не равен ChangeIndex необходимо
	// создать шаблоны по индексам большим, чем ChangeIndex
	if ($bsi != $ci)
	{
		$sp[] = ($sn[$bsi] < 9) ? rng($sn, $bsi, $sn[$bsi], 9) : $sn;
		for ($j=$bsi-1; $j>$ci; $j--)
			if ($sn[$j] != 9)
				$sp[] = ($sn[$j] < 8) ? rng($sn, $j, $sn[$j]+1, 9) : rng($sn, $j, 9);
	}

	/******************************************************
	 *                  finish patterns                   *
	 ******************************************************/
	// Удаление девяток в конце
	$clr9 = strlen(rtrim($fn, '9'))-1;
	$bfi = ($clr9 > $ci) ? $clr9 : $ci;
	// FinishIndex != ChangeIndex
	if ($bfi != $ci)
	{
		$fp[] = ($fn[$bfi] > 0) ? rng($fn, $bfi, 0, $fn[$bfi]) : $fn;
		for ($j=$bfi-1; $j>$ci; $j--)
			if ($fn[$j] != 0)
				$fp[] = ($fn[$j] > 1) ? rng($fn, $j, 0, $fn[$j]-1) : rng($fn, $j, 0);
	}
		
	/******************************************************
	 *                   root pattern                     *
	 ******************************************************/
	$sri = ( ! empty($sp)) ? $sn[$ci]+1 : $sn[$ci];
	$fri = ( ! empty($fp)) ? $fn[$ci]-1 : $fn[$ci];
	$diff_ri = $fri - $sri;
	if ($diff_ri == 9)
		$rp = rng($sn, $ci);
	elseif ($diff_ri > 0)
		$rp = rng($sn, $ci, $sri, $fri);
	elseif ($diff_ri == 0)
		$rp = rng($sn, $ci, $sri);



	function rng($num, $pref_length, $s = NULL, $f = NULL)
	{
		$pref = substr($num, 0, $pref_length);
		if ($s === NULL)
			return $pref;
		elseif ($f === NULL)
			return $pref.$s;
		elseif ($f - $s == 1)
			return $pref.'['.$s.$f.']';
		return $pref.'['.$s.'-'.$f.']';

	}


	/******************************************************
	 *                       output                       *
	 ******************************************************/
	rsort($sp);
	$cnt_d = 40;
	$msg   = ' regexp for copy ';
	$ret[] = str_pad($msg, $cnt_d, '-', STR_PAD_BOTH);
	$ret[] = $rp;
	$ret   = array_merge($ret, $sp, $fp);
	$ret[] = str_repeat('-', $cnt_d);

	echo implode("\n", $ret);


?>
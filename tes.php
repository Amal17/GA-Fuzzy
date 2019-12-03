<?php
	$arr = array(1,2,3,4,5,6);
	$max = max($arr);
	$index = array_search($max, $arr);
	print_r($index);
?>
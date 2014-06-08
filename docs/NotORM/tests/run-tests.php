<?php
$start = microtime(true);
/*
$tests = glob(dirname(__FILE__) . "/*.phpt", GLOB_NOSORT);print_r($tests);echo '<br>';
natsort($tests);
foreach ($tests as $filename) {
	ob_start();
	include $filename;
	if (!preg_match("~^--TEST--\n(.*?)\n(?:--SKIPIF--\n(.*\n)?)?--FILE--\n(.*\n)?--EXPECTF--\n(.*)~s", str_replace("\r\n", "\n", ob_get_clean()), $match)) {
		echo "wrong test in $filename\n";
	} elseif ($match[2]) {
		echo "skipped $filename ($match[1]): $match[2]";
	} elseif ($match[3] !== $match[4]) {
		echo "failed $filename ($match[1])\n";
	}
}
*/
include_once dirname(__FILE__) . "/connect.inc.php";
print_r($software->application());
foreach ($software->application() as $application) {
	echo "$application[title] (" . $application->author["name"] . ")\n<br>";
	foreach ($application->application_tag() as $application_tag) {
		echo "\t" . $application_tag->tag["name"] . "\n<br>";
	}
}

printf("%.3F s, %d KiB\n", microtime(true) - $start, memory_get_peak_usage() / 1024);

#! /usr/bin/env php
<?php
$COLUMNS = 160;
$LINES = 90;

if (function_exists('readline_completion_function')) {
	readline_completion_function(function($s) {
		file_put_contents('/tmp/readline.txt', $s . "\n" . $b . "\n" . print_r(readline_info(), 1));
		return true;
	});
}

if (empty($argv[1])) {
	echo 'input a image file', "\n";
	exit;
}
$image = $argv[1];

$file = tempnam(sys_get_temp_dir(), 'thumb_');
$bmpFile = $file . '.bmp';

if (preg_match('#^https?://#', $image)) {

	$tmp = file_get_contents($image);

	if (!$tmp) {
		echo 'download file ' . $image . ' fail';
		unlink($file);
		exit;
	}

	file_put_contents($file, $tmp);
	unset($tmp);
	$image = $file;

} else if (!file_exists($image)) {

	echo 'not a file: ', $image, "\n";
	exit;
}

$truecolor = true;
$fill = true;

$pixel = $truecolor ? 3 : 4;

if (!$COLUMNS) {
	$COLUMNS = max($_ENV['COLUMNS'], 5);
}
if (!$LINES) {
	$LINES = max($_ENV['LINES'], 5);
}

$resize = $COLUMNS . 'x' . $LINES;

$CMD = 'convert ' . escapeshellarg($image . '[0]')
	. ($truecolor ? ' -type truecolor' : '')
	. ($fill ? '' : ' -resize 100%x50%')
	. ' -resize ' . $resize . ' bmp:- > ' . escapeshellarg($bmpFile)
	. ' 2>&1';

function getcolor($x, $y, $bg = true) {
	global $bmp, $offset, $w, $padding, $filesize, $pixel;

	if (!$bg) {
		$y = $y - 1;
	}

	$start = $offset + $y * ($w * $pixel + $padding) + $x * $pixel;

	if (($start + 2) >= $filesize) {
		return false;
	}

	$r = bin2dec($bmp[$start + 2]);
	$g = bin2dec($bmp[$start + 1]);
	$b = bin2dec($bmp[$start + 0]);

	return sprintf("%d;2;%d;%d;%d", $bg ? 48: 38, $r, $g, $b);
}

function bin2dec($s) {
	return hexdec(bin2hex($s));
}

function getnum($addr, $len = 4) {
	global $bmp;
	$k = range($addr + $len - 1, $addr, -1);
	$return = '';
	foreach ($k as $i) {
		echo $i, "\n";
		$return .= $bmp[$i];
	}

	echo "\n";
	echo bin2hex($return);
	echo "\n";
	return bin2dec($return);
}

// /www/flash/qa/img/thumbs/paybg

// echo $CMD, "\n";
$result = shell_exec($CMD);

if (!file_exists($bmpFile)) {
	echo 'convert fail', "\n";
	echo 'cmd: ', $CMD, "\n";
	echo 'result: ', $result, "\n";
	exit;
}

// $bmp = file_get_contents($bmpFile);
$bmp = file_get_contents('/home/zhengkai/conf/script/google-logo');
$filesize = strlen($bmp);

unlink($bmpFile);
unlink($file);

// echo bin2hex($bmp), "\n";

// $offset = $bmp[13] . $bmp[12] . $bmp[11] . $bmp[10];

$offset = getnum(0x0a);

echo $offset;
exit;

/*
echo bin2hex($bmp[0x0a]);
echo bin2hex($bmp[0x0a + 1]);
echo bin2hex($bmp[0x0a + 2]);
echo bin2hex($bmp[0x0a + 3]);
echo "\n";
echo 'offset = ', $offset, "\n";
 */

$w = getnum(0x0012);
$h = getnum(0x0016);

$bit = ($w * $pixel) % 4;
$padding = 0;
if ($bit) {
	$padding = 4 - $bit;
}

/*
echo 'size = ', getnum(0x000e), "\n";
echo 'width = ', $w, "\n";
echo 'height = ', $h, "\n";
echo 'padding = ', $padding, "\n";
echo 'depth = ', getnum(0x001c, 2), "\n";
echo 'compress = ', getnum(0x001e), "\n";
exit;
 */

$prev_fg_color = '';
$prev_bg_color = '';

// echo "\n";

foreach (range($h, 1, $fill ? -2 : -1) as $y) {
	foreach (range(0, $w - 1) as $x) {

		// echo $r, ' ', $g, ' ', $b, "\n";

		$bg_color = getcolor($x, $y) ?: '';
		if ($bg_color) {
			if ($prev_bg_color === $bg_color) {
				$bg_color = '';
			} else {
				$prev_bg_color = $bg_color;
			}
		}

		$fg_color = '';
		if ($fill) {
			$fg_color = getcolor($x, $y, false);
			if (!$fg_color) {
				continue;
			}
			if ($prev_fg_color === $fg_color) {
				$fg_color = '';
			} else {
				$prev_fg_color = $fg_color;
			}
		}

		if ($fg_color || $bg_color) {
			if ($fg_color && $bg_color) {
				$fg_color .= ';';
			}
			echo sprintf("\033[%s%sm", $fg_color, $bg_color);
			// echo sprintf("\\033[%s%sm", $fg_color, $bg_color);
		}
		if ($fill) {
			echo '▄'; // https://www.compart.com/en/unicode/U+2584
		} else {
			echo ' ';
		}
	}
	$prev_fg_color = '';
	$prev_bg_color = '';
	echo "\033[0m\n";
}

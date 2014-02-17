<?php
function strToLink($link, $text = "", $newtab = false) {
	if (trim($text) == "") {
		$text = $link;
	}
	$buildLink = '<a href="' . $link . '" style="color: #B34251;"';
	if ($newtab === true) {
		$buildLink .= ' target="_blank"';
	}
	$buildLink .= '>' . $text . '</a>';
	return $buildLink;
}

function redirectTo($link) {
	return '<meta http-equiv="refresh" content="0; url=' . $link . '" />';
}

function rrmdir($dir) { 
  foreach(glob($dir . '/*') as $file) { 
    if(is_dir($file)) rrmdir($file); else unlink($file); 
  } rmdir($dir); 
}

function octalToFull($octalPerms) {
	$perms = $octalPerms;
	if (($perms & 0xC000) == 0xC000) {
		$info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		$info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		$info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		$info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		$info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		$info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		$info = 'p';
	} else {
		$info = 'u';
	}

	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
				(($perms & 0x0800) ? 's' : 'x' ) :
				(($perms & 0x0800) ? 'S' : '-'));

	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
				(($perms & 0x0400) ? 's' : 'x' ) :
				(($perms & 0x0400) ? 'S' : '-'));

	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
				(($perms & 0x0200) ? 't' : 'x' ) :
				(($perms & 0x0200) ? 'T' : '-'));
				
	return $info;
}

function _is_link($filename) 
{ 
    if(is_link($filename)) 
        return true; 

    $ext = substr(strrchr($filename, '.'), 1); 
    if(strtolower($ext) == 'lnk') 
    { 
        return (_readlink($filename) ? true : false); 
    } 

    return false; 
} 

function _readlink($file) 
{ 
    if(file_exists($file)) 
    { 
        if(is_link($file)) 
        { 
            return readlink($file); 
        } 

        // Get file content 
        $handle = fopen($file, "rb"); 
        $buffer = array(); 

        while(!feof($handle)) 
        { 
            $buffer[] = fread($handle, 1); 
        } 

        fclose($handle); 

        // Test magic value and GUID 
        if(count($buffer) < 20) 
            return false; 
        if($buffer[0] != 'L') 
            return false; 
        if((ord($buffer[4]) != 0x01) || 
           (ord($buffer[5]) != 0x14) || 
           (ord($buffer[6]) != 0x02) || 
           (ord($buffer[7]) != 0x00) || 
           (ord($buffer[8]) != 0x00) || 
           (ord($buffer[9]) != 0x00) || 
           (ord($buffer[10]) != 0x00) || 
           (ord($buffer[11]) != 0x00) || 
           (ord($buffer[12]) != 0xC0) || 
           (ord($buffer[13]) != 0x00) || 
           (ord($buffer[14]) != 0x00) || 
           (ord($buffer[15]) != 0x00) || 
           (ord($buffer[16]) != 0x00) || 
           (ord($buffer[17]) != 0x00) || 
           (ord($buffer[18]) != 0x00) || 
           (ord($buffer[19]) != 0x46)) 
        { 
            return false; 
        } 

        $i = 20; 
        if(count($buffer) < ($i + 4)) 
            return false; 

        $flags = ord($buffer[$i]); 
        $flags = $flags | (ord($buffer[++$i]) << 8); 
        $flags = $flags | (ord($buffer[++$i]) << 16); 
        $flags = $flags | (ord($buffer[++$i]) << 24); 

        $hasShellItemIdList = ($flags & 0x00000001) ? true : false; 
        $pointsToFileOrDir = ($flags & 0x00000002) ? true : false; 

        if(!$pointsToFileOrDir) 
            return false; 

        if($hasShellItemIdList) 
        { 
            $i = 76; 
            if(count($buffer) < ($i + 2)) 
                return false; 

            $a = ord($buffer[$i]); 
            $a = $a | (ord($buffer[++$i]) << 8); 
            
        } 

        $i = 78 + 4 + $a; 
        if(count($buffer) < ($i + 4)) 
            return false; 

        $b = ord($buffer[$i]); 
        $b = $b | (ord($buffer[++$i]) << 8); 
        $b = $b | (ord($buffer[++$i]) << 16); 
        $b = $b | (ord($buffer[++$i]) << 24); 

        $i = 78 + $a + $b; 
        if(count($buffer) < ($i + 4)) 
            return false; 

        $c = ord($buffer[$i]); 
        $c = $c | (ord($buffer[++$i]) << 8); 
        $c = $c | (ord($buffer[++$i]) << 16); 
        $c = $c | (ord($buffer[++$i]) << 24); 

        $i = 78 + $a + $b + $c; 
        if(count($buffer) < ($i +1)) 
            return false; 

        $linkedTarget = ""; 
        for(;$i < count($buffer); ++$i) 
        { 
            if(!ord($buffer[$i])) 
                break; 

            $linkedTarget .= $buffer[$i]; 
        } 

        if(empty($linkedTarget)) 
            return false; 

        
        return $linkedTarget; 
    } 

    return false; 
} 
?>
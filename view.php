<?php
require_once('../lib.php');
require_once(dirname(__FILE__)."/lib/webservice/webservice.class.php");

$ids = explode('/', $_SERVER['REQUEST_URI']);
$len = count($ids);
$repoId = $ids[$len - 4];
$bureauId = $ids[$len - 3];
$itemId = $ids[$len - 2];

$ret = WebService::getItemDetails($repoId, $bureauId, $itemId);
$itemDetails = $ret['itemDetails'];

if ($itemDetails['urlType'] == 'true')
{
  header('Content-Disposition: inline; filename=url.html');
  header('Content-type: text/html');
}
else
{
  header('Content-Disposition: inline; filename='.$itemDetails['filename']);
  header('Content-type: '.$itemDetails['mimeType']);
}

ob_clean();

if ($itemDetails['publicAccess'] == 'true')
{
	$content = WebService::download($repoId, $itemId, true);
}
else
{
	$content = WebService::download($repoId, $itemId);
}

echo $content;

exit;
?>

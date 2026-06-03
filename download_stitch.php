<?php
$url = "https://contribution.usercontent.google.com/download?c=CgthaWRhX2NvZGVmeBJ7Eh1hcHBfY29tcGFuaW9uX2dlbmVyYXRlZF9maWxlcxpaCiVodG1sXzkxY2JlM2JmNjAyOTRiZGViYzU0NWIzMTgxNTJmNzI1EgsSBxCkypygyx0YAZIBIwoKcHJvamVjdF9pZBIVQhM2Nzk5MDQ1ODE2Nzk0OTc4NTE4&filename=&opi=96797242";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$html = curl_exec($ch);
curl_close($ch);
file_put_contents("stitch_roles.html", $html);
echo "Downloaded";
?>

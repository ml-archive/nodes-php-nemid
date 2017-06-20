<?php

require __DIR__.'/vendor/autoload.php';

$config = include __DIR__.'/config/nemid.php';

$config['test'] = true;
$config['login']['testSettings']['privateKeyPassword'] = 'Test1234';
$config['login']['testSettings']['privateKeyLocation'] = __DIR__.'/testcertificates/test_private.pem';
$config['login']['testSettings']['certificateLocation'] = __DIR__.'/testcertificates/test_public.pem';

$login = new \Nodes\NemId\Login\Login($config);
$parameters = [];
foreach (json_decode($login->getParams(), true) as $param => $value) {
    $parameters[] = sprintf('"%s":"%s"', $param, $value);
}
$parameters = implode(',', $parameters);

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NemID - Login</title>
</head>
<body>

<iframe id="nemid_iframe" title="NemID" allowfullscreen="true" scrolling="no" frameborder="0" style="width:320px;height:460px;border:0" src="{$login->getIFrameUrl()}"></iframe>

<form method="post" action="#" id="postBackForm">
    <input type="hidden" name="response" value=""/>
</form>
    
<script>
    function onNemIDMessage(e) {
        var event = e || event;

        var win = document.getElementById("nemid_iframe").contentWindow, postMessage = {}, message;
        message = JSON.parse(event.data);
        console.log(message);

        if (message.command === "SendParameters") {
            postMessage.command = "parameters";
            postMessage.content = '{{$parameters}}';
            win.postMessage(JSON.stringify(postMessage), "{$login->getBaseUrl()}");
        }

        if (message.command === "changeResponseAndSubmit") {
            document.getElementById('postBackForm').response.value = message.content;
            //document.postBackForm.submit();
        }
    }

    if (window.addEventListener) {
        window.addEventListener("message", onNemIDMessage);
    } else if (window.attachEvent) {
        window.attachEvent("onmessage", onNemIDMessage);
    }
</script>
</body>
</html>
HTML;

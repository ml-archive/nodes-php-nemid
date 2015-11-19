<script type="text/x-nemid" id="nemid_parameters">
    {{--Convert json-string to json-object--}}
    {
        @foreach(json_decode($nemIdLogin->getParams(), true) as $key => $value)
            "{{$key}}":"{{$value}}",
        @endforeach
    }
</script>
<script>
    function onNemIDMessage(e) {
        var event = e || event;

        var win = document.getElementById("nemid_iframe").contentWindow, postMessage = {}, message;
        message = JSON.parse(event.data);

        if (message.command === "SendParameters") {
            var htmlParameters = document.getElementById("nemid_parameters").innerHTML;
            postMessage.command = "parameters";
            postMessage.content = htmlParameters;
            win.postMessage(JSON.stringify(postMessage), "{{$nemIdLogin->getBaseUrl()}}");
        }

        if (message.command === "changeResponseAndSubmit") {
            document.postBackForm.response.value = message.content;
            document.postBackForm.submit();
        }
    }

    if (window.addEventListener) {
        window.addEventListener("message", onNemIDMessage);
    } else if (window.attachEvent) {
        window.attachEvent("onmessage", onNemIDMessage);
    }
</script>

@if(! isset($_SESSION['nemid_login']['errors']))
    <iframe id="nemid_iframe" title="NemID" allowfullscreen="true" scrolling="no" frameborder="0" style="width:500px;height:450px;border:0" src="{{$nemIdLogin->getIFrameUrl()}}"></iframe>
    {!! Form::open(['method' => 'post', 'route' => 'nemid.callback', 'name' => 'postBackForm']) !!}
    <input type="hidden" name="response" value=""/>
@else
    There was a problem the NemID client
@endif
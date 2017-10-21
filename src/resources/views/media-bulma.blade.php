<!DOCTYPE html>
<html>
<head>
    <title>Media Manager</title>
    {{-- FW --}}
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- bulma --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.0/css/bulma.min.css">
</head>
<body>
    <div id="app" v-cloak>

        {{-- notifications --}}
        <div class="notif-container">
            <my-notification></my-notification>
        </div>

        <div class="container is-fluid is-marginless">
            <div class="columns">
                {{-- media manager --}}
                <div class="column">
                    @include('MediaManager::_partial')
                </div>
            </div>
        </div>

    </div>

    {{-- footer --}}
    <script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="{{ asset("path/to/app.js") }}"></script>
</body>
</html>

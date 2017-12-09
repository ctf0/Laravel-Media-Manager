<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Media Manager</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
</head>
<body>
    <section id="app" v-cloak>
        {{-- notifications --}}
        <div class="notif-container">
            <my-notification></my-notification>
        </div>

        <div class="container is-fluid is-marginless">
            <div class="columns">
                {{-- media manager --}}
                <div class="column">
                    @include('MediaManager::_manager')
                </div>
            </div>
        </div>
    </section>

    {{-- footer --}}
    <script src="{{ asset("path/to/app.js") }}"></script>
</body>
</html>

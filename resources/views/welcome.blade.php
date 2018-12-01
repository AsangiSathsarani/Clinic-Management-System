<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>window.Laravel = { csrfToken: '{{ csrf_token() }}'}</script>

        <title>Laravel</title>

    </head>
    <body>
        <div id="app">
            <example-component></example-component>
            <clock></clock>
        </div>

        <script type="text/javascript" src="/js/app.js"></script>
    </body>
</html>

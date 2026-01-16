<!DOCTYPE html>
<html lang="pt_br" @yield('html_attribute')>
<head>
    @include('layouts.partials/title-meta')

    @include('layouts.partials/head-css')
</head>
<body>
    @yield('content')

    @include('layouts.partials/customizer')
</body>
</html>
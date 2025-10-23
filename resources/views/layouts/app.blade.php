<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="frame-src 'self'">
    <link rel="icon" href="{{ Vite::asset('resources/images/favicon.png') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Styles -->


    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @vite('resources/css/fontawesome-all.min.css')
    @yield('css')


    {{-- plugins --}}
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    {{-- CKEDITOR --}}
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>

    @livewireStyles
    <wireui:scripts />
</head>

<body class="font-inter antialiased bg-slate-200 text-slate-600" :class="{ 'sidebar-expanded': sidebarExpanded }"
    x-data="{ page: '@yield('ruta')', @yield('panel') sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true', profileSidebarOpen: false }" x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))">


    <script>
        if (localStorage.getItem('sidebar-expanded') == 'true') {
            document.querySelector('body').classList.add('sidebar-expanded');
        } else {
            document.querySelector('body').classList.remove('sidebar-expanded');
        }
    </script>
    <script>
        if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
            document.querySelector('html').classList.remove('dark');
            document.querySelector('html').style.colorScheme = 'light';
        } else {
            document.querySelector('html').classList.add('dark');
            document.querySelector('html').style.colorScheme = 'dark';
        }
    </script>
    <!-- Page wrapper -->
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->

        <x-app.sidebar :variant="'v2'" />

        <!-- Content area -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden bg-slate-50 dark:bg-gray-800">

            <!-- Site header -->
            {{-- @livewire('admin.header', ['page' => request()->fullUrl()]) --}}
            <x-app.header :variant="'v3'" />

            <x-banner />

            <main>


                @yield('contenido')

            </main>

        </div>

    </div>

    @stack('modals')

    @livewireScripts


</body>

<script>
    document.addEventListener('livewire:navigated', () => {
        //+65initFlowbite();
    })
    document.addEventListener('livewire:initialized', () => {
        //success
        //question
        //info
        //warning
        //error
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });


        Livewire.on('notify-toast', (event) => {
            Toast.fire({
                icon: event.icon,
                title: `<div  style="font-size: 15px; color: #052c52;"> ` +
                    event.title + `</div`,
                html: `<div  style="font-size: 14px; color: #056b85;"> ` +
                    event.mensaje + `</div`,
                showCloseButton: true,
                timer: event.timer ? event.timer : 3000,
            });

        });
    });
</script>

@yield('js')

@stack('scripts')

</html>

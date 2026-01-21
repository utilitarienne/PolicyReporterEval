<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] p-6 lg:p-8 text-gray-800">
        <div class="w-lg mx-auto mt-6">
            <h1 class="mb-1 p-2 text-3xl"><strong>Mod-Three Finite State Machine</strong></h1>
            <form>
                <div  class="flex flex-row justify-start items-start">
                    <label class="block p-2">Enter a number in binary
                        <input 
                            class="block border border-gray-400 w-3xs p-1" 
                            name="binaryInput" 
                            required="required" 
                            type="text" 
                            maxlength="50"
                            placeholder="" />
                    </label>
                    
                    <div class="p-2">
                        <span class="block">Remainder</span>
                        <span id="remainder" class="block border border-gray-400 bg-amber-100 w-[6rem] p-1">--</span>
                    </div>
                </div>
                <button type="button" class="block border border-blue-400 bg-blue-100 text-blue-900 hover:bg-blue-800 hover:text-blue-200 m-2 p-2 font-bold"
                    hx-post="/modthree"
                    hx-trigger="click"
                    hx-target="#remainder"
                    hx-swap="value">
                    Run Mod-Three
                </button>

            </form>
        </div>
    </body>
</html>

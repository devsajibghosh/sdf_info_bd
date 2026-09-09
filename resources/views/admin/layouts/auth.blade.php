<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="./img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/bootstrap.min.css') }}  ">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/jquery.jgrowl.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/style.css') }}">

    <title>{{ isset($title) ? $title : '' }}</title>

    <style>
        body {
            /*background-image: url("https://images.unsplash.com/photo-1738567701323-ec2ac3f32404?q=80&w=1316&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D");*/
            display: flex;
            align-items: center;
            justify-content: center;
            background-repeat: no-repeat;
            background-position: cover;
            min-height: 100vh;
            /*overflow: hidden;*/
            background-color: #615F5F ;
            background-blend-mode: multiply;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            backdrop-filter: blur(50px);
            -webkit-backdrop-filter: blur(50px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid;
            border-top-color: red;
            border-left-color: rgb(245, 131, 2);
            border-color: #fff;
            color: #ffffff;
        }

        .card-header h4 {
            font-weight: 600;
            font-size: 1.6rem;
            color: #ffffff;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn {
            background-color: #ffffff;
            color: #333;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }

        x-button:hover {
            background-color: #e0e0e0;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 1.5rem;
            }

            .card-header h4 {
                font-size: 1.3rem;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="h-screen w-100 flex flex-row flex-wrap">
        <div class="bg-gray-100 flex-1 p-6 md:mt-16">
            @include('admin.partials.alerts')
            @yield('content')
        </div>
    </div>
    <!-- end wrapper -->

    <!-- script -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/shared/js/jquery.jgrowl.min.js') }}"></script>
    @stack('scripts')

</body>

</html>

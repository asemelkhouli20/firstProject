
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        /* Dark mode styles */
        body.dark-mode {
            background-color: #222;
            /* Dark background */
            color: #fff;
            /* Light text */
        }

        .card,
        .table {
            background-color: #333;
            /* Darker card and table background */
            color: #fff;
            /* Light text */
        }

        .thead-dark th {
            color: #fff;
            /* Light text for table header */
            background-color: #444;
            /* Darker table header background */
        }

        .btn-primary,
        .btn-info,
        .btn-danger {
            filter: brightness(85%);
            /* Adjust brightness for button colors */
        }

        .custom-control-input:checked~.custom-control-label::before {
            background-color: #007bff;
            /* Adjust toggle switch color */
        }

        .form-control {
            background-color: #333;
            /* Darker form background */
            color: #fff;
            /* Light text */
            border: 1px solid #444;
            /* Darker border */
        }

        .form-control:focus {
            background-color: #444;
            /* Darker background on focus */
            border-color: #555;
            /* Darker border on focus */
            color: #fff;
            /* Light text on focus */
        }

        .btn-primary {
            background-color: #007bff;
            /* Primary button color */
            border-color: #007bff;
            /* Primary button border color */
        }

        .btn-primary:hover {
            background-color: #0056b3;
            /* Darker hover color */
            border-color: #0056b3;
            /* Darker hover border color */
        }

        .invalid-feedback {
            color: #dc3545;
            /* Error message color */
        }
    </style>
</head>

<body class="{{ 'dark-mode' }}">

<div class="container-fluid d-flex align-items-center justify-content-center" style="height: 100vh">
    <div class="row justify-content-center" style="">
        <div class="col-md-8">
            <div class="card" >
                <div class="card-header">Post Details</div>

                <div class="card-body">
                    <p>{{ $post->content }}</p>



                    <div style="display: flex; justify-content: space-between; align-items: center; padding:15px;margin-bottom:20px;border-radius: 10px; background-color: #222">
                        <p style="margin-bottom: 0;"><strong>By:</strong> {{ $post->user->name }}</p>
                        <p style="margin-bottom: 0;"><strong>At:</strong> {{ $post->created_at->format('d-m-Y H:i:s') }}</p>
                    </div>

                    <a href="{{ route('posts.index') }}" class="btn btn-primary">Back to Posts</a>
                </div>
            </div>
        </div>
    </div>
</div>



</body>

</html>

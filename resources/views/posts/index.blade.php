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
    </style>
</head>

<body class="{{ 'dark-mode' }}">

    <div class="container-fluid"> <!-- Use container-fluid for full width -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        Posts
                        <a href="{{ route('posts.create') }}" class="float-right btn btn-success btn-sm">Create New
                            Post</a>

                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <!-- Use table-responsive to enable horizontal scrolling on smaller screens -->
                            <table class="table table-bordered table-striped">
                                <!-- Add table-striped for zebra-striping -->
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Content</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($posts as $post)
                                        <tr>
                                            <td>{{ $post->id }}</td>
                                            <td>{{ $post->content }}</td>
                                            <td>{{ $post->user->name }}</td>
                                            <td>{{ $post->created_at->format('d-m-Y H:i:s') }}</td>
                                            <td style="min-width: 200px;">
                                                <!-- Adjust minimum width of the Actions column as needed -->
                                                <div class="btn-group" role="group" aria-label="Post Actions">
                                                    <a href="{{ route('posts.show', ['post' => $post]) }}"
                                                        class="btn btn-primary btn-sm">View</a>
                                                    <a href="{{ route('posts.edit', ['post' => $post]) }}"
                                                        class="btn btn-info btn-sm">Edit</a>
                                                    <form action="{{ route('posts.destroy', ['post' => $post]) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

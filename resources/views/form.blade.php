<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form Login</title>
</head>
<body>
    @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>

            @endforeach
        </ul>

    @endif

    <form action="/form" method="post">
        @csrf
        <label for="username">Username : @error('username'){{ $message }} @enderror
            <input type="text" name="username" id="username" value="{{ old('username') }}"></label><br>
        <label for="password">Password : @error('password'){{ $message }} @enderror
            <input type="text" name="password" id="password" value="{{ old('password') }}"></label><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>

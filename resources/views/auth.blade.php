<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
        }

        .card {
            border-radius: 15px;
        }

        .input-group-text {
            background-color: #f0f0f0;
            border: none;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #6B73FF;
        }

        .btn-primary {
            background: #6B73FF;
            border: none;
        }

        .btn-primary:hover {
            background: #000DFF;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow p-5" style="width: 100%; max-width: 400px;">
        <h3 class="mb-4 text-center text-dark fw-bold">Login</h3>
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- NIP -->
            <div class="mb-3">
                <label for="nip" class="form-label text-dark">NIP</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip"
                        name="nip" value="{{ old('nip') }}" required autofocus placeholder="Masukkan NIP">
                    @error('nip')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label text-dark">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" required placeholder="Masukkan password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Remember me -->
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-dark" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

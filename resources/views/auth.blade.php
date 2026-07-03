<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Anugerah ASN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/staff-app.css') }}">

    <style>
        body.login-body {
            min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px;
            background: radial-gradient(1200px 600px at 50% -10%, #3562E314, transparent 60%), var(--bg);
        }
        .login-card {
            width: 100%; max-width: 400px; background: #fff; border-radius: 24px; padding: 36px 32px;
            box-shadow: var(--shadow-card);
        }
        .login-header { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-bottom: 26px; }
        .login-logo {
            width: 52px; height: 52px; border-radius: 14px; background: var(--aksen); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800;
        }
        .login-title { font-size: 21px; font-weight: 800; letter-spacing: -0.2px; }
        .login-subtitle { font-size: 13.5px; color: var(--muted); text-align: center; line-height: 1.5; }
        .pw-wrap { position: relative; }
        .pw-toggle {
            position: absolute; right: 6px; top: 5px; width: 36px; height: 36px; border: none; background: transparent;
            border-radius: 10px; cursor: pointer; color: var(--muted2); display: flex; align-items: center; justify-content: center;
        }
        .pw-toggle:hover { background: #F0F3F9; color: var(--ink); }
        .remember-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px; color: var(--muted); user-select: none; }
        .remember-label input { width: 16px; height: 16px; accent-color: var(--aksen); }
        .login-footnote { font-size: 12.5px; color: var(--muted2); text-align: center; }
        .login-copyright { margin-top: 22px; font-size: 12px; color: var(--muted3); }
    </style>
</head>

<body class="staff-app login-body">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">A</div>
            <div class="login-title">Anugerah ASN</div>
            <div class="login-subtitle">Sistem pengelolaan tim &amp; honorarium ASN.<br>Masuk dengan akun kepegawaian Anda.</div>
        </div>

        @if ($errors->has('login'))
            <div class="err-box" style="margin-bottom: 16px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                {{ $errors->first('login') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="flex-col gap-16">
                <div class="flex-col gap-6">
                    <label for="nip" class="field-label">NIP</label>
                    <input id="nip" name="nip" type="text" class="input" value="{{ old('nip') }}" required autofocus
                        placeholder="18 digit NIP">
                    @error('nip')
                        <div class="err-box">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex-col gap-6">
                    <label for="password" class="field-label">Kata Sandi</label>
                    <div class="pw-wrap">
                        <input id="password" name="password" type="password" class="input" style="padding-right: 44px;"
                            required placeholder="Kata sandi Anda">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Tampilkan kata sandi">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="err-box">{{ $message }}</div>
                    @enderror
                </div>

                <label for="remember" class="remember-label">
                    <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ingat saya di perangkat ini
                </label>

                <button type="submit" class="btn btn-primary" style="height: 48px; width: 100%; font-size: 15px;">Masuk</button>
                <div class="login-footnote">Lupa kata sandi? Hubungi admin kepegawaian.</div>
            </div>
        </form>
    </div>
    <div class="login-copyright">© {{ date('Y') }} Anugerah ASN</div>

    <script>
        document.getElementById('pwToggle').addEventListener('click', function () {
            const input = document.getElementById('password');
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
        });
    </script>
</body>

</html>

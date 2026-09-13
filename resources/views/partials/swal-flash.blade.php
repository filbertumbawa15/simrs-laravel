{{--
    Auto-trigger SweetAlert dari session flash message.
    Include sekali di layouts/app.blade.php — tampil otomatis setiap kali ada flash.
    Ganti alert-* Blade tradisional dengan Swal modal.
--}}
@if (session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('success'))
                window.notify.success(@json(session('success')));
            @endif

            @if (session('error'))
                window.notify.error(@json(session('error')));
            @endif

            @if (session('warning'))
                window.notify.warning(@json(session('warning')));
            @endif

            @if (session('info'))
                window.notify.info(@json(session('info')));
            @endif

            @if ($errors->any())
                {{-- Kumpulkan semua error validation jadi 1 modal --}}
                window.Swal.fire({
                    icon: 'error',
                    title: 'Periksa Kembali Input',
                    html: `<ul style="text-align:left; margin:0; padding-left:1.2em;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>`,
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endif

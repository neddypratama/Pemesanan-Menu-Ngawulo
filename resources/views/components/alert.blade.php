@if ($message)
    <div id="custom-alert" class="alert-box {{ $type ?? 'success' }}">
        <span>{{ $message }}</span>
        <button onclick="hideAlert()">×</button>
    </div>

    <script>
        // Sembunyikan alert setelah 5 detik
        setTimeout(() => {
            hideAlert();
        }, 5000);

        function hideAlert() {
            const alertBox = document.getElementById('custom-alert');
            if (alertBox) {
                alertBox.style.display = 'none';
            }
        }
    </script>

    <style>
        .alert-box {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #d1e7dd;
            /* default hijau */
            color: #0f5132;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 250px;
        }

        .alert-box.danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .alert-box button {
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
@endif

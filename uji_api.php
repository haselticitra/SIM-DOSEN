
<?php
require_once __DIR__ . '/includes/auth.php';

if (empty($_SESSION['id_pengguna'])) {
    header('Location: /simdosen/login.php');
    exit;
}

require_once __DIR__ . '/config.php';

try {
    $stmt = $pdo->query(
        'SELECT id_prodi, nama_prodi
         FROM program_studi
         ORDER BY nama_prodi ASC'
    );
    $programStudi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $programStudi = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengujian API SIM Dosen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 650px;
            margin: 30px auto;
            padding: 20px;
            background: #f4f6f9;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 10px;
        }
        label {
            display: block;
            margin-top: 14px;
            font-weight: bold;
        }
        input, select, button {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            margin-top: 6px;
        }
        button {
            background: #1769aa;
            color: white;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        #hasil {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            margin-top: 18px;
        }
        .catatan {
            color: #555;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Pengujian API Tambah Dosen</h2>

    <p class="catatan">
        Gunakan data uji dengan nomor registrasi yang belum dipakai.
    </p>

    <form id="formDosen">
        <label>Nomor Registrasi *</label>
        <input
            type="text"
            name="nomor_registrasi"
            required
        >

        <label>Nama Dosen *</label>
        <input
            type="text"
            name="nama"
            required
        >

        <label>NUPTK</label>
        <input type="text" name="nuptk">

        <label>NIP</label>
        <input type="text" name="nip">

        <label>Program Studi</label>
        <select name="id_prodi">
            <option value="">Pilih program studi</option>
            <?php foreach ($programStudi as $prodi): ?>
                <option value="<?= (int) $prodi['id_prodi'] ?>">
                    <?= htmlspecialchars(
                        $prodi['nama_prodi'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" id="tombol">
            Uji API Tambah Dosen
        </button>
    </form>

    <div id="hasil" role="status"></div>
    <p><a href="dosen.php">Kembali ke halaman data dosen</a></p>
</div>

<script>
const form = document.getElementById('formDosen');
const hasil = document.getElementById('hasil');
const tombol = document.getElementById('tombol');

form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    if (!data.id_prodi) {
        data.id_prodi = null;
    }

    hasil.textContent = 'Mengirim permintaan...';
    tombol.disabled = true;

    try {
        const response = await fetch(
            'api/dosen/tambah.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }
        );

        const teks = await response.text();
        let json;

        try {
            json = JSON.parse(teks);
        } catch {
            throw new Error(
                'Server tidak mengembalikan JSON yang valid. ' +
                'Respons: ' + teks.substring(0, 500)
            );
        }

        hasil.textContent =
            'HTTP Status: ' + response.status + '\n\n' +
            JSON.stringify(json, null, 2);

        if (response.ok && json.status === true) {
            form.reset();
        }
    } catch (error) {
        hasil.textContent = 'Pengujian gagal: ' + error.message;
    } finally {
        tombol.disabled = false;
    }
});
</script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nota'])) {
    if ($_FILES['nota']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['nota']['tmp_name'];
        $original_name = $_FILES['nota']['name'];

        // Tambahkan ekstensi jika belum ada
        $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $tmp_with_ext = $tmp_name . '.' . $file_ext;
        copy($tmp_name, $tmp_with_ext);

        // OCR.Space API
        $api_key = 'K81426622388957'; // Ganti jika perlu
        $url = 'https://api.ocr.space/parse/image';

        $post_fields = [
            'language' => 'eng',
            'isOverlayRequired' => 'false',
            'OCREngine' => '2',
            'apikey' => $api_key,
            'file' => new CURLFile($tmp_with_ext, mime_content_type($tmp_with_ext), $original_name),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['ParsedResults'][0]['ParsedText'])) {
            $text = $data['ParsedResults'][0]['ParsedText'];

            // Simpan ke database
            $db_host = "sql213.infinityfree.com";
            $db_user = "if0_38731598";
            $db_pass = "101079Dian";
            $db_name = "if0_38731598_tjoa";

            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

            if ($conn->connect_error) {
                die("<div class='text-danger'>Koneksi database gagal: " . $conn->connect_error . "</div>");
            }

            $stmt = $conn->prepare("INSERT INTO ocr_results (filename, ocr_text, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $original_name, $text);

            echo "<h4>Hasil OCR:</h4><pre>" . htmlspecialchars($text) . "</pre>";

            if ($stmt->execute()) {
                echo "<div class='text-success'>✔️ Data berhasil disimpan ke database.</div>";
            } else {
                echo "<div class='text-danger'>❌ Gagal menyimpan ke database: " . $stmt->error . "</div>";
            }

            $stmt->close();
            $conn->close();

            echo "<br><a href='index.html' class='btn btn-secondary'>Kembali</a>";
        } else {
            echo "<h4 class='text-danger'>Gagal membaca teks dari gambar.</h4><pre>";
            print_r($data);
            echo "</pre><a href='index.html' class='btn btn-secondary'>Kembali</a>";
        }

        // Hapus file sementara
        if (file_exists($tmp_with_ext)) {
            unlink($tmp_with_ext);
        }

    } else {
        echo "<div class='text-danger'>Upload file gagal.</div>";
    }
} else {
    echo "<div class='text-danger'>Permintaan tidak valid.</div>";
}
?>

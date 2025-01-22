<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Viewer</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            border: 10px solid #000;
            padding: 20px;
            position: relative;
            text-align: center;
        }
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 10px;
            text-decoration: none;
            font-size: 24px;
        }
        .nav-arrow.prev {
            left: 10px;
        }
        .nav-arrow.next {
            right: 10px;
        }
        .home-link {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 10px;
            text-decoration: none;
            font-size: 18px;
        }
        .banner-space {
            height: 100px;
            background-color: #f1f1f1;
            text-align: center;
            line-height: 100px;
            margin-bottom: 20px;
        }
        .image-viewer img {
            max-width: 100%;
            max-height: 600px;
            border: 2px solid #000;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="banner-space">Banner Space</div>
    <div class="container">
        <?php
        $path = isset($_GET['path']) ? $_GET['path'] : '';
        $index = isset($_GET['index']) ? intval($_GET['index']) : 0;
        $root = '/var/www/img.matrix.lan';
        $currentPath = realpath($root . '/' . $path);
        $files = array_values(array_filter(glob(dirname($currentPath) . '/*'), 'is_file'));

        echo "<a href=\"index.php?path=" . rawurlencode(dirname($path)) . "\" class=\"home-link\">Home</a>";

        if ($index > 0) {
            $prevIndex = $index - 1;
            echo "<a href=\"image.php?path=" . rawurlencode($path) . "&index=$prevIndex\" class=\"nav-arrow prev\">&lt; Previous</a>";
        }
        if ($index < count($files) - 1) {
            $nextIndex = $index + 1;
            echo "<a href=\"image.php?path=" . rawurlencode($path) . "&index=$nextIndex\" class=\"nav-arrow next\">Next &gt;</a>";
        }

        $fileName = basename($files[$index]);
        $thumbnailDir = $root . '/thumbnail';
        $folderName = basename(dirname($path));
        $middleImageDir = $thumbnailDir . '/' . $folderName;
        $middleImagePath = $middleImageDir . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '_scaled.webp';

        if (!file_exists($middleImagePath)) {
            if (!is_dir($middleImageDir)) {
                mkdir($middleImageDir, 0777, true);
            }
            try {
                $image = new \Gmagick($currentPath);
                $image->resizeImage(768, 768, \Gmagick::FILTER_LANCZOS, 1, true);
                $image->setImageFormat('webp');
                $image->setCompressionQuality(80);
                $image->write($middleImagePath);
                error_log("Middle-sized image created for $fileName at $middleImagePath");
            } catch (\Exception $e) {
                error_log("Failed to create middle-sized image for $fileName: " . $e->getMessage());
            }
        }

        echo "<div class=\"image-viewer\">
                <img src=\"$middleImagePath\" alt=\"$fileName\" onclick=\"window.open('$path', '_blank')\">
                <p>$fileName</p>
              </div>";
        ?>
    </div>
    <div class="banner-space">Banner Space</div>
    <script src="script.js"></script>
</body>
</html>

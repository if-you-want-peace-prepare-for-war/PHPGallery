<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            border: 10px solid #000;
            padding: 20px;
            position: relative;
        }
        .thumb {
            display: inline-block;
            margin: 10px;
        }
        .thumb img {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #000;
        }
        .banner-space {
            height: 100px;
            background-color: #f1f1f1;
            text-align: center;
            line-height: 100px;
            margin-bottom: 20px;
        }
    </style>
    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="//go.matrix.rocks/";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '1']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>
<body>
<div class="banner-space">Banner Space</div>
<div class="container">
    <h1>Image Gallery</h1>
    <nav class="breadcrumb">
        <?php
        $path = isset($_GET['path']) ? $_GET['path'] : '';
        $parts = explode('/', trim($path, '/'));
        $link = '';
        echo '<a href="?path=">Home</a>';
        foreach ($parts as $part) {
            $link .= rawurlencode($part) . '/';
            echo " &gt; <a href=\"?path=$link\">$part</a>";
        }
        ?>
    </nav>
    <div class="menu">
        <?php
        $root = '/var/www/img.matrix.lan';
        $currentPath = realpath($root . '/' . $path);
        $dirs = array_filter(glob($currentPath . '/*'), 'is_dir');
        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            echo "<a href=\"?path=" . rawurlencode($path . '/' . $dirName) . "\">$dirName</a><br>";
        }
        ?>
    </div>
    <div class="gallery">
        <?php
        $files = array_filter(glob($currentPath . '/*'), 'is_file');
        $thumbnailDir = $root . '/thumbnail';

        if (!is_dir($thumbnailDir)) {
            if (!mkdir($thumbnailDir, 0777, true)) {
                error_log("Failed to create directory $thumbnailDir");
                die("Failed to create directory $thumbnailDir");
            }
        }

        foreach ($files as $index => $file) {
            $fileName = basename($file);
            $thumbnailPath = $thumbnailDir . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

            if (!file_exists($thumbnailPath)) {
                try {
                    $image = new Gmagick($file);
                    $image->thumbnailImage(200, 200, true);
                    $image->setImageFormat('webp');
                    $image->write($thumbnailPath);
                    error_log("Thumbnail created for $fileName at $thumbnailPath");
                } catch (Exception $e) {
                    error_log("Failed to create thumbnail for $fileName: " . $e->getMessage());
                }
            }

            echo "<div class=\"thumb\">
                        <a href=\"image.php?path=" . rawurlencode($path . '/'
                    . $fileName) . "&index=$index\" target=\"_top\"><img src=\"/thumbnail/" . pathinfo($fileName, PATHINFO_FILENAME) . ".webp\" alt=\"$fileName\"></a>
                        <p>$fileName</p>
                      </div>";
        }
        ?>
    </div>
</div>
<div class="banner-space">Banner Space</div>
<script src="script.js"></script>
</body>
</html>
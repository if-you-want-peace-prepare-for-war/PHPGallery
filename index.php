<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <link rel="stylesheet" href="style.css">
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
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $parts = explode('/', trim($path, '/'));
        $link = '';
        echo '<a href="?path=&page=1">Home</a>';
        foreach ($parts as $part) {
            $link .= rawurlencode($part) . '/';
            echo " &gt; <a href=\"?path=$link&page=1\">$part</a>";
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
            echo "<a href=\"?path=" . rawurlencode($path . '/' . $dirName) . "&page=1\">$dirName</a><br>";
        }
        ?>
    </div>
    <div class="gallery">
        <?php
        $files = array_filter(glob($currentPath . '/*'), 'is_file');
        $thumbnailDir = $root . '/thumbnail';
        $imagesPerPage = 24;
        $totalPages = ceil(count($files) / $imagesPerPage);
        $offset = ($page - 1) * $imagesPerPage;
        $files = array_slice($files, $offset, $imagesPerPage);

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
                    <a href=\"image.php?path=" . rawurlencode($path . '/' . $fileName) . "&index=" . ($offset + $index) . "\" target=\"_top\"><img src=\"/thumbnail/" . pathinfo($fileName, PATHINFO_FILENAME) . ".webp\" alt=\"$fileName\"></a>
                    <p>$fileName</p>
                  </div>";
        }
        ?>
    </div>
    <div class="pagination">
        <?php
        if ($page > 1) {
            echo "<a href=\"?path=" . rawurlencode($path) . "&page=" . ($page - 1) . "\">&lt; Previous</a>";
        }
        for ($i = 1; $i <= $totalPages; $i++) {
            echo "<a href=\"?path=" . rawurlencode($path) . "&page=$i\">$i</a>";
        }
        if ($page < $totalPages) {
            echo "<a href=\"?path=" . rawurlencode($path) . "&page=" . ($page + 1) . "\">Next &gt;</a>";
        }
        ?>
    </div>
</div>
<div class="banner-space">Banner Space</div>
<script>
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key === 'ArrowLeft') {
            const breadcrumbLinks = document.querySelectorAll('.breadcrumb a');
            if (breadcrumbLinks.length > 1) {
                window.location.href = breadcrumbLinks[breadcrumbLinks.length - 2].href;
            }
        } else if (event.ctrlKey && event.key === 'ArrowRight') {
            const menuLinks = document.querySelectorAll('.menu a');
            if (menuLinks.length > 0) {
                window.location.href = menuLinks[0].href;
            }
        } else if (event.ctrlKey && event.key === 'ArrowUp') {
            window.location.href = document.querySelector('.breadcrumb a').href;
        } else if (event.key === 'ArrowDown') {
            window.history.back();
        } else if (event.key === 'Enter' || event.key === ' ') {
            const selectedThumb = document.querySelector('.thumb a');
            if (selectedThumb) {
                window.open(selectedThumb.href, '_blank');
            }
        }
    });
</script>
</body>
</html>
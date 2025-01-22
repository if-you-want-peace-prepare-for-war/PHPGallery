<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Viewer</title>
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
    <?php
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $index = isset($_GET['index']) ? intval($_GET['index']) : 0;
    $root = getcwd(); // Using the current working directory as the basepath/webroot
    $currentPath = realpath($root . '/' . $path);
    $files = array_values(array_filter(glob(dirname($currentPath) . '/*'), 'is_file'));

    echo "<a href=\"index.php?path=" . rawurlencode(dirname($path)) . "\" class=\"home-link\">Home</a>";

    if ($index > 0) {
        $prevIndex = $index - 1;
        $prevPath = str_replace($root, '', $files[$prevIndex]);
        echo "<a href=\"image.php?path=" . rawurlencode($prevPath) . "&index=$prevIndex\" class=\"nav-arrow prev\">&lt; Previous</a>";
    }
    if ($index < count($files) - 1) {
        $nextIndex = $index + 1;
        $nextPath = str_replace($root, '', $files[$nextIndex]);
        echo "<a href=\"image.php?path=" . rawurlencode($nextPath) . "&index=$nextIndex\" class=\"nav-arrow next\">Next &gt;</a>";
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
            $image = new Gmagick($currentPath);
            $image->resizeImage(768, 768, Gmagick::FILTER_LANCZOS, 1, true);
            $image->setImageFormat('webp');
            $image->setCompressionQuality(80);
            $image->write($middleImagePath);
            error_log("Middle-sized image created for $fileName at $middleImagePath");
        } catch (Exception $e) {
            error_log("Failed to create middle-sized image for $fileName: " . $e->getMessage());
        }
    }

    // Remove webroot path from the image source URL and include the filename
    $relativeMiddleImagePath = str_replace($root, '', $middleImagePath);

    echo "<div class=\"image-viewer\">
                <img src=\"$relativeMiddleImagePath\" alt=\"$fileName\" onclick=\"window.open('" . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . "', '_blank')\">
                <p>$fileName</p>
              </div>";
    ?>
</div>
<div class="banner-space">Banner Space</div>
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            const prevButton = document.querySelector('.nav-arrow.prev');
            if (prevButton) {
                window.location.href = prevButton.href;
            }
        } else if (event.key === 'ArrowRight') {
            const nextButton = document.querySelector('.nav-arrow.next');
            if (nextButton) {
                window.location.href = nextButton.href;
            }
        } else if (event.key === 'ArrowUp') {
            const homeButton = document.querySelector('.home-link');
            if (homeButton) {
                window.location.href = homeButton.href;
            }
        } else if (event.key === 'ArrowDown') {
            window.history.back();
        } else if (event.key === 'Enter' || event.key === ' ') {
            const image = document.querySelector('.image-viewer img');
            if (image) {
                window.open(image.src, '_blank');
            }
        }
    });
</script>
</body>
</html>
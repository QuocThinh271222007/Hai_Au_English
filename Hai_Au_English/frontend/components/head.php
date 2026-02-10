<?php
// Include base config
require_once __DIR__ . '/base_config.php';

// Helper function để fix đường dẫn CSS/JS
function fixAssetPath($path, $basePath, $assetsPath) {
    // Nếu đường dẫn bắt đầu bằng /frontend, thay thế bằng basePath + /frontend
    if (strpos($path, '/frontend') === 0) {
        return $basePath . $path;
    }
    // Nếu đường dẫn tương đối (không bắt đầu bằng / hoặc http), thêm assetsPath
    if (strpos($path, '/') !== 0 && strpos($path, 'http') !== 0) {
        return $assetsPath . '/' . $path;
    }
    return $path;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hải Âu English'; ?></title>
    <link rel="icon" href="<?php echo $assetsPath; ?>/assets/images/favicon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/styles.css">
    <?php if(isset($additionalCss)): ?>
        <?php foreach($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo fixAssetPath($css, $basePath, $assetsPath); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if(isset($additionalHeadScripts)): ?>
        <?php foreach($additionalHeadScripts as $script): ?>
            <script src="<?php echo fixAssetPath($script, $basePath, $assetsPath); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

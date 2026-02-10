<?php
// Ensure base config is loaded
if (!isset($basePath)) {
    require_once __DIR__ . '/base_config.php';
}

// Helper function nếu chưa được define
if (!function_exists('fixAssetPath')) {
    function fixAssetPath($path, $basePath) {
        if (strpos($path, '/frontend') === 0) {
            return $basePath . $path;
        }
        return $path;
    }
}
?>
    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
    <?php if(isset($additionalScripts)): ?>
        <?php foreach($additionalScripts as $script): ?>
            <?php 
                $scriptSrc = is_array($script) ? $script['src'] : $script;
                $scriptSrc = fixAssetPath($scriptSrc, $basePath);
            ?>
            <?php if(isset($script['module']) && $script['module']): ?>
                <script type="module" src="<?php echo $scriptSrc; ?>"></script>
            <?php else: ?>
                <script src="<?php echo $scriptSrc; ?>"></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

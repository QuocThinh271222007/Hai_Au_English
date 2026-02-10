<?php
/**
 * Site Content Helper - Load dynamic content from database
 * Include this file in frontend pages to access site_content
 */

// Load database connection
require_once __DIR__ . '/../../backend/php/db.php';

/**
 * Get site content by page
 * @param string $page - Page name (home, about, courses, teachers, contact)
 * @return array - Associative array of content [section][key] => value
 */
function getSiteContent($page = null) {
    global $pdo;
    
    try {
        if ($page) {
            $stmt = $pdo->prepare("SELECT section, content_key, content_value, content_type FROM site_content WHERE page = ? AND is_active = 1");
            $stmt->execute([$page]);
        } else {
            $stmt = $pdo->query("SELECT page, section, content_key, content_value, content_type FROM site_content WHERE is_active = 1");
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by section and key
        $content = [];
        foreach ($results as $row) {
            if ($page) {
                $content[$row['section']][$row['content_key']] = $row['content_value'];
            } else {
                $content[$row['page']][$row['section']][$row['content_key']] = $row['content_value'];
            }
        }
        
        return $content;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get specific content value
 * @param string $page
 * @param string $section
 * @param string $key
 * @param string $default - Default value if not found
 * @return string
 */
function getContent($page, $section, $key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT content_value FROM site_content WHERE page = ? AND section = ? AND content_key = ? AND is_active = 1");
        $stmt->execute([$page, $section, $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['content_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Get site settings
 * @return array - Associative array [setting_key] => setting_value
 */
function getSiteSettings() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get specific setting value
 * @param string $key
 * @param string $default
 * @return string
 */
function getSetting($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Pre-load all content for current page if $currentPage is set
$siteContent = [];
$siteSettings = getSiteSettings();

// Echo content helper - safely output content with default
function c($page, $section, $key, $default = '') {
    global $siteContent;
    
    if (!isset($siteContent[$page])) {
        $siteContent[$page] = getSiteContent($page);
    }
    
    return $siteContent[$page][$section][$key] ?? $default;
}

// Echo setting helper
function s($key, $default = '') {
    global $siteSettings;
    return $siteSettings[$key] ?? $default;
}

<?php
/**
 * Configuration pour l'API Brawl Stars
 * 
 * La clé API est idéalement fournie via l'environnement (.env).
 * Pour obtenir ou mettre à jour votre clé API (changement d'IP autorisée),
 * rendez-vous sur https://developer.brawlstars.com/ puis copiez la nouvelle clé.
 */

// Préférence à la variable d'environnement si disponible
if (!defined('BRAWL_STARS_API_KEY')) {
    $envKey = $_ENV['BRAWL_STARS_API_KEY'] ?? getenv('BRAWL_STARS_API_KEY') ?? '';
    if (!empty($envKey)) {
        define('BRAWL_STARS_API_KEY', $envKey);
    } else {
        // Fallback: ancienne clé (à remplacer dès que possible par une clé mise à jour)
        define('BRAWL_STARS_API_KEY', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6ImI0MWVhYTQzLWVlYWMtNDY3OC05OGQzLWRmZWMxNDUxMGZmYiIsImlhdCI6MTc2Mjc2NzYwMCwic3ViIjoiZGV2ZWxvcGVyLzRiYTBmNWQ1LTQ0NjQtYjY1NS1mZDc5LTEyMzU5MjJiMDE5ZiIsInNjb3BlcyI6WyJicmF3bHN0YXJzIl0sImxpbWl0cyI6W3sidGllciI6ImRldmVsb3Blci9zaWx2ZXIiLCJ0eXBlIjoidGhyb3R0bGluZyJ9LHsiY2lkcnMiOlsiOTAuOC4xNS4xNTgiXSwidHlwZSI6ImNsaWVudCJ9XX0.67pbxvvQG9zVN-fw7vGmXNiUUH8KilWSVvWtOcr_fVYx5M_bPIZPxkyhdDpf7zg6qPr_2SKujl44mRl7aF4T-g');
    }
}

// URL de base de l'API Brawl Stars
if (!defined('BRAWL_STARS_API_BASE_URL')) {
    define('BRAWL_STARS_API_BASE_URL', 'https://api.brawlstars.com/v1');
}

?>
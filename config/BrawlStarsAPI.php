<?php

class BrawlStarsAPI {
    private $apiKey;
    private $baseUrl = 'https://api.brawlstars.com/v1';
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Récupère les informations d'un joueur par son tag
     * @param string $playerTag Le tag du joueur (avec ou sans #)
     * @return array|false Les données du joueur ou false en cas d'erreur
     */
    public function getPlayer($playerTag) {
        // Nettoyer le tag (enlever # et espaces)
        $playerTag = str_replace('#', '', trim($playerTag));
        $playerTag = strtoupper($playerTag);
        
        // Encoder le tag pour l'URL
        $encodedTag = urlencode('#' . $playerTag);
        
        $url = $this->baseUrl . '/players/' . $encodedTag;
        
        return $this->makeRequest($url);
    }
    
    /**
     * Récupère les événements en cours
     * @return array|false Les événements ou false en cas d'erreur
     */
    public function getEvents() {
        $url = $this->baseUrl . '/events/rotation';
        return $this->makeRequest($url);
    }
    
    /**
     * Récupère les brawlers disponibles
     * @return array|false Les brawlers ou false en cas d'erreur
     */
    public function getBrawlers() {
        $url = $this->baseUrl . '/brawlers';
        return $this->makeRequest($url);
    }
    
    /**
     * Effectue une requête HTTP vers l'API
     * @param string $url L'URL de l'endpoint
     * @return array|false Les données décodées ou false en cas d'erreur
     */
    private function makeRequest($url) {
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL pour WAMP
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Désactiver la vérification de l'hôte SSL
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Erreur cURL: " . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log("Erreur API Brawl Stars: HTTP " . $httpCode . " - " . $response);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur JSON: " . json_last_error_msg());
            return false;
        }
        
        return $data;
    }
    
    /**
     * Valide un tag de joueur Brawl Stars
     * @param string $playerTag Le tag à valider
     * @return bool True si le tag est valide
     */
    public static function isValidPlayerTag($playerTag) {
        // Enlever le # s'il est présent
        $tag = str_replace('#', '', trim($playerTag));
        
        // Vérifier la longueur (généralement entre 3 et 9 caractères)
        if (strlen($tag) < 3 || strlen($tag) > 9) {
            return false;
        }
        
        // Vérifier que le tag ne contient que des caractères alphanumériques valides
        // Les tags Brawl Stars peuvent contenir: 0-9, A-Z (pas de caractères spéciaux sauf #)
        if (!preg_match('/^[0-9A-Z]+$/', strtoupper($tag))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Formate un tag de joueur (ajoute # au début)
     * @param string $playerTag Le tag à formater
     * @return string Le tag formaté
     */
    public static function formatPlayerTag($playerTag) {
        $tag = str_replace('#', '', trim($playerTag));
        return '#' . strtoupper($tag);
    }
}
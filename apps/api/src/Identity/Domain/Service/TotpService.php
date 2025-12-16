<?php

declare(strict_types=1);

namespace App\Identity\Domain\Service;

use OTPHP\TOTP;

/**
 * Service domain pour la gestion de l'authentification à deux facteurs (TOTP)
 * Utilise l'algorithme TOTP (Time-based One-Time Password) - RFC 6238
 */
class TotpService
{
    private const ISSUER = 'invest.ia';
    private const PERIOD = 30; // Secondes
    private const DIGITS = 6;
    private const ALGORITHM = 'sha1';
    
    /**
     * Génère un nouveau secret TOTP pour un utilisateur
     * 
     * @param string $userEmail Email de l'utilisateur (pour label)
     * @return array{secret: string, qrCodeUri: string}
     */
    public function generateSecret(string $userEmail): array
    {
        $totp = TOTP::create(
            null, // Auto-génère un secret aléatoire
            self::PERIOD,
            self::ALGORITHM,
            self::DIGITS
        );
        
        $totp->setLabel($userEmail);
        $totp->setIssuer(self::ISSUER);
        
        return [
            'secret' => $totp->getSecret(),
            'qrCodeUri' => $totp->getProvisioningUri()
        ];
    }
    
    /**
     * Vérifie un code TOTP pour un secret donné
     * 
     * @param string $secret Le secret TOTP de l'utilisateur
     * @param string $code Le code à 6 chiffres saisi par l'utilisateur
     * @param int $window Fenêtre de tolérance (nombre de périodes avant/après à vérifier)
     * @return bool True si le code est valide
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        try {
            $totp = TOTP::create($secret, self::PERIOD, self::ALGORITHM, self::DIGITS);
            
            // Vérifie le code avec une fenêtre de tolérance (pour compenser décalage horaire)
            return $totp->verify($code, null, $window);
            
        } catch (\Exception $e) {
            // Secret invalide ou erreur de vérification
            return false;
        }
    }
    
    /**
     * Génère le code TOTP actuel pour un secret donné
     * Utile pour les tests uniquement, ne pas exposer en production
     * 
     * @param string $secret Le secret TOTP
     * @return string Le code à 6 chiffres
     */
    public function getCurrentCode(string $secret): string
    {
        $totp = TOTP::create($secret, self::PERIOD, self::ALGORITHM, self::DIGITS);
        return $totp->now();
    }
    
    /**
     * Vérifie si un secret TOTP est valide
     * 
     * @param string $secret Le secret à vérifier
     * @return bool True si le secret est valide
     */
    public function isValidSecret(string $secret): bool
    {
        try {
            TOTP::create($secret, self::PERIOD, self::ALGORITHM, self::DIGITS);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Génère une URL de QR code pour Google Authenticator / Authy
     * Compatible avec https://chart.googleapis.com/chart ou bibliothèques QR tierces
     * 
     * @param string $provisioningUri L'URI de provisioning TOTP
     * @param int $size Taille du QR code en pixels
     * @return string URL du QR code
     */
    public function getQrCodeUrl(string $provisioningUri, int $size = 200): string
    {
        return sprintf(
            'https://chart.googleapis.com/chart?chs=%dx%d&chld=M|0&cht=qr&chl=%s',
            $size,
            $size,
            urlencode($provisioningUri)
        );
    }
}

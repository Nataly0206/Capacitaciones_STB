<?php

namespace App\Support;

class VistaPreviaRecurso
{
    /**
     * El visor de Office Online de Microsoft necesita poder alcanzar la URL
     * del archivo desde internet público. Si el host es localhost o cae en
     * un rango de IP privado/reservado (RFC1918, loopback, link-local, etc.),
     * Microsoft nunca podrá cargarlo — hay que usar el botón de descarga en
     * vez de intentar el visor.
     */
    public static function esUrlAccesibleDesdeInternet(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return false;
        }

        $host = strtolower($host);

        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.test') || str_ends_with($host, '.internal')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        return true;
    }
}

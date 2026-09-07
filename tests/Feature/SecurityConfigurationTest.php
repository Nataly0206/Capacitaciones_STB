<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    public function test_respuestas_web_incluyen_cabeceras_defensivas(): void
    {
        $this->withoutVite();

        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_csp_permite_previsualizar_recursos_del_mismo_sitio(): void
    {
        $request = Request::create('http://localhost/capacitaciones');
        $response = app(SecurityHeaders::class)->handle(
            $request,
            fn () => new Response('ok')
        );

        $this->assertStringContainsString(
            "frame-src 'self' https:",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_csp_de_produccion_no_permite_evaluacion_dinamica(): void
    {
        $entornoAnterior = app()->environment();
        app()->detectEnvironment(fn () => 'production');

        try {
            $request = Request::create('https://capacitaciones.example.com/login');
            $response = app(SecurityHeaders::class)->handle(
                $request,
                fn () => new Response('ok')
            );

            $this->assertStringNotContainsString(
                "'unsafe-eval'",
                (string) $response->headers->get('Content-Security-Policy')
            );
            $this->assertSame(
                'max-age=31536000; includeSubDomains',
                $response->headers->get('Strict-Transport-Security')
            );
        } finally {
            app()->detectEnvironment(fn () => $entornoAnterior);
        }
    }

    public function test_politica_global_rechaza_contrasenas_de_ocho_caracteres(): void
    {
        $validator = Validator::make([
            'password' => 'Abcd123!',
            'password_confirmation' => 'Abcd123!',
        ], [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_urls_de_recursos_rechazan_protocolos_ejecutables(): void
    {
        $reglas = ['url_recurso' => ['nullable', 'url:http,https', 'max:1000']];

        $this->assertTrue(Validator::make(['url_recurso' => 'javascript:alert(1)'], $reglas)->fails());
        $this->assertFalse(Validator::make(['url_recurso' => 'https://example.com/recurso'], $reglas)->fails());
    }
}

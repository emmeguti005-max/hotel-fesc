<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Las pruebas no deben depender de los assets compilados (public/build no se versiona):
        // en el runner de CI no existe manifest.json y las vistas fallaban con ViteManifestNotFoundException.
        $this->withoutVite();
    }
}

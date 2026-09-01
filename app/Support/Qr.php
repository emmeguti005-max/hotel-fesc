<?php

namespace App\Support;

use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Qr
{
    /** Devuelve el markup <svg> del QR para $data, listo para incrustar inline en una vista. */
    public static function svg(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'svgAddXmlHeader' => false,
            'quietzoneSize' => 2,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($data);
    }
}

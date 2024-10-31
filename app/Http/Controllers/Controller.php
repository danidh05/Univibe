<?php

namespace App\Http\Controllers;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Univibe API",
 *         version="1.0.0",
 *         description="API documentation for Univibe.",
 *         @OA\Contact(
 *             email="support@univibe.com"
 *         )
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT",
 *             in="header",
 *             name="Authorization"
 *         )
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server"
 *     )
 * )
 */

abstract class Controller
{
    //
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswa = \App\Models\User::where('role', 'mahasiswa')->first();
if(!$mahasiswa) die("No mahasiswa\n");

echo "Role: {$mahasiswa->role}\n";

$request = Illuminate\Http\Request::create('/lms/test-course/enroll', 'POST');
$request->setUserResolver(function() use ($mahasiswa) { return $mahasiswa; });

try {
    $middleware = new \App\Http\Middleware\EnsureUserHasRole();
    $middleware->handle($request, function($req) {
        echo "Middleware passed\n";
        return new \Illuminate\Http\Response();
    }, 'mahasiswa');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . " Code: " . $e->getCode() . "\n";
    if (method_exists($e, 'getStatusCode')) {
        echo "Status: " . $e->getStatusCode() . "\n";
    }
}

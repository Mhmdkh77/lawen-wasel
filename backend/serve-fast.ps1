$phpExecutable = (Get-Command php -ErrorAction Stop).Source
$phpDirectory = Split-Path -Parent $phpExecutable
$opcacheExtension = Join-Path $phpDirectory 'ext\php_opcache.dll'
$phpOptions = @()

if (-not ((& $phpExecutable -m) -match '^Zend OPcache$')) {
    if (-not (Test-Path -LiteralPath $opcacheExtension)) {
        throw "PHP OPcache was not found at $opcacheExtension"
    }

    $phpOptions += @('-d', "zend_extension=$opcacheExtension")
}

$phpOptions += @('-d', 'opcache.enable_cli=1', '-d', 'opcache.revalidate_freq=0')

Push-Location (Join-Path $PSScriptRoot 'public')
try {
    & $phpExecutable @phpOptions -S '127.0.0.1:8000' -t . '../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
}
finally {
    Pop-Location
}

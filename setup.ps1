$ErrorActionPreference = "Stop"

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$EnvExample = Join-Path $ProjectRoot ".env.example"
$EnvFile = Join-Path $ProjectRoot ".env"
$PrivateRoot = Join-Path (Split-Path -Parent $ProjectRoot) "SmartStudyProPrivateLearning"
$DatabaseDir = Join-Path $ProjectRoot "database"

if (-not (Test-Path $EnvExample)) {
    throw ".env.example was not found."
}

New-Item -ItemType Directory -Force -Path $DatabaseDir | Out-Null
New-Item -ItemType Directory -Force -Path $PrivateRoot | Out-Null

if (-not (Test-Path $EnvFile)) {
    Copy-Item $EnvExample $EnvFile
}

$envText = Get-Content $EnvFile -Raw

if ($envText -match '(?m)^APP_SECRET=$') {
    $bytes = New-Object byte[] 32
    [System.Security.Cryptography.RandomNumberGenerator]::Fill($bytes)
    $secret = ([Convert]::ToHexString($bytes)).ToLowerInvariant()
    $envText = $envText -replace '(?m)^APP_SECRET=$', "APP_SECRET=$secret"
}

$privatePath = $PrivateRoot.Replace('\','/')
$envText = $envText -replace '(?m)^PROTECTED_LEARNING_ROOT=.*$', "PROTECTED_LEARNING_ROOT=$privatePath"

Set-Content -Path $EnvFile -Value $envText -Encoding UTF8

$php = Get-Command php -ErrorAction SilentlyContinue
if ($null -eq $php) {
    Write-Host "PHP was not found in PATH."
    Write-Host "If using XAMPP, run C:\xampp\php\php.exe scripts\migrate-protected-learning.php manually."
    exit 1
}

& php -v
& php -l (Join-Path $ProjectRoot "config.php")

Write-Host ""
Write-Host "SmartStudyPro setup completed."
Write-Host "Environment: $EnvFile"
Write-Host "Private learning storage: $PrivateRoot"
Write-Host "Payment mode: DEMO"
Write-Host "Next: start Apache in XAMPP and open http://localhost/SmartStudyPro/"

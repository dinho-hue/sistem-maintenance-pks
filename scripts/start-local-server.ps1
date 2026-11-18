$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpPath = 'C:\xampp\php\php.exe'
$mysqlPath = 'C:\xampp\mysql\bin\mysqld.exe'
$mysqlConfig = 'C:\xampp\mysql\bin\my.ini'
$logPath = Join-Path $projectRoot 'storage\logs\local-server-task.log'

if (-not (Test-Path -LiteralPath $phpPath)) {
    throw "XAMPP PHP was not found at $phpPath."
}

if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'artisan'))) {
    throw "Laravel artisan was not found in $projectRoot."
}

function Test-LocalPort {
    param([int] $Port)

    $client = [System.Net.Sockets.TcpClient]::new()
    try {
        $connection = $client.ConnectAsync('127.0.0.1', $Port)
        return $connection.Wait(500) -and $client.Connected
    } catch {
        return $false
    } finally {
        $client.Dispose()
    }
}

function Write-TaskLog {
    param([string] $Message)

    Add-Content -LiteralPath $logPath -Value "$(Get-Date -Format s) $Message"
}

if (-not (Test-LocalPort -Port 3306)) {
    if (-not (Test-Path -LiteralPath $mysqlPath) -or -not (Test-Path -LiteralPath $mysqlConfig)) {
        throw 'XAMPP MySQL is not running and its startup files were not found.'
    }

    Start-Process -FilePath $mysqlPath `
        -ArgumentList "--defaults-file=`"$mysqlConfig`" --standalone" `
        -WorkingDirectory (Split-Path -Parent $mysqlPath) `
        -WindowStyle Hidden

    $mysqlReady = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        if (Test-LocalPort -Port 3306) {
            $mysqlReady = $true
            break
        }

        Start-Sleep -Seconds 2
    }

    if (-not $mysqlReady) {
        throw 'XAMPP MySQL did not start listening on port 3306.'
    }

    Write-TaskLog 'Started XAMPP MySQL.'
}

while ($true) {
    if (Test-LocalPort -Port 8000) {
        Start-Sleep -Seconds 5
        continue
    }

    Write-TaskLog 'Starting Laravel development server on 127.0.0.1:8000.'
    $server = Start-Process -FilePath $phpPath `
        -ArgumentList 'artisan serve --host=127.0.0.1 --port=8000' `
        -WorkingDirectory $projectRoot `
        -WindowStyle Hidden `
        -PassThru

    $server.WaitForExit()
    Write-TaskLog "Laravel server stopped with exit code $($server.ExitCode); restarting in 5 seconds."
    Start-Sleep -Seconds 5
}

param(
    [int[]] $Ports = @(3306, 6379, 8080),
    [int] $TimeoutSeconds = 60
)

$deadline = [DateTime]::UtcNow.AddSeconds($TimeoutSeconds)

do {
    $unavailable = @()

    foreach ($port in $Ports) {
        $client = [System.Net.Sockets.TcpClient]::new()

        try {
            $connected = $client.ConnectAsync('127.0.0.1', $port).Wait(1000)

            if (-not $connected -or -not $client.Connected) {
                $unavailable += $port
            }
        } catch {
            $unavailable += $port
        } finally {
            $client.Dispose()
        }
    }

    if ($unavailable.Count -eq 0) {
        Write-Host "Docker ports are ready: $($Ports -join ', ')"
        exit 0
    }

    Write-Host "Waiting for Docker ports: $($unavailable -join ', ')"
    Start-Sleep -Seconds 2
} while ([DateTime]::UtcNow -lt $deadline)

Write-Error "Docker ports did not become ready: $($unavailable -join ', ')"
exit 1

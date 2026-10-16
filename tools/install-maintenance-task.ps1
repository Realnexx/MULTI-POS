$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$phpPath = Join-Path (Split-Path (Split-Path $projectRoot -Parent) -Parent) 'php\php.exe'
if (-not (Test-Path -LiteralPath $phpPath)) { throw 'PHP was not found. Edit the PHP path in this installer for your server.' }
$runnerPath = Join-Path $projectRoot 'tools\system-maintenance.php'
$digest = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash([System.Text.Encoding]::UTF8.GetBytes($projectRoot))).Replace('-', '').Substring(0, 12)
$taskName = "KairoPosMaintenance-$digest"
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$action = New-ScheduledTaskAction -Execute $phpPath -Argument ('"' + $runnerPath + '"') -WorkingDirectory $projectRoot
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Hours 1)
# Never elevate a task that executes application files writable by the web server.
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -MultipleInstances IgnoreNew
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description 'Hourly POS maintenance; the application controls backup hour, enabled state and retention.' -Force | Out-Null
Write-Output "Installed $taskName for $identity. It runs hourly while this account is signed in. For an unattended server, configure a least-privilege service account in Task Scheduler."

@echo off
setlocal enabledelayedexpansion

REM Nome del file di output (creato accanto al .bat)
set OUTPUT=contenuto_cartella.txt

REM Se esiste già, lo cancella
if exist "%OUTPUT%" del "%OUTPUT%"

REM Scansiona ricorsivamente tutti i file partendo dalla cartella del .bat
for /R %%f in (*.*) do (
    if /I not "%%~nxf"=="%~nx0" if /I not "%%~nxf"=="%OUTPUT%" (
        echo ============================= >> "%OUTPUT%"
        echo FILE: %%f >> "%OUTPUT%"
        echo ============================= >> "%OUTPUT%"
        type "%%f" >> "%OUTPUT%" 2>nul
        echo. >> "%OUTPUT%"
        echo. >> "%OUTPUT%"
    )
)

echo Operazione completata! Tutto salvato in "%OUTPUT%"
pause

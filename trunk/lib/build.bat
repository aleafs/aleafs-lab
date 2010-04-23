SET PHING_HOME="F:\\web\pear"
SET PHP_COMMAND="F:\\web\php\php.exe"

F:\\web\pear\phing.bat %*
IF ERRORLEVEL 1 exit /B 1
